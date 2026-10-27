<?php

namespace Tests\Feature;

use App\Jobs\ProcessApplicantDocUploadJob;
use App\Models\PpdbApplicant;
use App\Models\PpdbDocument;
use App\Models\PpdbWave;
use App\Models\School;
use App\Services\DocumentCompressionService;
use App\Services\GoogleDriveClientService;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\FileList;
use Google\Service\Drive\Resource\Files;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class DocumentPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected PpdbApplicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->school = School::factory()->create([
            'school_code' => 'sman1jakarta',
            'name' => 'SMAN 1 Jakarta',
            'status' => 'active',
        ]);

        $wave = PpdbWave::create([
            'school_id' => 'sman1jakarta',
            'name' => 'Gelombang 1',
            'academic_year' => '2026/2027',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(5),
            'is_active' => true,
        ]);

        $this->applicant = PpdbApplicant::create([
            'school_id' => 'sman1jakarta',
            'wave_id' => $wave->id,
            'registration_number' => 'REG-SMAN1JKT-2026-0001',
            'full_name' => 'Budi Pratama',
            'nik' => '3201012345670001',
            'birth_date' => '2010-05-12',
            'gender' => 'L',
            'phone_whatsapp' => '08123456789',
            'status' => 'submitted',
        ]);
    }

    public function test_document_compression_service_skips_pdf_and_compresses_large_image(): void
    {
        $compression = new DocumentCompressionService();

        // 1. Test PDF skipping
        $pdfPath = Storage::disk('local')->path('test_sample.pdf');
        file_put_contents($pdfPath, '%PDF-1.4 sample content');
        $pdfResult = $compression->compressIfNeeded($pdfPath);
        $this->assertFalse($pdfResult['compressed']);

        // 2. Test Image Compression
        $imgPath = Storage::disk('local')->path('large_photo.jpg');
        $imgResource = imagecreatetruecolor(2000, 1800);
        $bgColor = imagecolorallocate($imgResource, 200, 100, 50);
        imagefilledrectangle($imgResource, 0, 0, 2000, 1800, $bgColor);
        imagejpeg($imgResource, $imgPath, 100);
        imagedestroy($imgResource);

        // Force compress with threshold = 1 byte
        $imgResult = $compression->compressIfNeeded($imgPath, sizeThresholdBytes: 100, maxDimension: 1200);
        $this->assertTrue($imgResult['compressed']);

        $size = getimagesize($imgPath);
        $this->assertLessThanOrEqual(1200, $size[0]);
        $this->assertLessThanOrEqual(1200, $size[1]);
    }

    public function test_google_drive_client_creates_3_tier_structure_and_uploads(): void
    {
        $mockFilesResource = Mockery::mock(Files::class);
        $mockDrive = Mockery::mock(Drive::class);
        $mockDrive->files = $mockFilesResource;

        // Mock folder queries returning empty (create folder)
        $emptyList = new FileList();
        $emptyList->setFiles([]);

        $createdFolder = new DriveFile(['id' => 'folder_123']);
        $uploadedFile = new DriveFile(['id' => 'file_999']);
        $uploadedFile->setWebViewLink('https://drive.google.com/file/d/file_999/view');

        $mockFilesResource->shouldReceive('listFiles')
            ->andReturn($emptyList);

        $mockFilesResource->shouldReceive('create')
            ->times(2)
            ->andReturn($createdFolder);

        $mockFilesResource->shouldReceive('create')
            ->once()
            ->andReturn($uploadedFile);

        $driveService = new GoogleDriveClientService($mockDrive);

        // Ensure folder hierarchy
        $applicantFolderId = $driveService->ensureApplicantFolder(
            'sman1jakarta',
            '2026',
            'REG-SMAN1JKT-2026-0001',
            'Budi Pratama'
        );
        $this->assertEquals('folder_123', $applicantFolderId);

        // Upload file
        $tempPath = Storage::disk('local')->path('test_upload.jpg');
        file_put_contents($tempPath, 'fake-image-bytes');

        $result = $driveService->uploadFile($tempPath, 'test_upload.jpg', 'image/jpeg', $applicantFolderId);
        $this->assertEquals('file_999', $result['drive_file_id']);
        $this->assertEquals('https://drive.google.com/file/d/file_999/view', $result['drive_view_url']);
    }

    public function test_process_applicant_doc_upload_job_compresses_uploads_and_cleans_temp(): void
    {
        $tempRelPath = 'temp_ppdb/sman1jakarta/REG-0001/kk.jpg';
        Storage::disk('local')->put($tempRelPath, 'mock-image-content');

        $document = PpdbDocument::create([
            'school_id' => 'sman1jakarta',
            'applicant_id' => $this->applicant->id,
            'document_type' => 'kartu_keluarga',
            'original_file_name' => 'kartu_keluarga.jpg',
            'verification_status' => 'pending',
        ]);

        $mockDriveService = Mockery::mock(GoogleDriveClientService::class);
        $mockDriveService->shouldReceive('ensureApplicantFolder')
            ->once()
            ->andReturn('folder_abc');

        $mockDriveService->shouldReceive('uploadFile')
            ->once()
            ->andReturn([
                'drive_file_id' => 'drive_id_123',
                'drive_view_url' => 'https://drive.google.com/file/d/drive_id_123/view',
            ]);

        $mockCompression = Mockery::mock(DocumentCompressionService::class);
        $mockCompression->shouldReceive('compressIfNeeded')
            ->once()
            ->andReturn(['compressed' => true, 'original_size' => 100, 'new_size' => 50, 'path' => '']);

        $job = new ProcessApplicantDocUploadJob(
            $document->id,
            $tempRelPath,
            'sman1jakarta',
            '2026',
            'REG-SMAN1JKT-2026-0001',
            'Budi Pratama'
        );

        $job->handle($mockCompression, $mockDriveService);

        // Assert DB updated
        $this->assertEquals('drive_id_123', $document->fresh()->drive_file_id);
        $this->assertEquals('https://drive.google.com/file/d/drive_id_123/view', $document->fresh()->drive_view_url);

        // Assert temp file was deleted on success
        Storage::disk('local')->assertMissing($tempRelPath);
    }

    public function test_cleanup_orphaned_temp_files_command_removes_old_files(): void
    {
        Storage::disk('local')->put('temp_ppdb/sman1jakarta/old_file.jpg', 'old content');
        Storage::disk('local')->put('temp_ppdb/sman1jakarta/recent_file.jpg', 'recent content');

        // Run command with days=0 to delete everything older than now
        $this->artisan('ppdb:clean-temp', ['--days' => 0])
            ->expectsOutputToContain('Cleaned')
            ->assertSuccessful();

        Storage::disk('local')->assertMissing('temp_ppdb/sman1jakarta/old_file.jpg');
    }
}

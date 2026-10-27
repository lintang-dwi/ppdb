<?php

namespace Tests\Feature;

use App\Jobs\ProcessApplicantDocUploadJob;
use App\Models\PpdbApplicant;
use App\Models\PpdbDocument;
use App\Models\PpdbTrack;
use App\Models\PpdbUser;
use App\Models\PpdbWave;
use App\Models\School;
use App\Services\TenantContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicantStatusAndReuploadTest extends TestCase
{
    use RefreshDatabase;

    protected School $schoolA;
    protected School $schoolB;
    protected PpdbApplicant $applicantA;
    protected PpdbDocument $docA;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Queue::fake();

        $this->schoolA = School::factory()->create([
            'school_code' => 'sman1jakarta',
            'name' => 'SMAN 1 Jakarta',
            'status' => 'active',
        ]);

        $this->schoolB = School::factory()->create([
            'school_code' => 'sman2surabaya',
            'name' => 'SMAN 2 Surabaya',
            'status' => 'active',
        ]);

        $tenantService = app(TenantContextService::class);
        $tenantService->setTenant($this->schoolA);

        $wave = PpdbWave::create([
            'name' => 'Gelombang 1 Reguler',
            'academic_year' => '2026/2027',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(5),
            'is_active' => true,
        ]);

        $track = PpdbTrack::create([
            'name' => 'Jalur Zonasi',
            'slug' => 'jalur-zonasi',
            'is_active' => true,
        ]);

        $this->applicantA = PpdbApplicant::create([
            'wave_id' => $wave->id,
            'track_id' => $track->id,
            'registration_number' => 'REG-SMAN1JKT-2026-0001',
            'full_name' => 'Fauzan Hakim',
            'nik' => '3201012345670001',
            'birth_date' => '2010-06-15',
            'birth_place' => 'Jakarta',
            'gender' => 'L',
            'phone_whatsapp' => '081299887766',
            'status' => 'submitted',
        ]);

        $this->docA = PpdbDocument::create([
            'applicant_id' => $this->applicantA->id,
            'document_type' => 'kartu_keluarga',
            'original_file_name' => 'kk_original.jpg',
            'verification_status' => 'pending',
        ]);

        $tenantService->clear();
    }

    public function test_guest_can_view_status_lookup_page(): void
    {
        $response = $this->get('/sman1jakarta/cek-status');
        $response->assertStatus(200);
        $response->assertSee('Cek Status Pendaftaran');
        $response->assertSee('Nomor Registrasi');
        $response->assertSee('Tanggal Lahir');
    }

    public function test_guest_can_verify_and_redirect_to_status_detail(): void
    {
        $response = $this->post('/sman1jakarta/cek-status', [
            'registration_number' => 'REG-SMAN1JKT-2026-0001',
            'birth_date' => '2010-06-15',
        ]);

        $response->assertSessionHas('verified_applicant_id', $this->applicantA->id);
        $response->assertRedirect('/sman1jakarta/cek-status/REG-SMAN1JKT-2026-0001');

        $detailResponse = $this->get('/sman1jakarta/cek-status/REG-SMAN1JKT-2026-0001');
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Fauzan Hakim');
        $detailResponse->assertSee('Terdaftar');
    }

    public function test_guest_cannot_view_status_detail_without_verification(): void
    {
        $response = $this->get('/sman1jakarta/cek-status/REG-SMAN1JKT-2026-0001');
        $response->assertRedirect('/sman1jakarta/cek-status');
        $response->assertSessionHas('error');
    }

    public function test_guest_with_invalid_credentials_is_rejected(): void
    {
        $response = $this->post('/sman1jakarta/cek-status', [
            'registration_number' => 'REG-SMAN1JKT-2026-0001',
            'birth_date' => '2015-01-01',
        ]);

        $response->assertSessionHas('error');
        $this->assertNull(session('verified_applicant_id'));
    }

    public function test_need_revision_status_shows_notes_and_allows_reupload(): void
    {
        $this->applicantA->update([
            'status' => 'need_revision',
            'verification_notes' => 'Foto Kartu Keluarga buram, mohon unggah scan asli yang jelas.',
        ]);

        $this->docA->update([
            'verification_status' => 'invalid',
            'rejection_reason' => 'Foto buram tidak terbaca',
        ]);

        // Login as verified applicant
        $this->withSession(['verified_applicant_id' => $this->applicantA->id]);

        $detail = $this->get('/sman1jakarta/cek-status/REG-SMAN1JKT-2026-0001');
        $detail->assertStatus(200);
        $detail->assertSee('Perlu Perbaikan');
        $detail->assertSee('Foto Kartu Keluarga buram');
        $detail->assertSee('Perbaiki Berkas Sekarang');

        $reuploadPage = $this->get('/sman1jakarta/cek-status/REG-SMAN1JKT-2026-0001/perbaiki');
        $reuploadPage->assertStatus(200);
        $reuploadPage->assertSee('Perbaikan Berkas Pendaftaran');
        $reuploadPage->assertSee('Foto buram tidak terbaca');

        // Submit revision
        $postResponse = $this->post('/sman1jakarta/cek-status/REG-SMAN1JKT-2026-0001/perbaiki', [
            'documents' => [
                'kartu_keluarga' => UploadedFile::fake()->image('kk_terbaru.jpg', 800, 600),
            ],
        ]);

        $postResponse->assertRedirect('/sman1jakarta/cek-status/REG-SMAN1JKT-2026-0001');

        // Refresh model & assert status flipped to submitted
        $this->applicantA->refresh();
        $this->assertEquals('submitted', $this->applicantA->status);
        $this->assertNull($this->applicantA->verification_notes);

        // Document updated
        $this->docA->refresh();
        $this->assertEquals('kk_terbaru.jpg', $this->docA->original_file_name);
        $this->assertEquals('pending', $this->docA->verification_status);

        // Job queued
        Queue::assertPushed(ProcessApplicantDocUploadJob::class);
    }

    public function test_tenant_isolation_prevents_cross_school_status_access(): void
    {
        // Try to access school A's applicant on school B
        $response = $this->post('/sman2surabaya/cek-status', [
            'registration_number' => 'REG-SMAN1JKT-2026-0001',
            'birth_date' => '2010-06-15',
        ]);

        $response->assertSessionHas('error');
    }
}

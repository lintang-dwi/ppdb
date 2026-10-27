<?php

namespace Tests\Feature;

use App\Models\PpdbUser;
use App\Models\School;
use App\Services\GoogleDriveClientService;
use App\Services\TenantContextService;
use Google\Service\Drive;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class CmsGoogleDriveTenantTest extends TestCase
{
    use RefreshDatabase;

    protected School $schoolA;
    protected School $schoolB;
    protected PpdbUser $staffA;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->staffA = PpdbUser::create([
            'name' => 'Admin SMAN 1',
            'email' => 'admin@sman1jkt.sch.id',
            'password' => Hash::make('password123'),
        ]);

        $tenantService->clear();
    }

    public function test_guest_cannot_access_gdrive_settings(): void
    {
        $response = $this->get('/sman1jakarta/cms/google-drive');
        $response->assertRedirect('/sman1jakarta/cms/login');

        $connectResponse = $this->get('/sman1jakarta/cms/google-drive/connect');
        $connectResponse->assertRedirect('/sman1jakarta/cms/login');
    }

    public function test_authenticated_staff_can_view_gdrive_settings_when_disconnected(): void
    {
        $this->actingAs($this->staffA, 'ppdb');

        $response = $this->get('/sman1jakarta/cms/google-drive');
        $response->assertStatus(200);
        $response->assertSee('Google Drive Sekolah');
        $response->assertSee('Belum Terhubung');
        $response->assertSee('Hubungkan Akun Google Drive Sekolah');
    }

    public function test_authenticated_staff_can_view_gdrive_settings_when_connected(): void
    {
        $this->actingAs($this->staffA, 'ppdb');

        $this->schoolA->update([
            'google_drive_refresh_token' => 'dummy-secret-refresh-token',
            'google_drive_email' => 'ppdb@sman1jkt.sch.id',
        ]);

        $response = $this->get('/sman1jakarta/cms/google-drive');
        $response->assertStatus(200);
        $response->assertSee('Terhubung');
        $response->assertSee('ppdb@sman1jkt.sch.id');
        $response->assertSee('Putus Hubungan Akun');
    }

    public function test_staff_can_disconnect_gdrive(): void
    {
        $this->actingAs($this->staffA, 'ppdb');

        $this->schoolA->update([
            'google_drive_refresh_token' => 'valid-secret-token',
            'google_drive_email' => 'drive@sman1jkt.sch.id',
        ]);

        $this->assertTrue($this->schoolA->hasGoogleDriveConnected());

        $response = $this->delete('/sman1jakarta/cms/google-drive/disconnect');
        $response->assertRedirect('/sman1jakarta/cms/google-drive');
        $response->assertSessionHas('success');

        $this->schoolA->refresh();
        $this->assertFalse($this->schoolA->hasGoogleDriveConnected());
        $this->assertNull($this->schoolA->google_drive_refresh_token);
        $this->assertNull($this->schoolA->google_drive_email);
    }

    public function test_google_drive_refresh_token_is_strictly_encrypted_in_database(): void
    {
        $plainToken = '1//04ABCDefgh_SecretRefreshToken_12345';
        $this->schoolA->update([
            'google_drive_refresh_token' => $plainToken,
        ]);

        $this->schoolA->refresh();

        // 1. Raw database value is encrypted (not plain text)
        $rawDatabaseToken = $this->schoolA->getRawOriginal('google_drive_refresh_token');
        $this->assertNotEquals($plainToken, $rawDatabaseToken);
        $this->assertStringNotContainsString('SecretRefreshToken', $rawDatabaseToken);

        // 2. Decrypted method returns original plain token
        $this->assertEquals($plainToken, $this->schoolA->getGoogleDriveRefreshTokenDecrypted());
    }

    public function test_google_drive_service_resolves_school_token_dynamically(): void
    {
        // Set fake client credentials
        config([
            'services.google_drive.client_id' => 'test-client-id.apps.googleusercontent.com',
            'services.google_drive.client_secret' => 'test-client-secret',
            'services.google_drive.refresh_token' => null, // No global fallback
        ]);

        // School B has no token
        $service = new GoogleDriveClientService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Sekolah [sman2surabaya] belum menghubungkan akun Google Drive.');
        $service->getDriveServiceForSchool('sman2surabaya');
    }

    public function test_ensure_applicant_folder_uses_school_specific_drive(): void
    {
        $mockDrive = Mockery::mock(Drive::class);
        $mockFiles = Mockery::mock(Drive\Resource\Files::class);
        $mockDrive->files = $mockFiles;

        $fileList = new \Google\Service\Drive\FileList();
        $fileList->setFiles([]);

        $mockFolder = new \Google\Service\Drive\DriveFile(['id' => 'folder_12345']);

        // Expect folder list and creation queries
        $mockFiles->shouldReceive('listFiles')->andReturn($fileList);
        $mockFiles->shouldReceive('create')->andReturn($mockFolder);

        $service = new GoogleDriveClientService($mockDrive);
        $folderId = $service->ensureApplicantFolder('sman1jakarta', '2026', 'REG-SMAN1-2026-0001', 'Budi Santoso');

        $this->assertEquals('folder_12345', $folderId);
    }
}

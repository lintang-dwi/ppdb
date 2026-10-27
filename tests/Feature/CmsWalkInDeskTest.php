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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CmsWalkInDeskTest extends TestCase
{
    use RefreshDatabase;

    protected School $schoolA;
    protected School $schoolB;
    protected PpdbUser $staffA;
    protected PpdbWave $waveA;
    protected PpdbTrack $trackA;

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

        $this->staffA = PpdbUser::create([
            'name' => 'Petugas Loket SMAN 1',
            'email' => 'loket@sman1jkt.sch.id',
            'password' => Hash::make('password123'),
        ]);

        $this->waveA = PpdbWave::create([
            'name' => 'Gelombang 1 Offline/Online',
            'academic_year' => '2026/2027',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(10),
            'is_active' => true,
        ]);

        $this->trackA = PpdbTrack::create([
            'name' => 'Jalur Reguler Umum',
            'slug' => 'jalur-reguler-umum',
            'is_active' => true,
        ]);

        $tenantService->clear();
    }

    public function test_guest_cannot_access_walk_in_desk(): void
    {
        $response = $this->get('/sman1jakarta/cms/walk-in');
        $response->assertRedirect('/sman1jakarta/cms/login');

        $postResponse = $this->post('/sman1jakarta/cms/walk-in', []);
        $postResponse->assertRedirect('/sman1jakarta/cms/login');
    }

    public function test_authenticated_staff_can_view_walk_in_form(): void
    {
        $this->actingAs($this->staffA, 'ppdb');

        $response = $this->get('/sman1jakarta/cms/walk-in');
        $response->assertStatus(200);
        $response->assertSee('Daftar di Loket');
        $response->assertSee('Gelombang 1 Offline/Online');
        $response->assertSee('Jalur Reguler Umum');
        $response->assertSee('Berkas fisik diterima langsung di loket');
    }

    public function test_staff_can_register_walk_in_applicant_with_physical_docs(): void
    {
        $this->actingAs($this->staffA, 'ppdb');

        $payload = [
            'wave_id' => $this->waveA->id,
            'track_id' => $this->trackA->id,
            'full_name' => 'Bambang Sudarsono',
            'nik' => '3201019908070003',
            'gender' => 'L',
            'birth_place' => 'Jakarta Barat',
            'birth_date' => '2010-04-12',
            'phone_whatsapp' => '085711223344',
            'is_physical_submitted' => 1,
            'custom_fields' => [
                'catatan_loket' => 'Berkas map kuning diserahkan lengkap',
            ],
        ];

        $response = $this->post('/sman1jakarta/cms/walk-in', $payload);
        $response->assertRedirect('/sman1jakarta/cms/walk-in');
        $response->assertSessionHas('success');
        $response->assertSessionHas('registered_applicant');

        $applicant = PpdbApplicant::where('full_name', 'Bambang Sudarsono')->first();
        $this->assertNotNull($applicant);
        $this->assertEquals('offline_walk_in', $applicant->registration_origin);
        $this->assertEquals('verified', $applicant->status);
        $this->assertEquals('complete_physical', $applicant->document_completeness);
        $this->assertStringStartsWith('REG-SMAN1JAKARTA-', $applicant->registration_number);
        $this->assertEquals('Petugas Loket SMAN 1', $applicant->verified_by);
        $this->assertNotNull($applicant->verified_at);
    }

    public function test_staff_can_register_walk_in_with_optional_file_upload_and_job_dispatched(): void
    {
        $this->actingAs($this->staffA, 'ppdb');

        $payload = [
            'wave_id' => $this->waveA->id,
            'track_id' => $this->trackA->id,
            'full_name' => 'Siti Nurhaliza',
            'nik' => '3201019908070004',
            'gender' => 'P',
            'birth_place' => 'Jakarta',
            'birth_date' => '2010-09-20',
            'phone_whatsapp' => '081299112233',
            'is_physical_submitted' => 1,
            'documents' => [
                'berkas_loket' => UploadedFile::fake()->create('ijazah_scan.pdf', 300),
            ],
        ];

        $response = $this->post('/sman1jakarta/cms/walk-in', $payload);
        $response->assertRedirect('/sman1jakarta/cms/walk-in');

        $applicant = PpdbApplicant::where('full_name', 'Siti Nurhaliza')->first();
        $this->assertNotNull($applicant);
        $this->assertCount(1, $applicant->documents);

        $doc = $applicant->documents->first();
        $this->assertEquals('berkas_loket', $doc->document_type);
        $this->assertEquals('ijazah_scan.pdf', $doc->original_file_name);
        $this->assertTrue($doc->is_physical_submitted);

        Queue::assertPushed(ProcessApplicantDocUploadJob::class);
    }

    public function test_tenant_isolation_on_walk_in_desk(): void
    {
        $this->actingAs($this->staffA, 'ppdb');

        // Staff of school A tries to access school B's walk-in desk
        $response = $this->get('/sman2surabaya/cms/walk-in');
        $response->assertRedirect('/sman2surabaya/cms/login');
    }
}

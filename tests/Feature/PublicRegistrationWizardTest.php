<?php

namespace Tests\Feature;

use App\Models\PpdbApplicant;
use App\Models\PpdbFormField;
use App\Models\PpdbTrack;
use App\Models\PpdbWave;
use App\Models\School;
use App\Services\RegistrationNumberService;
use App\Services\TenantContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicRegistrationWizardTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected PpdbWave $openWave;

    protected PpdbTrack $track;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->school = School::factory()->create([
            'school_code' => 'sman1jakarta',
            'name' => 'SMAN 1 Jakarta',
            'subdomain' => 'sman1jkt',
            'custom_domain' => 'ppdb.sman1jkt.sch.id',
            'custom_domain_verified' => true,
            'status' => 'active',
        ]);

        $tenantService = app(TenantContextService::class);
        $tenantService->setTenant($this->school);

        // Open wave for 2026/2027
        $this->openWave = PpdbWave::create([
            'name' => 'Gelombang 1 Reguler',
            'academic_year' => '2026/2027',
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(10),
            'is_active' => true,
        ]);

        // Track
        $this->track = PpdbTrack::create([
            'name' => 'Jalur Zonasi',
            'slug' => 'jalur-zonasi',
            'quota' => 100,
            'description' => 'Bagi calon siswa radius terdekat',
            'is_active' => true,
        ]);

        // Dynamic form field
        PpdbFormField::create([
            'step' => 'asal_sekolah',
            'field_key' => 'previous_school_name',
            'field_label' => 'Nama Sekolah Asal',
            'field_type' => 'text',
            'is_required' => true,
            'order_index' => 1,
        ]);

        $tenantService->clear();
    }

    public function test_landing_page_renders_school_branding_and_active_tracks(): void
    {
        // Path prefix mode
        $res = $this->get('/sman1jakarta');
        $res->assertStatus(200);
        $res->assertSee('SMAN 1 Jakarta');
        $res->assertSee('Pendaftaran Sedang Dibuka');
        $res->assertSee('Gelombang 1 Reguler');
        $res->assertSee('Jalur Zonasi');

        // Custom domain mode
        $resCustom = $this->get('http://ppdb.sman1jkt.sch.id');
        $resCustom->assertStatus(200);
        $resCustom->assertSee('SMAN 1 Jakarta');
    }

    public function test_registration_blocked_when_period_is_closed(): void
    {
        // Deactivate wave
        $this->openWave->update(['is_active' => false]);

        $res = $this->get('/sman1jakarta/daftar');
        $res->assertStatus(200);
        $res->assertSee('Pendaftaran Sedang Ditutup');
    }

    public function test_applicant_can_access_wizard_and_submit_form(): void
    {
        \Illuminate\Support\Facades\Queue::fake([\App\Jobs\ProcessApplicantDocUploadJob::class]);

        // 1. Visit wizard page
        $res = $this->get('/sman1jakarta/daftar');
        $res->assertStatus(200);
        $res->assertSee('Pilih Jalur Pendaftaran');
        $res->assertSee('Jalur Zonasi');
        $res->assertSee('Nama Sekolah Asal');

        // 2. Submit form with file
        $fakeDoc = UploadedFile::fake()->create('kartu_keluarga.jpg', 200, 'image/jpeg');

        $payload = [
            'wave_id' => $this->openWave->id,
            'track_id' => $this->track->id,
            'full_name' => 'Ahmad Dahlan',
            'nik' => '3201012345670001',
            'birth_place' => 'Jakarta',
            'birth_date' => '2010-08-17',
            'gender' => 'L',
            'phone_whatsapp' => '081234567890',
            'custom_fields' => [
                'previous_school_name' => 'SMP Negeri 1 Jakarta',
            ],
            'documents' => [
                'kartu_keluarga' => $fakeDoc,
            ],
        ];

        $submitRes = $this->post('/sman1jakarta/daftar', $payload);
        $submitRes->assertRedirect();

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\ProcessApplicantDocUploadJob::class);

        // 3. Check applicant in DB
        $applicant = PpdbApplicant::where('school_id', 'sman1jakarta')->first();
        $this->assertNotNull($applicant);
        $this->assertEquals('Ahmad Dahlan', $applicant->full_name);
        $this->assertEquals('3201012345670001', $applicant->nik);
        $this->assertEquals('submitted', $applicant->status);
        $this->assertEquals('SMP Negeri 1 Jakarta', $applicant->custom_fields['previous_school_name']);

        // Check registration number format: REG-SMAN1JAKARTA-{YEAR}-0001
        $this->assertStringStartsWith('REG-SMAN1JAKARTA-', $applicant->registration_number);
        $this->assertStringEndsWith('-0001', $applicant->registration_number);

        // 4. Check document created in DB
        $this->assertDatabaseHas('ppdb_documents', [
            'school_id' => 'sman1jakarta',
            'applicant_id' => $applicant->id,
            'document_type' => 'kartu_keluarga',
            'original_file_name' => 'kartu_keluarga.jpg',
            'verification_status' => 'pending',
        ]);

        // 5. Check success page
        $successRes = $this->get("/sman1jakarta/daftar/selesai?reg={$applicant->registration_number}");
        $successRes->assertStatus(200);
        $successRes->assertSee($applicant->registration_number);
        $successRes->assertSee('Ahmad Dahlan');
        $successRes->assertSee('Unduh Bukti Pendaftaran');
    }

    public function test_sequential_registration_number_generation_increments_properly(): void
    {
        $numberService = app(RegistrationNumberService::class);

        $num1 = $numberService->generate('sman1jakarta', '2026');
        $this->assertEquals('REG-SMAN1JAKARTA-2026-0001', $num1);

        // Simulate creation of applicant with num1
        PpdbApplicant::create([
            'school_id' => 'sman1jakarta',
            'wave_id' => $this->openWave->id,
            'track_id' => $this->track->id,
            'registration_number' => $num1,
            'full_name' => 'Siswa Pertama',
            'nik' => '3201010000000001',
            'birth_date' => '2010-01-01',
            'gender' => 'L',
            'phone_whatsapp' => '08111111111',
            'status' => 'submitted',
        ]);

        $num2 = $numberService->generate('sman1jakarta', '2026');
        $this->assertEquals('REG-SMAN1JAKARTA-2026-0002', $num2);
    }
}

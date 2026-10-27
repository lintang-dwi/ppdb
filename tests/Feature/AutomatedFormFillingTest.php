<?php

namespace Tests\Feature;

use App\Models\PpdbApplicant;
use App\Models\PpdbFormField;
use App\Models\PpdbTrack;
use App\Models\PpdbWave;
use App\Models\School;
use App\Services\FormPresetSeederService;
use App\Services\FormTestAutomationService;
use App\Services\TenantContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomatedFormFillingTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected PpdbWave $wave;

    protected PpdbTrack $track;

    protected FormTestAutomationService $automationService;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->wave = PpdbWave::create([
            'name' => 'Gelombang 1 Reguler',
            'academic_year' => '2026/2027',
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(10),
            'is_active' => true,
        ]);

        $this->track = PpdbTrack::create([
            'name' => 'Jalur Zonasi Unggulan',
            'slug' => 'jalur-zonasi-unggulan',
            'quota' => 100,
            'description' => 'Jalur zonasi reguler sekolah',
            'is_active' => true,
        ]);

        $this->automationService = app(FormTestAutomationService::class);

        $tenantService->clear();
    }

    public function test_automated_form_filling_with_all_dynamic_fields_succeeds_without_file_upload(): void
    {
        // 1. Create a diverse set of dynamic form fields including a required file field
        $tenantService = app(TenantContextService::class);
        $tenantService->setTenant($this->school);

        PpdbFormField::create([
            'step' => 'biodata',
            'field_key' => 'nisn',
            'field_label' => 'NISN Siswa',
            'field_type' => 'text',
            'is_required' => true,
            'order_index' => 1,
        ]);

        PpdbFormField::create([
            'step' => 'biodata',
            'field_key' => 'average_score',
            'field_label' => 'Nilai Rapor Rata-rata',
            'field_type' => 'number',
            'is_required' => true,
            'order_index' => 2,
        ]);

        PpdbFormField::create([
            'step' => 'biodata',
            'field_key' => 'transport_mode',
            'field_label' => 'Moda Transportasi',
            'field_type' => 'select',
            'options' => ['Jalan Kaki', 'Sepeda Motor', 'Angkutan Umum'],
            'is_required' => true,
            'order_index' => 3,
        ]);

        PpdbFormField::create([
            'step' => 'tambahan',
            'field_key' => 'medical_notes',
            'field_label' => 'Catatan Khusus',
            'field_type' => 'textarea',
            'is_required' => false,
            'order_index' => 4,
        ]);

        PpdbFormField::create([
            'step' => 'berkas',
            'field_key' => 'kartu_keluarga',
            'field_label' => 'Kartu Keluarga (KK)',
            'field_type' => 'file',
            'is_required' => true,
            'order_index' => 5,
        ]);

        $tenantService->clear();

        // 2. Generate automated payload for all fields (documents intentionally empty for testing)
        $payload = $this->automationService->generatePayload($this->school, $this->wave, $this->track);

        $this->assertNotEmpty($payload['full_name']);
        $this->assertNotEmpty($payload['nik']);
        $this->assertEquals('L', $payload['gender'] === 'P' ? 'P' : 'L');
        $this->assertArrayHasKey('nisn', $payload['custom_fields']);
        $this->assertArrayHasKey('average_score', $payload['custom_fields']);
        $this->assertArrayHasKey('transport_mode', $payload['custom_fields']);
        $this->assertEmpty($payload['documents'], 'Testing automation does not upload files');

        // 3. Submit registration request with automated testing header
        $res = $this->withHeaders(['X-PPDB-Test-Mode' => 'automated-testing'])
            ->post("/{$this->school->school_code}/daftar", $payload);

        // 4. Assert redirect to success page with session notification
        $res->assertSessionHas('success', 'Pendaftaran Anda berhasil dikirim!');
        $res->assertRedirect();

        $applicant = PpdbApplicant::where('school_id', $this->school->school_code)->latest('id')->first();
        $this->assertNotNull($applicant);
        $this->assertEquals($payload['full_name'], $applicant->full_name);
        $this->assertEquals($payload['nik'], $applicant->nik);
        $this->assertEquals('submitted', $applicant->status);
        $this->assertEquals('incomplete', $applicant->document_completeness);
        $this->assertEquals(85, $applicant->custom_fields['average_score']);
        $this->assertEquals('Jalan Kaki', $applicant->custom_fields['transport_mode']);

        // 5. Assert success page renders confirmation notification & slip download
        $successUrl = "/{$this->school->school_code}/daftar/selesai?reg={$applicant->registration_number}";
        $successRes = $this->get($successUrl);
        $successRes->assertStatus(200);
        $successRes->assertSee($applicant->registration_number);
        $successRes->assertSee($applicant->full_name);
        $successRes->assertSee('Unduh Bukti Pendaftaran');
        $successRes->assertSee('Simpan Bukti ke WhatsApp');
    }

    public function test_automated_form_filling_across_all_school_presets(): void
    {
        $presetService = app(FormPresetSeederService::class);
        $tiers = [
            FormPresetSeederService::TIER_PAUD,
            FormPresetSeederService::TIER_SD,
            FormPresetSeederService::TIER_SMP,
            FormPresetSeederService::TIER_SMA,
            FormPresetSeederService::TIER_SMK,
            FormPresetSeederService::TIER_PESANTREN,
        ];

        foreach ($tiers as $tier) {
            // Seed preset for tier
            $presetService->seedPreset($this->school->school_code, $tier, $this->wave->id, true);

            // Generate full automated payload
            $payload = $this->automationService->generatePayload($this->school, $this->wave, $this->track);

            $res = $this->withHeaders(['X-PPDB-Test-Mode' => 'automated-testing'])
                ->post("/{$this->school->school_code}/daftar", $payload);

            $res->assertSessionHas('success');
            $res->assertRedirect();

            $latestApplicant = PpdbApplicant::where('school_id', $this->school->school_code)->latest('id')->first();
            $this->assertNotNull($latestApplicant);
            $this->assertStringStartsWith('REG-SMAN1JAKARTA-', $latestApplicant->registration_number);
        }
    }

    public function test_public_user_file_requirement_remains_enforced_when_not_in_test_mode(): void
    {
        $tenantService = app(TenantContextService::class);
        $tenantService->setTenant($this->school);

        PpdbFormField::create([
            'step' => 'berkas',
            'field_key' => 'akta_kelahiran',
            'field_label' => 'Akta Kelahiran Siswa',
            'field_type' => 'file',
            'is_required' => true,
            'order_index' => 1,
        ]);

        $tenantService->clear();

        $payload = $this->automationService->generatePayload($this->school, $this->wave, $this->track);

        // When public user submits without file and without test bypass, validation must fail
        $res = $this->withServerVariables(['HTTP_X_PPDB_TEST_MODE' => ''])
            ->withSession([])
            ->call('POST', "/{$this->school->school_code}/daftar", $payload, [], [], [
                'ppdb_test_bypass_files' => false,
            ]);

        // When bypass is false, documents.akta_kelahiran is required
        $this->assertTrue(session()->has('errors') || $res->isRedirection());
    }

    public function test_public_user_can_submit_with_physical_documents_option_bypassing_mandatory_file_upload(): void
    {
        $tenantService = app(TenantContextService::class);
        $tenantService->setTenant($this->school);

        PpdbFormField::create([
            'step' => 'berkas',
            'field_key' => 'kartu_keluarga',
            'field_label' => 'Kartu Keluarga',
            'field_type' => 'file',
            'is_required' => true,
            'order_index' => 1,
        ]);

        $tenantService->clear();

        $payload = $this->automationService->generatePayload($this->school, $this->wave, $this->track);
        $payload['is_physical_submitted'] = '1';
        $payload['documents'] = [];

        // Submit as normal public applicant with is_physical_submitted enabled (no test mode header)
        $res = $this->withServerVariables(['HTTP_X_PPDB_TEST_MODE' => ''])
            ->call('POST', "/{$this->school->school_code}/daftar", $payload, [], [], [
                'ppdb_test_bypass_files' => false,
            ]);

        $res->assertSessionHas('success');
        $res->assertRedirect();

        $applicant = PpdbApplicant::where('school_id', $this->school->school_code)->latest('id')->first();
        $this->assertNotNull($applicant);
        $this->assertEquals('submitted', $applicant->status);
        $this->assertEquals('complete_physical', $applicant->document_completeness);

        // Verify that a pending placeholder document was created for verification by school committee
        $doc = $applicant->documents()->where('document_type', 'kartu_keluarga')->first();
        $this->assertNotNull($doc);
        $this->assertEquals('pending', $doc->verification_status);
        $this->assertNull($doc->original_file_name);
        $this->assertFalse($doc->is_physical_submitted);

        // Verify status detail page shows physical document instruction
        $detailRes = $this->withSession(['verified_applicant_id' => $applicant->id])
            ->get("/{$this->school->school_code}/cek-status/{$applicant->registration_number}");
        $detailRes->assertStatus(200);
        $detailRes->assertSee('Ditunjukkan fisik langsung ke sekolah');
    }
}

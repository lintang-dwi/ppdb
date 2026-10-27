<?php

namespace Tests\Feature;

use App\Models\PpdbApplicant;
use App\Models\PpdbDocument;
use App\Models\PpdbFormField;
use App\Models\PpdbTrack;
use App\Models\PpdbUser;
use App\Models\PpdbWave;
use App\Models\School;
use App\Services\FormPresetSeederService;
use App\Services\TenantContextService;
use Database\Seeders\PpdbDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseAndModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_usable_school_and_domain_records(): void
    {
        $this->seed(PpdbDemoSeeder::class);

        $school = School::find('sman1jakarta');
        $this->assertNotNull($school);
        $this->assertEquals('SMAN 1 Jakarta', $school->name);

        $tenantService = app(TenantContextService::class);
        $tenantService->setTenant($school);

        // Verify Wave
        $wave = PpdbWave::first();
        $this->assertNotNull($wave);
        $this->assertEquals('Gelombang 1 - Reguler & Prestasi', $wave->name);
        $this->assertTrue($wave->isOpen());

        // Verify Tracks
        $tracks = PpdbTrack::ordered()->get();
        $this->assertCount(2, $tracks);
        $this->assertEquals('Reguler', $tracks->first()->name);

        // Verify Staff User
        $user = PpdbUser::where('email', 'panitia@sman1jkt.sch.id')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Panitia PPDB SMAN 1', $user->name);

        // Verify Form Preset
        $fields = PpdbFormField::ordered()->get();
        $this->assertGreaterThanOrEqual(5, $fields->count());

        // Verify Applicant & Document
        $applicant = PpdbApplicant::where('registration_number', 'REG-SMAN1-2026-0001')->first();
        $this->assertNotNull($applicant);
        $this->assertEquals('Ahmad Fathoni', $applicant->full_name);
        $this->assertEquals('Terdaftar', $applicant->status_label);
        $this->assertEquals('online', $applicant->registration_origin);

        $this->assertCount(1, $applicant->documents);
        $document = $applicant->documents->first();
        $this->assertEquals('kk', $document->document_type);
        $this->assertEquals('pending', $document->verification_status);
    }

    public function test_form_preset_seeder_seeds_all_four_tiers(): void
    {
        $school = School::factory()->create(['school_code' => 'sch_test_presets']);
        $tenantService = app(TenantContextService::class);
        $tenantService->setTenant($school);

        $seeder = new FormPresetSeederService;

        $countPaud = $seeder->seedPreset('sch_test_presets', FormPresetSeederService::TIER_PAUD);
        $this->assertEquals(6, $countPaud);

        $countSd = $seeder->seedPreset('sch_test_presets', FormPresetSeederService::TIER_SD);
        $this->assertEquals(5, $countSd);

        $countSmp = $seeder->seedPreset('sch_test_presets', FormPresetSeederService::TIER_SMP);
        $this->assertEquals(5, $countSmp);

        $countSma = $seeder->seedPreset('sch_test_presets', FormPresetSeederService::TIER_SMA);
        $this->assertEquals(5, $countSma);
    }

    public function test_tenant_isolation_on_all_domain_models(): void
    {
        $schoolA = School::factory()->create(['school_code' => 'sch_alpha']);
        $schoolB = School::factory()->create(['school_code' => 'sch_beta']);

        $tenantService = app(TenantContextService::class);

        // Create records in school A
        $tenantService->setTenant($schoolA);
        $waveA = PpdbWave::factory()->create(['name' => 'Gelombang Alpha']);
        $trackA = PpdbTrack::factory()->create(['name' => 'Jalur Alpha']);
        $applicantA = PpdbApplicant::factory()->create([
            'wave_id' => $waveA->id,
            'track_id' => $trackA->id,
            'full_name' => 'Siswa Alpha',
        ]);
        PpdbDocument::factory()->create([
            'applicant_id' => $applicantA->id,
            'document_type' => 'ijazah',
        ]);

        // Switch context to school B
        $tenantService->setTenant($schoolB);

        $this->assertCount(0, PpdbWave::all());
        $this->assertCount(0, PpdbTrack::all());
        $this->assertCount(0, PpdbApplicant::all());
        $this->assertCount(0, PpdbDocument::all());

        // Switch back to school A
        $tenantService->setTenant($schoolA);

        $this->assertCount(1, PpdbWave::all());
        $this->assertCount(1, PpdbTrack::all());
        $this->assertCount(1, PpdbApplicant::all());
        $this->assertCount(1, PpdbDocument::all());
    }
}

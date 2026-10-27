<?php

namespace Tests\Feature;

use App\Models\PpdbFormField;
use App\Models\PpdbTrack;
use App\Models\PpdbUser;
use App\Models\PpdbWave;
use App\Models\School;
use App\Services\TenantContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CmsSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected School $schoolA;

    protected School $schoolB;

    protected PpdbUser $userA;

    protected PpdbUser $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schoolA = School::factory()->create([
            'school_code' => 'sman1jakarta',
            'name' => 'SMAN 1 Jakarta',
            'subdomain' => 'sman1jkt',
            'custom_domain' => 'ppdb.sman1jkt.sch.id',
            'custom_domain_verified' => true,
            'status' => 'active',
        ]);

        $this->schoolB = School::factory()->create([
            'school_code' => 'sman2surabaya',
            'name' => 'SMAN 2 Surabaya',
            'subdomain' => 'sman2sby',
            'custom_domain' => 'ppdb.sman2sby.sch.id',
            'custom_domain_verified' => true,
            'status' => 'active',
        ]);

        $tenantService = app(TenantContextService::class);

        $tenantService->setTenant($this->schoolA);
        $this->userA = PpdbUser::create([
            'name' => 'Panitia A',
            'email' => 'panitia@sman1jkt.sch.id',
            'password' => Hash::make('secret123'),
        ]);

        $tenantService->setTenant($this->schoolB);
        $this->userB = PpdbUser::create([
            'name' => 'Panitia B',
            'email' => 'panitia@sman2sby.sch.id',
            'password' => Hash::make('secret123'),
        ]);

        $tenantService->clear();
    }

    public function test_staff_can_view_and_manage_waves(): void
    {
        $this->actingAs($this->userA, 'ppdb');

        // 1. Visit wave settings page
        $res = $this->get('/sman1jakarta/cms/waves');
        $res->assertStatus(200);
        $res->assertSee('Gelombang Pendaftaran');

        // 2. Create new wave
        $postRes = $this->post('/sman1jakarta/cms/waves', [
            'name' => 'Gelombang 1 Reguler',
            'academic_year' => '2026/2027',
            'start_date' => '2026-05-01 08:00:00',
            'end_date' => '2026-06-30 23:59:00',
            'announcement_date' => '2026-07-05 10:00:00',
            'is_active' => 1,
        ]);

        $postRes->assertRedirect('/sman1jakarta/cms/waves');
        $this->assertDatabaseHas('ppdb_waves', [
            'school_id' => 'sman1jakarta',
            'name' => 'Gelombang 1 Reguler',
            'academic_year' => '2026/2027',
            'is_active' => 1,
        ]);

        $wave = PpdbWave::where('school_id', 'sman1jakarta')->first();

        // 3. Update wave
        $putRes = $this->put("/sman1jakarta/cms/waves/{$wave->id}", [
            'name' => 'Gelombang 1 Reguler Revisi',
            'academic_year' => '2026/2027',
            'start_date' => '2026-05-01 08:00:00',
            'end_date' => '2026-07-05 23:59:00',
            'is_active' => 1,
        ]);

        $putRes->assertRedirect('/sman1jakarta/cms/waves');
        $this->assertEquals('Gelombang 1 Reguler Revisi', $wave->fresh()->name);

        // 4. Toggle active status
        $toggleRes = $this->post("/sman1jakarta/cms/waves/{$wave->id}/toggle");
        $toggleRes->assertRedirect('/sman1jakarta/cms/waves');
        $this->assertFalse((bool) $wave->fresh()->is_active);
    }

    public function test_staff_can_view_and_manage_tracks(): void
    {
        $this->actingAs($this->userA, 'ppdb');

        // 1. Visit track settings page
        $res = $this->get('/sman1jakarta/cms/tracks');
        $res->assertStatus(200);
        $res->assertSee('Jalur Masuk');

        // 2. Create track with auto slug
        $postRes = $this->post('/sman1jakarta/cms/tracks', [
            'name' => 'Jalur Prestasi Akademik',
            'quota' => 50,
            'description' => 'Bagi pemenang olimpiade sains',
            'is_active' => 1,
        ]);

        $postRes->assertRedirect('/sman1jakarta/cms/tracks');
        $this->assertDatabaseHas('ppdb_tracks', [
            'school_id' => 'sman1jakarta',
            'name' => 'Jalur Prestasi Akademik',
            'slug' => 'jalur-prestasi-akademik',
            'quota' => 50,
        ]);

        $track = PpdbTrack::where('school_id', 'sman1jakarta')->first();

        // 3. Update track
        $putRes = $this->put("/sman1jakarta/cms/tracks/{$track->id}", [
            'name' => 'Jalur Prestasi Akademik & Seni',
            'quota' => 60,
            'description' => 'Bagi pemenang lomba akademik dan seni',
            'is_active' => 1,
        ]);

        $putRes->assertRedirect('/sman1jakarta/cms/tracks');
        $this->assertEquals('Jalur Prestasi Akademik & Seni', $track->fresh()->name);
        $this->assertEquals(60, $track->fresh()->quota);

        // 4. Toggle track status
        $toggleRes = $this->post("/sman1jakarta/cms/tracks/{$track->id}/toggle");
        $toggleRes->assertRedirect('/sman1jakarta/cms/tracks');
        $this->assertFalse((bool) $track->fresh()->is_active);
    }

    public function test_staff_can_manage_form_builder_and_apply_presets(): void
    {
        $this->actingAs($this->userA, 'ppdb');

        // 1. Visit form builder page
        $res = $this->get('/sman1jakarta/cms/form-builder');
        $res->assertStatus(200);
        $res->assertSee('Formulir Builder');

        // 2. Create dynamic field
        $postRes = $this->post('/sman1jakarta/cms/form-builder', [
            'step' => 'tambahan',
            'field_key' => 'ukuran_baju',
            'field_label' => 'Ukuran Seragam Sekolah',
            'field_type' => 'select',
            'options' => "S\nM\nL\nXL",
            'is_required' => 1,
            'order_index' => 10,
        ]);

        $postRes->assertRedirect('/sman1jakarta/cms/form-builder');
        $this->assertDatabaseHas('ppdb_form_fields', [
            'school_id' => 'sman1jakarta',
            'field_key' => 'ukuran_baju',
            'field_label' => 'Ukuran Seragam Sekolah',
            'field_type' => 'select',
            'is_required' => 1,
        ]);

        $field = PpdbFormField::where('school_id', 'sman1jakarta')->where('field_key', 'ukuran_baju')->first();
        $this->assertEquals(['S', 'M', 'L', 'XL'], $field->options);

        // 3. Update field
        $putRes = $this->put("/sman1jakarta/cms/form-builder/{$field->id}", [
            'step' => 'tambahan',
            'field_key' => 'ukuran_baju',
            'field_label' => 'Ukuran Baju Seragam Siswa',
            'field_type' => 'select',
            'options' => "S\nM\nL\nXL\nXXL",
            'is_required' => 0,
            'order_index' => 12,
        ]);

        $putRes->assertRedirect('/sman1jakarta/cms/form-builder');
        $this->assertEquals('Ukuran Baju Seragam Siswa', $field->fresh()->field_label);
        $this->assertFalse((bool) $field->fresh()->is_required);

        // 4. Apply SMA/SMK preset
        $presetRes = $this->post('/sman1jakarta/cms/form-builder/apply-preset', [
            'tier' => 'sma_smk',
        ]);

        $presetRes->assertRedirect('/sman1jakarta/cms/form-builder');
        // Check that preset fields were created for sman1jakarta
        $this->assertDatabaseHas('ppdb_form_fields', [
            'school_id' => 'sman1jakarta',
            'field_key' => 'major_choice_1',
        ]);
        $this->assertDatabaseHas('ppdb_form_fields', [
            'school_id' => 'sman1jakarta',
            'field_key' => 'nisn',
        ]);

        // 5. Change preset to SD/MI (replaces previous tier blueprint, preserves custom field)
        $sdPresetRes = $this->post('/sman1jakarta/cms/form-builder/apply-preset', [
            'tier' => 'sd_mi',
            'replace_existing' => 1,
        ]);
        $sdPresetRes->assertRedirect('/sman1jakarta/cms/form-builder');
        $this->assertDatabaseHas('ppdb_form_fields', [
            'school_id' => 'sman1jakarta',
            'field_key' => 'kk_number',
        ]);
        $this->assertDatabaseMissing('ppdb_form_fields', [
            'school_id' => 'sman1jakarta',
            'field_key' => 'major_choice_1',
        ]);
        $this->assertDatabaseHas('ppdb_form_fields', [
            'school_id' => 'sman1jakarta',
            'field_key' => 'ukuran_baju',
        ]);

        // 6. Change preset to Pesantren (replaces SD fields, seeds pondok program)
        $pesantrenRes = $this->post('/sman1jakarta/cms/form-builder/apply-preset', [
            'tier' => 'pesantren',
            'replace_existing' => 1,
        ]);
        $pesantrenRes->assertRedirect('/sman1jakarta/cms/form-builder');
        $this->assertDatabaseHas('ppdb_form_fields', [
            'school_id' => 'sman1jakarta',
            'field_key' => 'pesantren_program',
        ]);
        $this->assertDatabaseMissing('ppdb_form_fields', [
            'school_id' => 'sman1jakarta',
            'field_key' => 'kk_number',
        ]);
        $this->assertDatabaseHas('ppdb_form_fields', [
            'school_id' => 'sman1jakarta',
            'field_key' => 'ukuran_baju',
        ]);
    }

    public function test_tenant_isolation_prevents_access_to_other_school_settings(): void
    {
        $tenantService = app(TenantContextService::class);

        // Create wave, track, and form field in School B
        $tenantService->setTenant($this->schoolB);
        $waveB = PpdbWave::create([
            'name' => 'Gelombang Khusus Surabaya',
            'academic_year' => '2026/2027',
            'start_date' => '2026-05-01 08:00:00',
            'end_date' => '2026-06-30 23:59:00',
            'is_active' => true,
        ]);
        $trackB = PpdbTrack::create([
            'name' => 'Jalur Zonasi Surabaya',
            'slug' => 'jalur-zonasi-surabaya',
            'is_active' => true,
        ]);
        $fieldB = PpdbFormField::create([
            'step' => 'asal_sekolah',
            'field_key' => 'asal_smp_surabaya',
            'field_label' => 'Asal SMP Kota Surabaya',
            'field_type' => 'text',
            'is_required' => true,
            'order_index' => 1,
        ]);
        $tenantService->clear();

        // Log in as School A user
        $this->actingAs($this->userA, 'ppdb');

        // Attempting to update or toggle School B's wave from School A context must fail with 404
        $resWave = $this->put("/sman1jakarta/cms/waves/{$waveB->id}", [
            'name' => 'Hacked Wave',
            'academic_year' => '2026/2027',
            'start_date' => '2026-05-01 08:00:00',
            'end_date' => '2026-06-30 23:59:00',
        ]);
        $resWave->assertStatus(404);

        $resTrack = $this->put("/sman1jakarta/cms/tracks/{$trackB->id}", [
            'name' => 'Hacked Track',
        ]);
        $resTrack->assertStatus(404);

        $resField = $this->put("/sman1jakarta/cms/form-builder/{$fieldB->id}", [
            'step' => 'asal_sekolah',
            'field_key' => 'asal_smp_surabaya',
            'field_label' => 'Hacked Field',
            'field_type' => 'text',
        ]);
        $resField->assertStatus(404);
    }
}

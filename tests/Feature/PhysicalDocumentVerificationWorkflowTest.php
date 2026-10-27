<?php

namespace Tests\Feature;

use App\Models\PpdbApplicant;
use App\Models\PpdbDocument;
use App\Models\PpdbFormField;
use App\Models\PpdbTrack;
use App\Models\PpdbUser;
use App\Models\PpdbWave;
use App\Models\School;
use App\Services\TenantContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhysicalDocumentVerificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected PpdbUser $staff;

    protected PpdbWave $wave;

    protected PpdbTrack $track;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create([
            'school_code' => 'sman1jakarta',
            'name' => 'SMAN 1 Jakarta',
            'status' => 'active',
        ]);

        $tenantService = app(TenantContextService::class);
        $tenantService->setTenant($this->school);

        $this->staff = PpdbUser::create([
            'name' => 'Panitia Verifikasi Fisik',
            'email' => 'panitia@sman1jkt.sch.id',
            'password' => Hash::make('password123'),
        ]);

        $this->wave = PpdbWave::create([
            'name' => 'Gelombang 1 Reguler',
            'academic_year' => '2026/2027',
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(10),
            'is_active' => true,
        ]);

        $this->track = PpdbTrack::create([
            'name' => 'Jalur Reguler',
            'slug' => 'jalur-reguler',
            'is_active' => true,
        ]);

        // Define 3 required documents in form builder
        PpdbFormField::create([
            'step' => 'berkas',
            'field_key' => 'kartu_keluarga',
            'field_label' => 'Kartu Keluarga (KK)',
            'field_type' => 'file',
            'is_required' => true,
            'order_index' => 1,
        ]);

        PpdbFormField::create([
            'step' => 'berkas',
            'field_key' => 'akta_kelahiran',
            'field_label' => 'Akta Kelahiran',
            'field_type' => 'file',
            'is_required' => true,
            'order_index' => 2,
        ]);

        PpdbFormField::create([
            'step' => 'berkas',
            'field_key' => 'ijazah',
            'field_label' => 'Ijazah / Surat Keterangan Lulus',
            'field_type' => 'file',
            'is_required' => true,
            'order_index' => 3,
        ]);

        $tenantService->clear();
    }

    /**
     * Kondisi 1: Berkas kurang 1 (Sebagian berkas fisik diterima, sebagian masih kurang/pending).
     * Pendaftar online Cara 2 datang ke sekolah hanya membawa 2 dari 3 dokumen fisik.
     */
    public function test_partial_physical_submission_keeps_applicant_status_as_submitted_and_incomplete(): void
    {
        // 1. Applicant registers via Cara 2 (Online with is_physical_submitted = 1)
        $payload = [
            'wave_id' => $this->wave->id,
            'track_id' => $this->track->id,
            'full_name' => 'Dimas Arya Pratama',
            'nik' => '3201015504090001',
            'birth_date' => '2010-06-15',
            'birth_place' => 'Jakarta',
            'gender' => 'L',
            'phone_whatsapp' => '081234567890',
            'is_physical_submitted' => 1,
            'documents' => [],
        ];

        $regRes = $this->call('POST', "/{$this->school->school_code}/daftar", $payload);
        $regRes->assertRedirect();

        $applicant = PpdbApplicant::where('school_id', $this->school->school_code)
            ->where('full_name', 'Dimas Arya Pratama')
            ->firstOrFail();

        $this->assertEquals('online', $applicant->registration_origin);
        $this->assertEquals('submitted', $applicant->status);
        $this->assertEquals(3, $applicant->documents()->count());

        $docKK = $applicant->documents()->where('document_type', 'kartu_keluarga')->firstOrFail();
        $docAkta = $applicant->documents()->where('document_type', 'akta_kelahiran')->firstOrFail();
        $docIjazah = $applicant->documents()->where('document_type', 'ijazah')->firstOrFail();

        // 2. Staff at desk receives KK and Akta physical hardcopies, but Ijazah is missing
        $this->actingAs($this->staff, 'ppdb');

        $this->patch("/{$this->school->school_code}/cms/documents/{$docKK->id}/physical");
        $this->patch("/{$this->school->school_code}/cms/documents/{$docAkta->id}/physical");

        $docKK->refresh();
        $docAkta->refresh();
        $docIjazah->refresh();
        $applicant->refresh();

        // KK and Akta are marked physical received
        $this->assertTrue($docKK->is_physical_submitted);
        $this->assertTrue($docAkta->is_physical_submitted);
        // Ijazah is NOT received
        $this->assertFalse($docIjazah->is_physical_submitted);

        // System automatically keeps applicant document_completeness as incomplete because 1 document is missing
        $this->assertEquals('incomplete', $applicant->document_completeness);
        $this->assertEquals('submitted', $applicant->status);

        // 3. Public status detail shows clear indicators: KK and Akta are received, Ijazah is still waiting
        $statusRes = $this->withSession(['verified_applicant_id' => $applicant->id])
            ->get("/{$this->school->school_code}/cek-status/{$applicant->registration_number}");

        $statusRes->assertStatus(200);
        $statusRes->assertSee('Berkas fisik diterima panitia');
        $statusRes->assertSee('Ditunjukkan fisik langsung ke sekolah');
        $statusRes->assertSee('Terdaftar');
    }

    /**
     * Kondisi 2: Susulan berkas yang kurang (Orang tua kembali membawa berkas ke-3).
     * Saat berkas terakhir diserahkan, sistem otomatis mengubah kelengkapan menjadi complete_physical.
     */
    public function test_subsequent_delivery_of_missing_document_automatically_completes_physical_status(): void
    {
        // 1. Create applicant with 2 docs received and 1 doc pending
        $tenantService = app(TenantContextService::class);
        $tenantService->setTenant($this->school);

        $applicant = PpdbApplicant::create([
            'wave_id' => $this->wave->id,
            'track_id' => $this->track->id,
            'registration_number' => 'REG-SMAN1-2026-0088',
            'registration_origin' => 'online',
            'full_name' => 'Siti Nurhaliza',
            'nik' => '3201014402100002',
            'birth_date' => '2010-02-14',
            'phone_whatsapp' => '081298765432',
            'status' => 'submitted',
            'document_completeness' => 'incomplete',
        ]);

        $docKK = PpdbDocument::create([
            'applicant_id' => $applicant->id,
            'document_type' => 'kartu_keluarga',
            'is_physical_submitted' => true,
            'verification_status' => 'pending',
        ]);

        $docAkta = PpdbDocument::create([
            'applicant_id' => $applicant->id,
            'document_type' => 'akta_kelahiran',
            'is_physical_submitted' => true,
            'verification_status' => 'pending',
        ]);

        $docIjazah = PpdbDocument::create([
            'applicant_id' => $applicant->id,
            'document_type' => 'ijazah',
            'is_physical_submitted' => false,
            'verification_status' => 'pending',
        ]);

        $tenantService->clear();

        $this->actingAs($this->staff, 'ppdb');

        // 2. Parents deliver the missing 3rd document (Ijazah)
        $this->patch("/{$this->school->school_code}/cms/documents/{$docIjazah->id}/physical");

        $docIjazah->refresh();
        $applicant->refresh();

        $this->assertTrue($docIjazah->is_physical_submitted);
        // Automatically transitioned to complete_physical because all 3 documents are now physically submitted
        $this->assertEquals('complete_physical', $applicant->document_completeness);

        // 3. Staff verifies the applicant and transitions status to verified
        $verifyRes = $this->patch("/{$this->school->school_code}/cms/applicants/{$applicant->id}/status", [
            'status' => 'verified',
        ]);
        $verifyRes->assertRedirect();

        $applicant->refresh();
        $this->assertEquals('verified', $applicant->status);
        $this->assertNotNull($applicant->verified_at);
        $this->assertEquals('Panitia Verifikasi Fisik', $applicant->verified_by);

        // Public status view reflects verified status (Berkas Lengkap)
        $statusRes = $this->withSession(['verified_applicant_id' => $applicant->id])
            ->get("/{$this->school->school_code}/cek-status/{$applicant->registration_number}");
        $statusRes->assertStatus(200);
        $statusRes->assertSee('Berkas Lengkap');
    }

    /**
     * Kondisi 3: Berkas kurang diberi catatan "Minta Perbaikan" (need_revision).
     * Orang tua melihat catatan instruksi resmi dari panitia langsung di ponsel mereka.
     */
    public function test_missing_physical_document_marked_as_need_revision_shows_alert_to_applicant(): void
    {
        $tenantService = app(TenantContextService::class);
        $tenantService->setTenant($this->school);

        $applicant = PpdbApplicant::create([
            'wave_id' => $this->wave->id,
            'track_id' => $this->track->id,
            'registration_number' => 'REG-SMAN1-2026-0099',
            'registration_origin' => 'online',
            'full_name' => 'Farhan Ramadhan',
            'nik' => '3201016607090003',
            'birth_date' => '2009-07-20',
            'phone_whatsapp' => '081344556677',
            'status' => 'submitted',
            'document_completeness' => 'incomplete',
        ]);

        $docIjazah = PpdbDocument::create([
            'applicant_id' => $applicant->id,
            'document_type' => 'ijazah',
            'is_physical_submitted' => false,
            'verification_status' => 'pending',
        ]);

        $tenantService->clear();

        // Staff flags the missing/invalid document with revision note
        $this->actingAs($this->staff, 'ppdb');

        $note = 'Fotokopi Ijazah / SKL kurang 1 lembar, mohon disusulkan ke loket panitia sekolah.';
        $res = $this->patch("/{$this->school->school_code}/cms/documents/{$docIjazah->id}/verify", [
            'verification_status' => 'invalid',
            'rejection_reason' => $note,
            'flag_revision' => 1,
        ]);
        $res->assertRedirect();

        $docIjazah->refresh();
        $applicant->refresh();

        $this->assertEquals('invalid', $docIjazah->verification_status);
        $this->assertEquals($note, $docIjazah->rejection_reason);
        $this->assertEquals('need_revision', $applicant->status);
        $this->assertEquals($note, $applicant->verification_notes);

        // Applicant checks status from their phone
        $statusRes = $this->withSession(['verified_applicant_id' => $applicant->id])
            ->get("/{$this->school->school_code}/cek-status/{$applicant->registration_number}");

        $statusRes->assertStatus(200);
        $statusRes->assertSee('Perlu Perbaikan');
        $statusRes->assertSee('Catatan Perbaikan dari Panitia:');
        $statusRes->assertSee($note);
    }

    /**
     * Kondisi 4a: Walk-in Desk (Daftar di Loket) dengan berkas fisik lengkap.
     * Otomatis tercatat offline_walk_in, status verified, dan complete_physical.
     */
    public function test_walk_in_applicant_with_complete_physical_documents_is_directly_verified(): void
    {
        $this->actingAs($this->staff, 'ppdb');

        $payload = [
            'wave_id' => $this->wave->id,
            'track_id' => $this->track->id,
            'full_name' => 'Ratna Sari Dewi',
            'nik' => '3201017708090004',
            'birth_date' => '2010-08-08',
            'gender' => 'P',
            'birth_place' => 'Bandung',
            'phone_whatsapp' => '081299887766',
            'is_physical_submitted' => 1,
        ];

        $res = $this->post("/{$this->school->school_code}/cms/walk-in", $payload);
        $res->assertRedirect("/{$this->school->school_code}/cms/walk-in");
        $res->assertSessionHas('success');

        $applicant = PpdbApplicant::where('school_id', $this->school->school_code)
            ->where('full_name', 'Ratna Sari Dewi')
            ->firstOrFail();

        $this->assertEquals('offline_walk_in', $applicant->registration_origin);
        $this->assertEquals('verified', $applicant->status);
        $this->assertEquals('complete_physical', $applicant->document_completeness);
        $this->assertNotNull($applicant->verified_at);
        $this->assertEquals('Panitia Verifikasi Fisik', $applicant->verified_by);
    }

    /**
     * Kondisi 4b: Walk-in Desk (Daftar di Loket) ketika berkas pemohon belum lengkap.
     * is_physical_submitted tidak dicentang -> data tetap tersimpan aman sebagai submitted dan incomplete.
     */
    public function test_walk_in_applicant_with_missing_documents_is_safely_saved_as_submitted_incomplete(): void
    {
        $this->actingAs($this->staff, 'ppdb');

        $payload = [
            'wave_id' => $this->wave->id,
            'track_id' => $this->track->id,
            'full_name' => 'Bayu Kusuma',
            'nik' => '3201018809090005',
            'birth_date' => '2009-09-09',
            'gender' => 'L',
            'birth_place' => 'Jakarta',
            'phone_whatsapp' => '081255443322',
            'is_physical_submitted' => 0, // Berkas belum lengkap dibawa
        ];

        $res = $this->post("/{$this->school->school_code}/cms/walk-in", $payload);
        $res->assertRedirect("/{$this->school->school_code}/cms/walk-in");
        $res->assertSessionHas('success');

        $applicant = PpdbApplicant::where('school_id', $this->school->school_code)
            ->where('full_name', 'Bayu Kusuma')
            ->firstOrFail();

        // Data is saved safely, origin recorded, but status is kept at submitted & incomplete
        $this->assertEquals('offline_walk_in', $applicant->registration_origin);
        $this->assertEquals('submitted', $applicant->status);
        $this->assertEquals('incomplete', $applicant->document_completeness);
        $this->assertNull($applicant->verified_at);
    }

    /**
     * Uji Tampilan Form Asli (Real HTML Form): Wizard Pendaftaran Publik (/daftar)
     * Menguji bahwa halaman web benar-benar me-render elemen form asli HTML,
     * input NIK, nama, checkbox Cara 2, input file dokumen, serta atribut Alpine.js.
     */
    public function test_public_registration_wizard_renders_real_html_form_with_exact_inputs_and_physical_option(): void
    {
        $res = $this->get("/{$this->school->school_code}/daftar");

        $res->assertStatus(200);

        // 1. Tag <form> asli dengan method POST dan enctype multipart
        $res->assertSee('<form id="regForm"', false);
        $res->assertSee('action="'.tenant_route('applicant.register.store').'"', false);
        $res->assertSee('method="POST"', false);
        $res->assertSee('enctype="multipart/form-data"', false);

        // 2. Input pokok formulir asli
        $res->assertSee('name="wave_id"', false);
        $res->assertSee('name="track_id"', false);
        $res->assertSee('name="full_name"', false);
        $res->assertSee('name="nik"', false);
        $res->assertSee('name="gender"', false);
        $res->assertSee('name="birth_date"', false);
        $res->assertSee('name="phone_whatsapp"', false);

        // 3. Elemen Checkbox Cara 2 (Opsi Berkas Fisik Langsung)
        $res->assertSee('name="is_physical_submitted"', false);
        $res->assertSee('x-model="formData.is_physical_submitted"', false);
        $res->assertSee('@change="togglePhysicalOption()"', false);
        $res->assertSee('Tunjukkan berkas fisik langsung ke sekolah (tanpa unggah file)');

        // 4. Input file untuk ketiga berkas persyaratan
        $res->assertSee('name="documents[kartu_keluarga]"', false);
        $res->assertSee('name="documents[akta_kelahiran]"', false);
        $res->assertSee('name="documents[ijazah]"', false);
        $res->assertSee('data-originally-required="true"', false);

        // 5. Script Alpine.js handler terpasang
        $res->assertSee('togglePhysicalOption()', false);
        $res->assertSee('formData: {', false);
    }

    /**
     * Uji Tampilan Form Asli: CMS Detail Verifikasi Berkas (/cms/applicants/{id})
     * Menguji bahwa tombol dan form aksi panitia (+ Terima Fisik, Setujui, Minta Perbaikan)
     * benar-benar ter-render di HTML halaman panitia.
     */
    public function test_cms_applicant_detail_renders_real_verification_forms_and_buttons(): void
    {
        $tenantService = app(TenantContextService::class);
        $tenantService->setTenant($this->school);

        $applicant = PpdbApplicant::create([
            'wave_id' => $this->wave->id,
            'track_id' => $this->track->id,
            'registration_number' => 'REG-SMAN1-2026-0777',
            'registration_origin' => 'online',
            'full_name' => 'Indra Wijaya',
            'nik' => '3201019901010007',
            'birth_date' => '2010-01-01',
            'phone_whatsapp' => '081233445566',
            'status' => 'submitted',
        ]);

        $docKK = PpdbDocument::create([
            'applicant_id' => $applicant->id,
            'document_type' => 'kartu_keluarga',
            'is_physical_submitted' => false,
            'verification_status' => 'pending',
        ]);

        $tenantService->clear();

        $this->actingAs($this->staff, 'ppdb');

        $res = $this->get("/{$this->school->school_code}/cms/applicants/{$applicant->id}");

        $res->assertStatus(200);

        // Tombol dan form + Terima Fisik
        $res->assertSee("/documents/{$docKK->id}/physical", false);
        $res->assertSee('+ Terima Fisik');

        // Tombol dan form Setujui
        $res->assertSee("/documents/{$docKK->id}/verify", false);
        $res->assertSee('Setujui');

        // Tombol Minta Perbaikan dan input alasan
        $res->assertSee('Minta Perbaikan');
        $res->assertSee('name="rejection_reason"', false);

        // Tombol workflow status di header
        $res->assertSee('✓ Berkas Lengkap');
        $res->assertSee('✓ Diterima');
    }

    /**
     * Uji Tampilan Form Asli: Form Loket Walk-In (/cms/walk-in)
     * Menguji form input loket lengkap dengan checkbox berkas fisik.
     */
    public function test_cms_walk_in_renders_real_html_form_with_physical_checkbox(): void
    {
        $this->actingAs($this->staff, 'ppdb');

        $res = $this->get("/{$this->school->school_code}/cms/walk-in");

        $res->assertStatus(200);
        $res->assertSee('action="'.tenant_route('cms.walk-in.store').'"', false);
        $res->assertSee('name="full_name"', false);
        $res->assertSee('name="nik"', false);
        $res->assertSee('name="is_physical_submitted"', false);
        $res->assertSee('Berkas fisik diterima langsung di loket');
        $res->assertSee('Daftar di Loket &amp; Cetak Slip', false);
    }
}


<?php

namespace Tests\Feature;

use App\Models\PpdbApplicant;
use App\Models\PpdbDocument;
use App\Models\PpdbFormField;
use App\Models\PpdbSuperadmin;
use App\Models\PpdbTrack;
use App\Models\PpdbUser;
use App\Models\PpdbWave;
use App\Models\School;
use App\Services\FormPresetSeederService;
use App\Services\TenantContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class MasterPpdbFullLifecycleE2ETest extends TestCase
{
    use RefreshDatabase;

    /**
     * Master End-to-End Test:
     * Menguji seluruh siklus hidup PPDB Multi-Tenant secara komprehensif:
     * 1. Superadmin membuat dan mengelola tenant sekolah (multi-tenancy onboarding).
     * 2. Panitia sekolah mengatur gelombang, jalur masuk, dan template formulir preset SMP.
     * 3. Pendaftar online mendaftar via Cara 2 (opsi berkas fisik langsung / tanpa upload).
     * 4. Sistem menerbitkan nomor registrasi, slip PDF anti-hilang, dan link share WhatsApp.
     * 5. Pendaftar walk-in mendaftar di loket offline (dual-entry model).
     * 6. Panitia CMS memverifikasi dokumen fisik: kasus 1 berkas kurang (incomplete).
     * 7. Panitia menandai berkas kurang dengan catatan revisi (need_revision) yang tampil di HP pendaftar.
     * 8. Pendaftar menyusulkan berkas yang kurang, sistem otomatis mengubah menjadi complete_physical.
     * 9. Panitia meluluskan/menerima siswa (accepted), cetak bukti PDF, dan ekspor CSV.
     */
    public function test_complete_ppdb_lifecycle_from_tenant_creation_to_final_admission(): void
    {
        // =========================================================================
        // FASE 1: MULTI-TENANCY & SUPERADMIN ONBOARDING
        // =========================================================================

        // 1.1 Buat akun Superadmin Platform
        $superadmin = PpdbSuperadmin::create([
            'name' => 'Superadmin Cloud',
            'email' => 'superadmin@bitserver.cloud',
            'password' => Hash::make('SuperPass123'),
        ]);

        // 1.2 Login Superadmin
        $loginRes = $this->post('/superadmin/login', [
            'email' => 'superadmin@bitserver.cloud',
            'password' => 'SuperPass123',
        ]);
        $loginRes->assertRedirect('/superadmin');
        $this->assertAuthenticatedAs($superadmin, 'superadmin');

        // 1.3 Onboard Tenant Sekolah Baru ke dalam registry 'schools'
        $school = School::create([
            'school_code' => 'smpn1teladan',
            'uuid' => (string) Str::uuid(),
            'name' => 'SMP Negeri 1 Teladan',
            'subdomain' => 'smpn1teladan',
            'custom_domain' => 'ppdb.smpn1teladan.sch.id',
            'custom_domain_verified' => true,
            'status' => 'active',
        ]);

        // 1.4 Dashboard Superadmin mendeteksi sekolah baru
        $superDash = $this->actingAs($superadmin, 'superadmin')->get('/superadmin');
        $superDash->assertStatus(200);
        $superDash->assertSee('SMP Negeri 1 Teladan');
        $superDash->assertSee('smpn1teladan');

        // 1.5 Superadmin Impersonate ke CMS Panitia Sekolah
        $impersonateRes = $this->actingAs($superadmin, 'superadmin')
            ->post("/superadmin/impersonate/{$school->school_code}");
        $impersonateRes->assertRedirect("/{$school->school_code}/cms/dashboard");
        $this->assertAuthenticated('ppdb');

        // =========================================================================
        // FASE 2: PENGATURAN CMS SEKOLAH (GELOMBANG, JALUR, DAN PRESET FORMULIR)
        // =========================================================================

        $tenantService = app(TenantContextService::class);
        $tenantService->setTenant($school);

        // 2.1 Buat akun panitia sekolah resmi
        $staff = PpdbUser::create([
            'name' => 'Budi Santoso (Panitia PPDB)',
            'email' => 'panitia@smpn1teladan.sch.id',
            'password' => Hash::make('RahasiaPanitia123'),
        ]);

        $this->actingAs($staff, 'ppdb');

        // 2.2 Buat Gelombang Pendaftaran Aktif
        $waveRes = $this->post("/{$school->school_code}/cms/waves", [
            'name' => 'Gelombang 1 Reguler TA 2026/2027',
            'academic_year' => '2026/2027',
            'start_date' => now()->subDays(1)->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(14)->format('Y-m-d H:i:s'),
            'is_active' => 1,
        ]);
        $waveRes->assertRedirect("/{$school->school_code}/cms/waves");

        $wave = PpdbWave::where('name', 'Gelombang 1 Reguler TA 2026/2027')->firstOrFail();

        // 2.3 Buat Jalur Pendaftaran & Kuota
        $trackRes = $this->post("/{$school->school_code}/cms/tracks", [
            'name' => 'Jalur Zonasi Unggulan',
            'quota' => 120,
            'description' => 'Khusus calon siswa domisili terdekat',
            'is_active' => 1,
        ]);
        $trackRes->assertRedirect("/{$school->school_code}/cms/tracks");

        $track = PpdbTrack::where('name', 'Jalur Zonasi Unggulan')->firstOrFail();

        // 2.4 Terapkan Preset Formulir SMP (SMP/MTs Tier)
        $presetRes = $this->post("/{$school->school_code}/cms/form-builder/apply-preset", [
            'tier' => 'smp_mts',
            'wave_id' => $wave->id,
            'replace_existing' => 1,
        ]);
        $presetRes->assertRedirect("/{$school->school_code}/cms/form-builder");

        // Verifikasi field formulir preset SMP telah terkonfigurasi di database
        $this->assertTrue(PpdbFormField::where('field_key', 'nisn')->where('is_required', true)->exists());
        $this->assertTrue(PpdbFormField::where('field_key', 'previous_school_name')->where('is_required', true)->exists());
        $this->assertTrue(PpdbFormField::where('field_key', 'uniform_size')->where('is_required', true)->exists());

        // 2.5 Panitia melengkapi berkas persyaratan wajib (KK, Akta, SKL)
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
            'field_key' => 'skl',
            'field_label' => 'Surat Keterangan Lulus (SKL)',
            'field_type' => 'file',
            'is_required' => true,
            'order_index' => 3,
        ]);

        $tenantService->clear();

        // =========================================================================
        // FASE 3: PENDAFTARAN ONLINE END-USER (CARA 2: BERKAS FISIK LANGSUNG)
        // =========================================================================

        // 3.1 Calon siswa membuka portal landing page (via sub-path dan custom domain)
        $landingPath = $this->get("/{$school->school_code}");
        $landingPath->assertStatus(200);
        $landingPath->assertSee('SMP Negeri 1 Teladan');
        $landingPath->assertSee('Gelombang 1 Reguler TA 2026/2027');
        $landingPath->assertSee('Jalur Zonasi Unggulan');

        $landingDomain = $this->get('http://ppdb.smpn1teladan.sch.id');
        $landingDomain->assertStatus(200);
        $landingDomain->assertSee('SMP Negeri 1 Teladan');

        // 3.2 Calon siswa membuka formulir wizard 4 langkah (/daftar)
        $wizardPage = $this->get("/{$school->school_code}/daftar");
        $wizardPage->assertStatus(200);
        $wizardPage->assertSee('<form id="regForm"', false);
        $wizardPage->assertSee('name="is_physical_submitted"', false);
        $wizardPage->assertSee('Tunjukkan berkas fisik langsung ke sekolah (tanpa unggah file)');
        $wizardPage->assertSee('togglePhysicalOption()', false);

        // 3.3 Calon siswa mengisi formulir dan memilih Cara 2 (is_physical_submitted = 1, file kosong)
        $regPayload = [
            'wave_id' => $wave->id,
            'track_id' => $track->id,
            'full_name' => 'Muhammad Rayhan Pratama',
            'nik' => '3201011105100001',
            'birth_date' => '2010-05-11',
            'birth_place' => 'Jakarta Selatan',
            'gender' => 'L',
            'phone_whatsapp' => '081234567899',
            'is_physical_submitted' => 1,
            'custom_fields' => [
                'nisn' => '0098765432',
                'previous_school_name' => 'SD Negeri Teladan 01',
                'uniform_size' => 'M',
            ],
            'documents' => [],
        ];

        $regSubmit = $this->call('POST', "/{$school->school_code}/daftar", $regPayload);
        $regSubmit->assertRedirect();
        $regSubmit->assertSessionHas('success');

        $rayhan = PpdbApplicant::where('school_id', $school->school_code)
            ->where('full_name', 'Muhammad Rayhan Pratama')
            ->firstOrFail();

        // Verifikasi hasil database pendaftar online Cara 2
        $this->assertEquals('online', $rayhan->registration_origin);
        $this->assertEquals('submitted', $rayhan->status);
        $this->assertEquals('complete_physical', $rayhan->document_completeness);
        $this->assertEquals('0098765432', $rayhan->custom_fields['nisn']);

        // Verifikasi placeholder dokumen otomatis dibuat untuk diverifikasi panitia di sekolah
        $this->assertEquals(3, $rayhan->documents()->count());
        $docKK = $rayhan->documents()->where('document_type', 'kartu_keluarga')->firstOrFail();
        $docAkta = $rayhan->documents()->where('document_type', 'akta_kelahiran')->firstOrFail();
        $docSKL = $rayhan->documents()->where('document_type', 'skl')->firstOrFail();

        $this->assertEquals('pending', $docKK->verification_status);
        $this->assertFalse($docKK->is_physical_submitted);
        $this->assertNull($docKK->original_file_name);

        // 3.4 Halaman sukses pendaftaran & slip anti-hilang
        $successPage = $this->get("/{$school->school_code}/daftar/selesai?reg={$rayhan->registration_number}");
        $successPage->assertStatus(200);
        $successPage->assertSee($rayhan->registration_number);
        $successPage->assertSee('Simpan Bukti ke WhatsApp');

        // =========================================================================
        // FASE 4: DUAL-ENTRY MODEL: PENDAFTARAN WALK-IN DI LOKET OFFLINE
        // =========================================================================

        $this->actingAs($staff, 'ppdb');

        // Pendaftar lain datang ke meja loket sekolah
        $walkInRes = $this->post("/{$school->school_code}/cms/walk-in", [
            'wave_id' => $wave->id,
            'track_id' => $track->id,
            'full_name' => 'Anisa Rahmawati',
            'nik' => '3201018809100002',
            'birth_date' => '2010-09-18',
            'birth_place' => 'Bogor',
            'gender' => 'P',
            'phone_whatsapp' => '085711223344',
            'is_physical_submitted' => 1,
        ]);
        $walkInRes->assertRedirect("/{$school->school_code}/cms/walk-in");
        $walkInRes->assertSessionHas('registered_applicant');

        $anisa = PpdbApplicant::where('school_id', $school->school_code)
            ->where('full_name', 'Anisa Rahmawati')
            ->firstOrFail();

        $this->assertEquals('offline_walk_in', $anisa->registration_origin);
        $this->assertEquals('verified', $anisa->status);
        $this->assertEquals('complete_physical', $anisa->document_completeness);

        // =========================================================================
        // FASE 5: PANITIA MEMERIKSA BERKAS FISIK & KASUS 1 BERKAS KURANG (INCOMPLETE)
        // =========================================================================

        // 5.1 Panitia membuka daftar pendaftar di CMS dan mencari Rayhan
        $listPage = $this->get("/{$school->school_code}/cms/applicants?search=Rayhan");
        $listPage->assertStatus(200);
        $listPage->assertSee('Muhammad Rayhan Pratama');
        $listPage->assertSee('Online'); // Badge Online

        // 5.2 Panitia membuka halaman detail pendaftar
        $detailPage = $this->get("/{$school->school_code}/cms/applicants/{$rayhan->id}");
        $detailPage->assertStatus(200);
        $detailPage->assertSee('Muhammad Rayhan Pratama');
        $detailPage->assertSee('+ Terima Fisik');

        // 5.3 Rayhan membawa fotokopi KK dan Akta, TETAPI SKL KETINGGALAN/KURANG
        // Panitia menerima fisik KK dan Akta:
        $this->patch("/{$school->school_code}/cms/documents/{$docKK->id}/physical");
        $this->patch("/{$school->school_code}/cms/documents/{$docAkta->id}/physical");

        $docKK->refresh();
        $docAkta->refresh();
        $docSKL->refresh();
        $rayhan->refresh();

        // Verifikasi dokumen 1 & 2 diterima, dokumen 3 belum
        $this->assertTrue($docKK->is_physical_submitted);
        $this->assertTrue($docAkta->is_physical_submitted);
        $this->assertFalse($docSKL->is_physical_submitted);

        // Sistem secara otomatis menjaga status kelengkapan tetap incomplete & status submitted
        $this->assertEquals('incomplete', $rayhan->document_completeness);
        $this->assertEquals('submitted', $rayhan->status);

        // 5.4 Panitia menandai SKL dengan catatan "Minta Perbaikan"
        $note = 'Fotokopi Surat Keterangan Lulus (SKL) belum diserahkan, mohon disusulkan ke loket panitia sekolah.';
        $rejectRes = $this->patch("/{$school->school_code}/cms/documents/{$docSKL->id}/verify", [
            'verification_status' => 'invalid',
            'rejection_reason' => $note,
            'flag_revision' => 1,
        ]);
        $rejectRes->assertRedirect();

        $docSKL->refresh();
        $rayhan->refresh();
        $this->assertEquals('invalid', $docSKL->verification_status);
        $this->assertEquals('need_revision', $rayhan->status);
        $this->assertEquals($note, $rayhan->verification_notes);

        // 5.5 Orang tua mengecek status dari ponsel via link bukti pendaftaran
        $mobileStatus = $this->withSession(['verified_applicant_id' => $rayhan->id])
            ->get("/{$school->school_code}/cek-status/{$rayhan->registration_number}");
        $mobileStatus->assertStatus(200);
        $mobileStatus->assertSee('Perlu Perbaikan');
        $mobileStatus->assertSee('Catatan Perbaikan dari Panitia:');
        $mobileStatus->assertSee($note);
        $mobileStatus->assertSee('Berkas fisik diterima panitia'); // KK & Akta
        $mobileStatus->assertSee('Ditunjukkan fisik langsung ke sekolah'); // SKL

        // =========================================================================
        // FASE 6: SUSULAN BERKAS LENGKAP, STATUS BERKAS LENGKAP, & KELULUSAN
        // =========================================================================

        // 6.1 Keesokan harinya orang tua membawa SKL ke loket panitia
        $this->patch("/{$school->school_code}/cms/documents/{$docSKL->id}/physical");
        $this->patch("/{$school->school_code}/cms/documents/{$docSKL->id}/verify", [
            'verification_status' => 'valid',
        ]);

        $docSKL->refresh();
        $rayhan->refresh();

        // Sistem otomatis mengubah kelengkapan menjadi complete_physical karena seluruh berkas sudah fisik
        $this->assertTrue($docSKL->is_physical_submitted);
        $this->assertEquals('complete_physical', $rayhan->document_completeness);

        // 6.2 Panitia mengubah status siswa menjadi Berkas Lengkap (verified)
        $verifyRes = $this->patch("/{$school->school_code}/cms/applicants/{$rayhan->id}/status", [
            'status' => 'verified',
        ]);
        $verifyRes->assertRedirect();

        $rayhan->refresh();
        $this->assertEquals('verified', $rayhan->status);
        $this->assertNotNull($rayhan->verified_at);
        $this->assertEquals('Budi Santoso (Panitia PPDB)', $rayhan->verified_by);

        // 6.3 Panitia rapat kelulusan dan mengubah status siswa menjadi DITERIMA (accepted)
        $admitRes = $this->patch("/{$school->school_code}/cms/applicants/{$rayhan->id}/status", [
            'status' => 'accepted',
        ]);
        $admitRes->assertRedirect();

        $rayhan->refresh();
        $this->assertEquals('accepted', $rayhan->status);

        // 6.4 Orang tua mengecek status dari ponsel dan melihat tahapan Diterima
        $admitStatusPage = $this->withSession(['verified_applicant_id' => $rayhan->id])
            ->get("/{$school->school_code}/cek-status/{$rayhan->registration_number}");
        $admitStatusPage->assertStatus(200);
        $admitStatusPage->assertSee('Diterima');

        // =========================================================================
        // FASE 7: UNDUH SLIP RESMI PDF & EKSPOR DATA KESELURUHAN KE CSV
        // =========================================================================

        // 7.1 Unduh ulang Bukti Pendaftaran PDF resmi dengan zero-password auth
        $slipRes = $this->get("/{$school->school_code}/bukti-pendaftaran?reg={$rayhan->registration_number}&dob=2010-05-11");
        $slipRes->assertStatus(200);
        $slipRes->assertHeader('content-type', 'application/pdf');

        // 7.2 Ekspor CSV data pendaftar oleh panitia di akhir gelombang
        $exportRes = $this->get("/{$school->school_code}/cms/applicants/export");
        $exportRes->assertStatus(200);
        $exportRes->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csvOutput = $exportRes->streamedContent();
        // Memastikan kedua siswa (Online Rayhan dan Walk-in Anisa) tercantum di laporan
        $this->assertStringContainsString('Muhammad Rayhan Pratama', $csvOutput);
        $this->assertStringContainsString('Anisa Rahmawati', $csvOutput);
        $this->assertStringContainsString($rayhan->registration_number, $csvOutput);
        $this->assertStringContainsString($anisa->registration_number, $csvOutput);

        // 7.3 Superadmin meninggalkan sesi impersonate dengan aman
        $leaveRes = $this->actingAs($superadmin, 'superadmin')
            ->withSession([
                'superadmin_impersonating' => true,
                'impersonated_school' => $school->school_code,
            ])
            ->get('/superadmin/leave-impersonate');

        $leaveRes->assertRedirect('/superadmin');
        $this->assertGuest('ppdb');
    }
}

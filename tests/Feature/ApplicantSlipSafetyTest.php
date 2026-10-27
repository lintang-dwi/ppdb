<?php

namespace Tests\Feature;

use App\Models\PpdbApplicant;
use App\Models\PpdbTrack;
use App\Models\PpdbUser;
use App\Models\PpdbWave;
use App\Models\School;
use App\Services\ApplicantSlipService;
use App\Services\TenantContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApplicantSlipSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected School $schoolA;

    protected School $schoolB;

    protected PpdbApplicant $applicantA;

    protected PpdbUser $staffA;

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
            'name' => 'Jalur Zonasi Terpadu',
            'slug' => 'jalur-zonasi-terpadu',
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
            'custom_fields' => [
                'previous_school_name' => 'SMP Negeri 115 Jakarta',
            ],
            'status' => 'submitted',
        ]);

        $this->staffA = PpdbUser::create([
            'name' => 'Panitia A',
            'email' => 'panitia@sman1jkt.sch.id',
            'password' => Hash::make('secret123'),
        ]);

        $tenantService->clear();
    }

    public function test_guest_can_download_pdf_slip_with_valid_registration_and_birth_date(): void
    {
        $res = $this->get('/sman1jakarta/bukti-pendaftaran?reg=REG-SMAN1JKT-2026-0001&dob=2010-06-15');

        $res->assertStatus(200);
        $res->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('BUKTI_PPDB_REG-SMAN1JKT-2026-0001.pdf', $res->headers->get('content-disposition'));
    }

    public function test_preview_mode_returns_inline_pdf_stream(): void
    {
        $res = $this->get('/sman1jakarta/bukti-pendaftaran?reg=REG-SMAN1JKT-2026-0001&dob=2010-06-15&preview=1');

        $res->assertStatus(200);
        $res->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inline', $res->headers->get('content-disposition'));
    }

    public function test_guest_access_denied_without_birth_date_verification(): void
    {
        // Missing DOB
        $res = $this->get('/sman1jakarta/bukti-pendaftaran?reg=REG-SMAN1JKT-2026-0001');
        $res->assertStatus(403);

        // Mismatched DOB
        $resWrongDob = $this->get('/sman1jakarta/bukti-pendaftaran?reg=REG-SMAN1JKT-2026-0001&dob=2010-01-01');
        $resWrongDob->assertStatus(404);
    }

    public function test_authenticated_staff_can_access_slip_without_birth_date(): void
    {
        $this->actingAs($this->staffA, 'ppdb');

        $res = $this->get('/sman1jakarta/bukti-pendaftaran?reg=REG-SMAN1JKT-2026-0001');
        $res->assertStatus(200);
        $res->assertHeader('content-type', 'application/pdf');
    }

    public function test_cross_tenant_access_to_slip_is_strictly_forbidden(): void
    {
        // Try to access School A's applicant from School B portal
        $res = $this->get('/sman2surabaya/bukti-pendaftaran?reg=REG-SMAN1JKT-2026-0001&dob=2010-06-15');
        $res->assertStatus(404);
    }

    public function test_applicant_slip_service_generates_correct_whatsapp_payload(): void
    {
        $service = app(ApplicantSlipService::class);
        $text = $service->buildWhatsAppText($this->applicantA, $this->schoolA);

        $this->assertStringContainsString('Fauzan Hakim', $text);
        $this->assertStringContainsString('REG-SMAN1JKT-2026-0001', $text);
        $this->assertStringContainsString('SMAN 1 Jakarta', $text);
        $this->assertStringContainsString('Jalur Zonasi Terpadu', $text);
        $this->assertStringContainsString('cek-status', $text);
    }
}

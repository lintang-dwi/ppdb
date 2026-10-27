<?php

namespace Tests\Feature;

use App\Models\PpdbApplicant;
use App\Models\PpdbDocument;
use App\Models\PpdbTrack;
use App\Models\PpdbUser;
use App\Models\PpdbWave;
use App\Models\School;
use App\Services\TenantContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CmsApplicantManagementTest extends TestCase
{
    use RefreshDatabase;

    protected School $schoolA;
    protected School $schoolB;
    protected PpdbUser $staffA;
    protected PpdbApplicant $applicantA;
    protected PpdbDocument $docA;

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
            'name' => 'Panitia SMAN 1',
            'email' => 'panitia@sman1jkt.sch.id',
            'password' => Hash::make('secret123'),
        ]);

        $wave = PpdbWave::create([
            'name' => 'Gelombang 1',
            'academic_year' => '2026/2027',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(5),
            'is_active' => true,
        ]);

        $track = PpdbTrack::create([
            'name' => 'Jalur Afirmasi',
            'slug' => 'jalur-afirmasi',
            'is_active' => true,
        ]);

        $this->applicantA = PpdbApplicant::create([
            'wave_id' => $wave->id,
            'track_id' => $track->id,
            'registration_number' => 'REG-SMAN1JKT-2026-0001',
            'full_name' => 'Ahmad Dahlan',
            'nik' => '3201012345670002',
            'birth_date' => '2010-08-17',
            'phone_whatsapp' => '081234567890',
            'status' => 'submitted',
        ]);

        $this->docA = PpdbDocument::create([
            'applicant_id' => $this->applicantA->id,
            'document_type' => 'kartu_keluarga',
            'original_file_name' => 'kartu_keluarga.pdf',
            'verification_status' => 'pending',
            'is_physical_submitted' => false,
        ]);

        $tenantService->clear();
    }

    public function test_guest_cannot_access_cms_applicant_pages(): void
    {
        $response = $this->get('/sman1jakarta/cms/applicants');
        $response->assertRedirect('/sman1jakarta/cms/login');

        $detailResponse = $this->get('/sman1jakarta/cms/applicants/'.$this->applicantA->id);
        $detailResponse->assertRedirect('/sman1jakarta/cms/login');
    }

    public function test_authenticated_staff_can_view_applicant_list_and_search(): void
    {
        $this->actingAs($this->staffA, 'ppdb');

        $response = $this->get('/sman1jakarta/cms/applicants');
        $response->assertStatus(200);
        $response->assertSee('Ahmad Dahlan');
        $response->assertSee('REG-SMAN1JKT-2026-0001');
        $response->assertSee('Jalur Afirmasi');

        // Search match
        $searchResponse = $this->get('/sman1jakarta/cms/applicants?search=Ahmad');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Ahmad Dahlan');

        // Search no match
        $noMatch = $this->get('/sman1jakarta/cms/applicants?search=BudiNonExistent');
        $noMatch->assertStatus(200);
        $noMatch->assertDontSee('Ahmad Dahlan');
    }

    public function test_staff_can_view_applicant_detail(): void
    {
        $this->actingAs($this->staffA, 'ppdb');

        $response = $this->get('/sman1jakarta/cms/applicants/'.$this->applicantA->id);
        $response->assertStatus(200);
        $response->assertSee('Ahmad Dahlan');
        $response->assertSee('REG-SMAN1JKT-2026-0001');
        $response->assertSee('kartu_keluarga');
        $response->assertSee('Unduh Bukti (PDF)');
    }

    public function test_staff_can_toggle_applicant_status_freely(): void
    {
        $this->actingAs($this->staffA, 'ppdb');

        // Flip to verified
        $res1 = $this->patch('/sman1jakarta/cms/applicants/'.$this->applicantA->id.'/status', [
            'status' => 'verified',
        ]);
        $res1->assertRedirect();
        $this->applicantA->refresh();
        $this->assertEquals('verified', $this->applicantA->status);
        $this->assertNotNull($this->applicantA->verified_at);

        // Flip to need_revision with notes (undo / change state)
        $res2 = $this->patch('/sman1jakarta/cms/applicants/'.$this->applicantA->id.'/status', [
            'status' => 'need_revision',
            'verification_notes' => 'Tolong scan ulang KK.',
        ]);
        $res2->assertRedirect();
        $this->applicantA->refresh();
        $this->assertEquals('need_revision', $this->applicantA->status);
        $this->assertEquals('Tolong scan ulang KK.', $this->applicantA->verification_notes);

        // Flip to accepted
        $res3 = $this->patch('/sman1jakarta/cms/applicants/'.$this->applicantA->id.'/status', [
            'status' => 'accepted',
        ]);
        $res3->assertRedirect();
        $this->applicantA->refresh();
        $this->assertEquals('accepted', $this->applicantA->status);
    }

    public function test_staff_can_verify_and_reject_document(): void
    {
        $this->actingAs($this->staffA, 'ppdb');

        // Reject document and flag revision
        $resReject = $this->patch('/sman1jakarta/cms/documents/'.$this->docA->id.'/verify', [
            'verification_status' => 'invalid',
            'rejection_reason' => 'Scan tidak jelas',
            'flag_revision' => 1,
        ]);

        $resReject->assertRedirect();
        $this->docA->refresh();
        $this->assertEquals('invalid', $this->docA->verification_status);
        $this->assertEquals('Scan tidak jelas', $this->docA->rejection_reason);

        $this->applicantA->refresh();
        $this->assertEquals('need_revision', $this->applicantA->status);

        // Approve document
        $resApprove = $this->patch('/sman1jakarta/cms/documents/'.$this->docA->id.'/verify', [
            'verification_status' => 'valid',
        ]);
        $resApprove->assertRedirect();
        $this->docA->refresh();
        $this->assertEquals('valid', $this->docA->verification_status);
        $this->assertNull($this->docA->rejection_reason);
    }

    public function test_staff_can_toggle_physical_document_submission(): void
    {
        $this->actingAs($this->staffA, 'ppdb');

        $res = $this->patch('/sman1jakarta/cms/documents/'.$this->docA->id.'/physical');
        $res->assertRedirect();

        $this->docA->refresh();
        $this->assertTrue($this->docA->is_physical_submitted);

        $this->applicantA->refresh();
        $this->assertEquals('complete_physical', $this->applicantA->document_completeness);
    }

    public function test_tenant_isolation_on_cms_applicants(): void
    {
        $this->actingAs($this->staffA, 'ppdb');

        // Accessing applicant from another school path returns 404
        $res = $this->get('/sman2surabaya/cms/applicants/'.$this->applicantA->id);
        $res->assertStatus(404);
    }

    public function test_staff_can_export_applicants_to_csv(): void
    {
        $this->actingAs($this->staffA, 'ppdb');

        $response = $this->get('/sman1jakarta/cms/applicants/export');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('PPDB_Pendaftar_sman1jakarta_', $response->headers->get('content-disposition'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Ahmad Dahlan', $content);
        $this->assertStringContainsString('REG-SMAN1JKT-2026-0001', $content);
        $this->assertStringContainsString('Jalur Afirmasi', $content);
    }
}

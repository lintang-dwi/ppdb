<?php

namespace Tests\Feature;

use App\Models\PpdbApplicant;
use App\Models\PpdbUser;
use App\Models\PpdbWave;
use App\Models\School;
use App\Services\TenantContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CmsAuthAndLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected School $schoolA;

    protected School $schoolB;

    protected PpdbUser $userA;

    protected PpdbUser $userB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create two separate schools
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

        // 2. Create staff users for each school
        $tenantService = app(TenantContextService::class);

        $tenantService->setTenant($this->schoolA);
        $this->userA = PpdbUser::create([
            'name' => 'Budi Santoso',
            'email' => 'panitia@sman1jkt.sch.id',
            'password' => Hash::make('secret123'),
        ]);

        $tenantService->setTenant($this->schoolB);
        $this->userB = PpdbUser::create([
            'name' => 'Dewi Lestari',
            'email' => 'panitia@sman2sby.sch.id',
            'password' => Hash::make('secret123'),
        ]);

        $tenantService->clear();
    }

    public function test_guest_is_redirected_to_tenant_login_when_accessing_dashboard(): void
    {
        // Path prefix mode
        $response = $this->get('/sman1jakarta/cms/dashboard');
        $response->assertRedirect('/sman1jakarta/cms/login');

        // Custom domain mode
        $responseCustom = $this->get('http://ppdb.sman1jkt.sch.id/cms/dashboard');
        $responseCustom->assertRedirect('http://ppdb.sman1jkt.sch.id/cms/login');
    }

    public function test_login_page_renders_school_branding(): void
    {
        $response = $this->get('/sman1jakarta/cms/login');

        $response->assertStatus(200);
        $response->assertSee('SMAN 1 Jakarta');
        $response->assertSee('Kode: sman1jakarta');
    }

    public function test_staff_user_can_authenticate_on_their_own_school(): void
    {
        $response = $this->post('/sman1jakarta/cms/login', [
            'email' => 'panitia@sman1jkt.sch.id',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/sman1jakarta/cms/dashboard');
        $this->assertTrue(Auth::guard('ppdb')->check());
        $this->assertEquals($this->userA->id, Auth::guard('ppdb')->id());

        // Check dashboard view
        $dashResponse = $this->get('/sman1jakarta/cms/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Selamat Datang, Budi Santoso');
        $dashResponse->assertSee('SMAN 1 Jakarta');
    }

    public function test_cross_tenant_login_is_strictly_rejected(): void
    {
        // User from School A attempts to login on School B portal with correct password
        $response = $this->post('/sman2surabaya/cms/login', [
            'email' => 'panitia@sman1jkt.sch.id',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertFalse(Auth::guard('ppdb')->check());
    }

    public function test_dashboard_displays_correct_scoped_applicant_counts(): void
    {
        $tenantService = app(TenantContextService::class);

        // Add 2 applicants in School A
        $tenantService->setTenant($this->schoolA);
        $waveA = PpdbWave::factory()->create();
        PpdbApplicant::factory()->count(2)->create([
            'wave_id' => $waveA->id,
            'status' => 'submitted',
        ]);

        // Add 5 applicants in School B
        $tenantService->setTenant($this->schoolB);
        $waveB = PpdbWave::factory()->create();
        PpdbApplicant::factory()->count(5)->create([
            'wave_id' => $waveB->id,
            'status' => 'submitted',
        ]);

        // Login as School A staff and inspect dashboard
        $this->actingAs($this->userA, 'ppdb');
        $response = $this->get('/sman1jakarta/cms/dashboard');

        $response->assertStatus(200);
        $response->assertSee('SMAN 1 Jakarta');
        // Total count for school A must be 2, not 7
        $this->assertEquals(2, $response->viewData('counts')['total']);
    }

    public function test_staff_can_logout_successfully(): void
    {
        $this->actingAs($this->userA, 'ppdb');
        $this->assertTrue(Auth::guard('ppdb')->check());

        $response = $this->post('/sman1jakarta/cms/logout');

        $response->assertRedirect('/sman1jakarta/cms/login');
        $this->assertFalse(Auth::guard('ppdb')->check());
    }
}

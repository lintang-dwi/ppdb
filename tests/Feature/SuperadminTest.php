<?php

namespace Tests\Feature;

use App\Models\PpdbSuperadmin;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuperadminTest extends TestCase
{
    use RefreshDatabase;

    protected PpdbSuperadmin $superadmin;
    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = PpdbSuperadmin::create([
            'name' => 'Superadmin Test',
            'email' => 'superadmin@test.cloud',
            'password' => Hash::make('password123'),
        ]);

        $this->school = School::create([
            'school_code' => 'sman1jakarta',
            'uuid' => (string) Str::uuid(),
            'name' => 'SMAN 1 Jakarta',
            'subdomain' => 'sman1jkt',
            'status' => 'active',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_superadmin_dashboard(): void
    {
        $response = $this->get('/superadmin');
        $response->assertRedirect('/superadmin/login');
    }

    public function test_superadmin_can_view_login_page(): void
    {
        $response = $this->get('/superadmin/login');
        $response->assertStatus(200);
        $response->assertSee('Login Superadmin');
    }

    public function test_superadmin_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/superadmin/login', [
            'email' => 'superadmin@test.cloud',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/superadmin');
        $this->assertAuthenticatedAs($this->superadmin, 'superadmin');
    }

    public function test_superadmin_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->post('/superadmin/login', [
            'email' => 'superadmin@test.cloud',
            'password' => 'wrong-pass',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('superadmin');
    }

    public function test_superadmin_can_view_dashboard_with_metrics_and_schools(): void
    {
        $response = $this->actingAs($this->superadmin, 'superadmin')->get('/superadmin');

        $response->assertStatus(200);
        $response->assertSee('Monitoring Platform PPDB Multi-Tenant');
        $response->assertSee('SMAN 1 Jakarta');
        $response->assertSee('Login Sebagai Panitia');
    }

    public function test_superadmin_can_impersonate_school_panitia(): void
    {
        $response = $this->actingAs($this->superadmin, 'superadmin')
            ->post("/superadmin/impersonate/{$this->school->school_code}");

        $response->assertRedirect("/{$this->school->school_code}/cms/dashboard");
        $this->assertAuthenticated('ppdb');
        $response->assertSessionHas('superadmin_impersonating', true);
    }

    public function test_superadmin_can_leave_impersonate(): void
    {
        $response = $this->actingAs($this->superadmin, 'superadmin')
            ->withSession([
                'superadmin_impersonating' => true,
                'impersonated_school' => $this->school->school_code,
            ])
            ->get('/superadmin/leave-impersonate');

        $response->assertRedirect('/superadmin');
        $response->assertSessionMissing('superadmin_impersonating');
        $this->assertGuest('ppdb');
    }

    public function test_superadmin_can_logout(): void
    {
        $response = $this->actingAs($this->superadmin, 'superadmin')
            ->post('/superadmin/logout');

        $response->assertRedirect('/superadmin/login');
        $this->assertGuest('superadmin');
    }
}

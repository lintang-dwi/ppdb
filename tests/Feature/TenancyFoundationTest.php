<?php

namespace Tests\Feature;

use App\Models\School;
use App\Services\TenantContextService;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DummyTenantItem extends Model
{
    use BelongsToTenant;

    protected $table = 'dummy_tenant_items';

    protected $guarded = [];
}

class TenancyFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('dummy_tenant_items', function (Blueprint $table) {
            $table->id();
            $table->string('school_id', 50)->index();
            $table->string('title');
            $table->timestamps();
        });

        School::insert([
            [
                'school_code' => 'sman1jakarta',
                'name' => 'SMAN 1 Jakarta',
                'subdomain' => 'sman1jkt',
                'custom_domain' => 'ppdb.sman1jkt.sch.id',
                'custom_domain_verified' => 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_code' => 'sman2surabaya',
                'name' => 'SMAN 2 Surabaya',
                'subdomain' => 'sman2sby',
                'custom_domain' => 'ppdb.sman2sby.sch.id',
                'custom_domain_verified' => 0, // Unverified
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function test_central_platform_root_returns_operational_status(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertJson([
            'engine' => 'PPDB Multi-Tenant Monolith',
            'status' => 'operational',
        ]);
    }

    public function test_path_prefix_resolves_valid_school_and_renders_tenant_page(): void
    {
        $response = $this->get('/sman1jakarta');

        $response->assertStatus(200);
        $response->assertSee('SMAN 1 Jakarta');
        $response->assertSee('Portal PPDB Online');
        $this->assertFalse(tenant_context()->isCustomDomain());
    }

    public function test_path_prefix_aborts_404_for_unknown_school(): void
    {
        $response = $this->get('/nonexistent_school');

        $response->assertStatus(404);
    }

    public function test_custom_domain_resolves_verified_cname(): void
    {
        $response = $this->get('http://ppdb.sman1jkt.sch.id/');

        $response->assertStatus(200);
        $response->assertSee('SMAN 1 Jakarta');
        $this->assertTrue(tenant_context()->isCustomDomain());
    }

    public function test_custom_domain_aborts_404_if_unverified(): void
    {
        $response = $this->get('http://ppdb.sman2sby.sch.id/');

        $response->assertStatus(404);
    }

    public function test_tenant_scope_and_automatic_school_id_assignment(): void
    {
        $tenantService = app(TenantContextService::class);
        $school1 = School::find('sman1jakarta');
        $school2 = School::find('sman2surabaya');

        // 1. In context of School 1
        $tenantService->setTenant($school1);

        $item1 = DummyTenantItem::create(['title' => 'Pengumuman SMAN 1']);
        $this->assertEquals('sman1jakarta', $item1->school_id);

        // 2. In context of School 2
        $tenantService->setTenant($school2);

        $item2 = DummyTenantItem::create(['title' => 'Pengumuman SMAN 2']);
        $this->assertEquals('sman2surabaya', $item2->school_id);

        // 3. School 2 cannot see School 1 rows
        $itemsSchool2 = DummyTenantItem::all();
        $this->assertCount(1, $itemsSchool2);
        $this->assertEquals('Pengumuman SMAN 2', $itemsSchool2->first()->title);

        // 4. Switch back to School 1
        $tenantService->setTenant($school1);
        $itemsSchool1 = DummyTenantItem::all();
        $this->assertCount(1, $itemsSchool1);
        $this->assertEquals('Pengumuman SMAN 1', $itemsSchool1->first()->title);

        // 5. Unscoped query allows seeing all rows (only for tenant resolution/collision check)
        $allItems = DummyTenantItem::withoutGlobalScopes()->get();
        $this->assertCount(2, $allItems);
    }
}

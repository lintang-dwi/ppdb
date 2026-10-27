<?php

namespace App\Http\Middleware;

use App\Services\TenantContextService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffBelongsToTenant
{
    public function __construct(
        protected TenantContextService $tenantContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('ppdb')->user();
        $currentSchool = $this->tenantContext->getSchoolCode();

        if ($user && $currentSchool && $user->school_id !== $currentSchool) {
            auth('ppdb')->logout();

            return redirect()->to(tenant_route('cms.login'))
                ->with('error', 'Sesi Anda tidak valid untuk sekolah ini.');
        }

        return $next($request);
    }
}

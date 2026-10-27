<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Services\TenantContextService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenantPpdb
{
    public function __construct(
        protected TenantContextService $tenantContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $centralDomain = config('app.central_domain', env('PPDB_CENTRAL_DOMAIN', 'ppdb.bitserver.cloud'));

        // 1. Resolve via Custom Domain (CNAME e.g. ppdb.sekolah.sch.id)
        if ($host !== $centralDomain && $host !== 'localhost' && $host !== '127.0.0.1') {
            $school = School::where('custom_domain', $host)
                ->where('custom_domain_verified', true)
                ->first();

            if (! $school && str_ends_with($host, '.'.$centralDomain)) {
                $subdomain = str_replace('.'.$centralDomain, '', $host);
                $school = School::where('subdomain', $subdomain)->first();
            }

            if ($school) {
                $this->tenantContext->setTenant($school, true);

                return $next($request);
            }

            abort(Response::HTTP_NOT_FOUND, 'Portal PPDB custom domain tidak ditemukan atau belum diverifikasi.');
        }

        // 2. Resolve via Path Prefix: /{school_code}
        $schoolCode = $request->route('school_code');
        if ($schoolCode) {
            $school = School::where('school_code', $schoolCode)->first();

            if ($school) {
                $this->tenantContext->setTenant($school, false);
                url()->defaults(['school_code' => $schoolCode]);
                $request->route()->forgetParameter('school_code');

                return $next($request);
            }

            abort(Response::HTTP_NOT_FOUND, "Sekolah dengan kode '{$schoolCode}' tidak ditemukan.");
        }

        // 3. Fallback when context is already set in memory
        if ($this->tenantContext->hasTenant()) {
            return $next($request);
        }

        // 4. Allow central platform root to render platform status
        if ($request->path() === '/') {
            return $next($request);
        }

        abort(Response::HTTP_NOT_FOUND, 'Portal PPDB tidak ditemukan.');
    }
}

<?php

use App\Services\TenantContextService;
use Illuminate\Support\Facades\Route;

if (! function_exists('tenant_context')) {
    function tenant_context(): TenantContextService
    {
        return app(TenantContextService::class);
    }
}

if (! function_exists('tenant_route')) {
    /**
     * Generate a tenant-aware route URL supporting both CNAME and path prefix modes.
     */
    function tenant_route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        if (! is_array($parameters)) {
            $parameters = [$parameters];
        }

        foreach ($parameters as $k => $v) {
            if ($v instanceof \DateTimeInterface) {
                $parameters[$k] = $v->format('Y-m-d');
            }
        }

        $tenantService = tenant_context();
        $schoolCode = $parameters['school_code']
            ?? $tenantService->getSchoolCode()
            ?? request()?->route('school_code');

        // 1. Custom domain mode (e.g. ppdb.sekolah.sch.id)
        if ($tenantService->isCustomDomain()) {
            unset($parameters['school_code']);

            $customName = 'tenant.custom.'.$name;
            if (Route::has($customName)) {
                return route($customName, $parameters, $absolute);
            }

            return route($name, $parameters, $absolute);
        }

        // 2. Sub-path prefix mode (e.g. ppdb.bitserver.cloud/{school_code})
        if (! isset($parameters['school_code']) && $schoolCode) {
            $parameters['school_code'] = $schoolCode;
        }

        $prefixName = 'tenant.prefix.'.$name;
        if (Route::has($prefixName)) {
            return route($prefixName, $parameters, $absolute);
        }

        return route($name, $parameters, $absolute);
    }
}

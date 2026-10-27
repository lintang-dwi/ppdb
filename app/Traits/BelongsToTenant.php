<?php

namespace App\Traits;

use App\Models\School;
use App\Scopes\TenantScope;
use App\Services\TenantContextService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            $tenantService = app(TenantContextService::class);
            $schoolCode = $tenantService->getSchoolCode();

            if (! $schoolCode && ! app()->runningUnitTests()) {
                throw new RuntimeException('Cannot create tenant model without an active tenant context.');
            }

            if ($schoolCode) {
                // Always enforce tenant school_id, preventing request injection
                $model->school_id = $schoolCode;
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id', 'school_code');
    }
}

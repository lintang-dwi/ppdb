<?php

namespace App\Scopes;

use App\Services\TenantContextService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantService = app(TenantContextService::class);
        $schoolCode = $tenantService->getSchoolCode();

        if ($schoolCode !== null) {
            $builder->where($model->qualifyColumn('school_id'), '=', $schoolCode);
        }
    }
}

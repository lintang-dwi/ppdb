<?php

namespace App\Services;

use App\Models\School;

class TenantContextService
{
    protected ?School $school = null;

    protected bool $isCustomDomain = false;

    public function setTenant(School $school, bool $isCustomDomain = false): void
    {
        $this->school = $school;
        $this->isCustomDomain = $isCustomDomain;
    }

    public function getTenant(): ?School
    {
        return $this->school;
    }

    public function hasTenant(): bool
    {
        return $this->school !== null;
    }

    public function getSchoolCode(): ?string
    {
        return $this->school?->school_code;
    }

    public function getName(): ?string
    {
        return $this->school?->name;
    }

    public function getSchoolName(): ?string
    {
        return $this->getName();
    }

    public function getLogo(): ?string
    {
        return $this->school?->logo;
    }

    public function isCustomDomain(): bool
    {
        return $this->isCustomDomain;
    }

    public function clear(): void
    {
        $this->school = null;
        $this->isCustomDomain = false;
    }
}

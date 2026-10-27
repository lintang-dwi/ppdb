<?php

namespace App\Services;

use App\Models\PpdbApplicant;
use Illuminate\Support\Facades\DB;

class RegistrationNumberService
{
    /**
     * Generate the next unique registration number for the school and year.
     * Guaranteed safe against concurrency with lockForUpdate().
     * Format: REG-{SCHOOL_CODE}-{YEAR}-{INDEX} (e.g. REG-SMAN1JKT-2026-0001)
     */
    public function generate(string $schoolCode, ?string $year = null): string
    {
        $year = $year ?: date('Y');
        $code = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $schoolCode));
        $prefix = "REG-{$code}-{$year}-";

        return DB::transaction(function () use ($schoolCode, $prefix) {
            $lastApplicant = PpdbApplicant::withoutGlobalScopes()
                ->where('school_id', $schoolCode)
                ->where('registration_number', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $nextIndex = 1;

            if ($lastApplicant) {
                $lastNumber = $lastApplicant->registration_number;
                $suffix = substr($lastNumber, strlen($prefix));
                if (is_numeric($suffix)) {
                    $nextIndex = ((int) $suffix) + 1;
                }
            }

            $number = $prefix.sprintf('%04d', $nextIndex);

            // Double check safety
            while (PpdbApplicant::withoutGlobalScopes()->where('registration_number', $number)->exists()) {
                $nextIndex++;
                $number = $prefix.sprintf('%04d', $nextIndex);
            }

            return $number;
        });
    }
}

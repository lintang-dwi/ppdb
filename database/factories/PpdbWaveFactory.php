<?php

namespace Database\Factories;

use App\Models\PpdbWave;
use Illuminate\Database\Eloquent\Factories\Factory;

class PpdbWaveFactory extends Factory
{
    protected $model = PpdbWave::class;

    public function definition(): array
    {
        return [
            'name' => 'Gelombang 1 - Jalur Reguler',
            'academic_year' => '2026/2027',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(20),
            'announcement_date' => now()->addDays(25),
            'is_active' => true,
        ];
    }
}

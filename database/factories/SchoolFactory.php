<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        $code = 'sch_'.fake()->unique()->slug(1);

        return [
            'school_code' => $code,
            'uuid' => (string) Str::uuid(),
            'name' => 'SMP Negeri '.fake()->numberBetween(1, 99).' '.fake()->city(),
            'subdomain' => $code,
            'custom_domain' => null,
            'custom_domain_verified' => false,
            'theme' => 'default',
            'status' => 'active',
            'logo' => null,
        ];
    }
}

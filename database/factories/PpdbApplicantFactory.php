<?php

namespace Database\Factories;

use App\Models\PpdbApplicant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PpdbApplicantFactory extends Factory
{
    protected $model = PpdbApplicant::class;

    public function definition(): array
    {
        return [
            'registration_number' => 'REG-'.fake()->unique()->numerify('#####'),
            'registration_origin' => 'online',
            'full_name' => fake()->name(),
            'nik' => fake()->numerify('3171############'),
            'birth_date' => fake()->date('Y-m-d', '-12 years'),
            'birth_place' => fake()->city(),
            'gender' => fake()->randomElement(['L', 'P']),
            'phone_whatsapp' => '08'.fake()->numerify('##########'),
            'custom_fields' => [],
            'document_completeness' => 'incomplete',
            'status' => 'submitted',
            'verification_notes' => null,
            'verified_at' => null,
            'verified_by' => null,
        ];
    }
}

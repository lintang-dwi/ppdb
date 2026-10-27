<?php

namespace Database\Factories;

use App\Models\PpdbFormField;
use Illuminate\Database\Eloquent\Factories\Factory;

class PpdbFormFieldFactory extends Factory
{
    protected $model = PpdbFormField::class;

    public function definition(): array
    {
        return [
            'wave_id' => null,
            'step' => 'biodata',
            'field_key' => fake()->unique()->word(),
            'field_label' => fake()->words(2, true),
            'field_type' => 'text',
            'options' => null,
            'is_required' => false,
            'is_active' => true,
            'order_index' => 0,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\PpdbTrack;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PpdbTrackFactory extends Factory
{
    protected $model = PpdbTrack::class;

    public function definition(): array
    {
        $name = fake()->randomElement(['Reguler', 'Prestasi', 'Zonasi', 'Afirmasi', 'Tahfidz']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'quota' => fake()->numberBetween(30, 150),
            'description' => 'Jalur penerimaan '.$name.' dengan ketentuan berkas lengkap.',
            'is_active' => true,
            'order_index' => 0,
        ];
    }
}

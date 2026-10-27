<?php

namespace Database\Factories;

use App\Models\PpdbDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

class PpdbDocumentFactory extends Factory
{
    protected $model = PpdbDocument::class;

    public function definition(): array
    {
        return [
            'document_type' => fake()->randomElement(['kk', 'akta_kelahiran', 'ijazah', 'pas_foto']),
            'original_file_name' => fake()->word().'.jpg',
            'drive_file_id' => 'drive_'.fake()->uuid(),
            'drive_view_url' => 'https://drive.google.com/file/d/'.fake()->uuid().'/view',
            'is_physical_submitted' => false,
            'verification_status' => 'pending',
            'rejection_reason' => null,
        ];
    }
}

<?php

namespace App\Services;

use App\Models\PpdbFormField;
use App\Models\PpdbTrack;
use App\Models\PpdbWave;
use App\Models\School;

class FormTestAutomationService
{
    /**
     * Generate complete fake payload for all form fields of a school/wave.
     * Documents are intentionally empty because testing uses notifications instead of uploads.
     *
     * @return array<string, mixed>
     */
    public function generatePayload(School $school, ?PpdbWave $wave = null, ?PpdbTrack $track = null, array $overrides = []): array
    {
        $wave = $wave ?? PpdbWave::where('school_id', $school->school_code)->where('is_active', true)->first();
        $track = $track ?? PpdbTrack::where('school_id', $school->school_code)->where('is_active', true)->first();

        $randomSuffix = rand(1000, 9999);
        $payload = [
            'wave_id' => $wave?->id,
            'track_id' => $track?->id,
            'full_name' => 'Calon Siswa Testing '.$randomSuffix,
            'nik' => '320101'.str_pad((string) rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT),
            'birth_place' => 'Jakarta',
            'birth_date' => '2010-05-15',
            'gender' => rand(0, 1) ? 'L' : 'P',
            'phone_whatsapp' => '0812'.rand(10000000, 99999999),
            'custom_fields' => [],
            'documents' => [],
        ];

        $fields = PpdbFormField::withoutGlobalScopes()
            ->where('school_id', $school->school_code)
            ->where('is_active', true)
            ->when($wave, fn ($q) => $q->where(fn ($sub) => $sub->where('wave_id', $wave->id)->orWhereNull('wave_id')))
            ->orderBy('order_index')
            ->get();

        foreach ($fields as $field) {
            if ($field->field_type === 'file') {
                // Testing automation invariant: berkas disimulasikan dari notifikasi tanpa upload
                continue;
            }

            $payload['custom_fields'][$field->field_key] = $this->generateFieldValue($field);
        }

        return array_replace_recursive($payload, $overrides);
    }

    /**
     * Generate suitable value based on field type and options.
     */
    protected function generateFieldValue(PpdbFormField $field): mixed
    {
        if (in_array($field->field_type, ['select', 'radio'])) {
            return is_array($field->options) && count($field->options) > 0 ? $field->options[0] : 'Pilihan Default';
        }

        if ($field->field_type === 'checkbox') {
            return is_array($field->options) && count($field->options) > 0 ? [$field->options[0]] : ['Opsi Default'];
        }

        return match ($field->field_type) {
            'number' => 85,
            'date' => '2024-05-20',
            'textarea' => 'Pengisian otomatis untuk pengujian form PPDB.',
            default => $this->generateTextFieldValue($field->field_key),
        };
    }

    /**
     * Provide realistic default text for common preset keys.
     */
    protected function generateTextFieldValue(string $key): string
    {
        return match ($key) {
            'nisn' => '00'.rand(10000000, 99999999),
            'previous_school_npsn' => (string) rand(10000000, 99999999),
            'previous_school_name' => 'SMP Negeri 1 Simulasi',
            'nickname' => 'Budi',
            'kk_number' => '320101'.rand(1000000000, 9999999999),
            'father_job' => 'Karyawan Swasta',
            'mother_job' => 'Ibu Rumah Tangga',
            default => 'Data Testing Otomatis',
        };
    }

    /**
     * Descriptive message for test document bypass.
     */
    public function getDocumentTestNotice(): string
    {
        return 'Berkas fisik/digital diverifikasi lewat notifikasi sistem tanpa perlu unggah berkas.';
    }
}

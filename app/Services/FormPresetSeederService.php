<?php

namespace App\Services;

use App\Models\PpdbFormField;
use InvalidArgumentException;

class FormPresetSeederService
{
    public const TIER_PAUD = 'paud_tk';
    public const TIER_SD = 'sd_mi';
    public const TIER_SMP = 'smp_mts';
    public const TIER_SMA = 'sma_smk';
    public const TIER_SMK = 'smk';
    public const TIER_PESANTREN = 'pesantren';

    public function seedPreset(string $schoolCode, string $tier, ?int $waveId = null, bool $replaceExisting = false): int
    {
        $blueprints = $this->getBlueprint($tier);

        if ($replaceExisting) {
            $query = PpdbFormField::withoutGlobalScopes()
                ->where('school_id', $schoolCode)
                ->whereIn('field_key', $this->getAllPresetKeys());

            if ($waveId !== null) {
                $query->where(fn ($q) => $q->where('wave_id', $waveId)->orWhereNull('wave_id'));
            }
            $query->delete();
        }

        $count = 0;
        foreach ($blueprints as $index => $field) {
            PpdbFormField::withoutGlobalScopes()->updateOrCreate(
                ['school_id' => $schoolCode, 'wave_id' => $waveId, 'field_key' => $field['field_key']],
                [
                    'step' => $field['step'],
                    'field_label' => $field['field_label'],
                    'field_type' => $field['field_type'],
                    'options' => $field['options'] ?? null,
                    'is_required' => $field['is_required'] ?? false,
                    'is_active' => true,
                    'order_index' => $index,
                ]
            );
            $count++;
        }

        return $count;
    }

    public function getAllPresetKeys(): array
    {
        return [
            'nickname', 'sibling_order', 'body_weight', 'body_height', 'medical_notes', 'uniform_size',
            'kk_number', 'distance_to_school', 'transport_mode', 'father_job', 'mother_job',
            'nisn', 'previous_school_npsn', 'previous_school_name', 'average_score',
            'major_choice_1', 'major_choice_2', 'is_colorblind',
            'quran_reading_ability', 'tahfidz_juz', 'residence_type',
        ];
    }

    public function detectCurrentTier(string $schoolCode, ?int $waveId = null): ?string
    {
        $query = PpdbFormField::withoutGlobalScopes()->where('school_id', $schoolCode);
        if ($waveId) {
            $query->where(fn ($q) => $q->where('wave_id', $waveId)->orWhereNull('wave_id'));
        }
        $keys = $query->pluck('field_key')->all();

        if (in_array('quran_reading_ability', $keys) || in_array('tahfidz_juz', $keys)) return self::TIER_PESANTREN;
        if (in_array('is_colorblind', $keys)) return self::TIER_SMK;
        if (in_array('major_choice_1', $keys) || in_array('major_choice_2', $keys)) return self::TIER_SMA;
        if (in_array('average_score', $keys) || in_array('previous_school_npsn', $keys)) return self::TIER_SMP;
        if (in_array('kk_number', $keys) || in_array('distance_to_school', $keys)) return self::TIER_SD;
        if (in_array('nickname', $keys) || in_array('sibling_order', $keys)) return self::TIER_PAUD;

        return null;
    }

    public function getTierLabel(?string $tier): string
    {
        return match ($tier) {
            self::TIER_PAUD => 'PAUD / TK',
            self::TIER_SD => 'SD / MI',
            self::TIER_SMP => 'SMP / MTs',
            self::TIER_SMA => 'SMA / MA',
            self::TIER_SMK => 'SMK (Kejuruan)',
            self::TIER_PESANTREN => 'Pesantren / Islamic',
            default => 'Kustom',
        };
    }

    public function getBlueprint(string $tier): array
    {
        return match ($tier) {
            self::TIER_PAUD => [
                ['step' => 'biodata', 'field_key' => 'nickname', 'field_label' => 'Nama Panggilan', 'field_type' => 'text', 'is_required' => true],
                ['step' => 'biodata', 'field_key' => 'sibling_order', 'field_label' => 'Anak Ke-', 'field_type' => 'number', 'is_required' => false],
                ['step' => 'biodata', 'field_key' => 'body_weight', 'field_label' => 'Berat Badan (kg)', 'field_type' => 'number', 'is_required' => false],
                ['step' => 'biodata', 'field_key' => 'body_height', 'field_label' => 'Tinggi Badan (cm)', 'field_type' => 'number', 'is_required' => false],
                ['step' => 'biodata', 'field_key' => 'medical_notes', 'field_label' => 'Catatan Kesehatan Khusus', 'field_type' => 'textarea', 'is_required' => false],
                ['step' => 'tambahan', 'field_key' => 'uniform_size', 'field_label' => 'Ukuran Seragam', 'field_type' => 'select', 'options' => ['S', 'M', 'L', 'XL'], 'is_required' => true],
            ],
            self::TIER_SD => [
                ['step' => 'biodata', 'field_key' => 'kk_number', 'field_label' => 'Nomor Kartu Keluarga (KK)', 'field_type' => 'text', 'is_required' => true],
                ['step' => 'biodata', 'field_key' => 'distance_to_school', 'field_label' => 'Jarak ke Sekolah (km)', 'field_type' => 'number', 'is_required' => false],
                ['step' => 'biodata', 'field_key' => 'transport_mode', 'field_label' => 'Moda Transportasi', 'field_type' => 'select', 'options' => ['Jalan Kaki', 'Sepeda', 'Sepeda Motor', 'Angkutan Umum', 'Mobil Pribadi'], 'is_required' => false],
                ['step' => 'orang_tua', 'field_key' => 'father_job', 'field_label' => 'Pekerjaan Ayah', 'field_type' => 'text', 'is_required' => false],
                ['step' => 'orang_tua', 'field_key' => 'mother_job', 'field_label' => 'Pekerjaan Ibu', 'field_type' => 'text', 'is_required' => false],
            ],
            self::TIER_SMP => [
                ['step' => 'biodata', 'field_key' => 'nisn', 'field_label' => 'NISN', 'field_type' => 'text', 'is_required' => true],
                ['step' => 'asal_sekolah', 'field_key' => 'previous_school_npsn', 'field_label' => 'NPSN Sekolah Asal', 'field_type' => 'text', 'is_required' => false],
                ['step' => 'asal_sekolah', 'field_key' => 'previous_school_name', 'field_label' => 'Nama Sekolah Asal', 'field_type' => 'text', 'is_required' => true],
                ['step' => 'tambahan', 'field_key' => 'average_score', 'field_label' => 'Rata-rata Nilai Rapor', 'field_type' => 'number', 'is_required' => false],
                ['step' => 'tambahan', 'field_key' => 'uniform_size', 'field_label' => 'Ukuran Seragam', 'field_type' => 'select', 'options' => ['S', 'M', 'L', 'XL', 'XXL'], 'is_required' => true],
            ],
            self::TIER_SMA => [
                ['step' => 'biodata', 'field_key' => 'nisn', 'field_label' => 'NISN', 'field_type' => 'text', 'is_required' => true],
                ['step' => 'asal_sekolah', 'field_key' => 'previous_school_name', 'field_label' => 'Nama Sekolah Asal', 'field_type' => 'text', 'is_required' => true],
                ['step' => 'tambahan', 'field_key' => 'major_choice_1', 'field_label' => 'Pilihan Peminatan 1', 'field_type' => 'select', 'options' => ['MIPA (Matematika & IPA)', 'IPS (Ilmu Pengetahuan Sosial)', 'Bahasa & Budaya'], 'is_required' => true],
                ['step' => 'tambahan', 'field_key' => 'major_choice_2', 'field_label' => 'Pilihan Peminatan 2', 'field_type' => 'select', 'options' => ['MIPA (Matematika & IPA)', 'IPS (Ilmu Pengetahuan Sosial)', 'Bahasa & Budaya'], 'is_required' => false],
            ],
            self::TIER_SMK => [
                ['step' => 'biodata', 'field_key' => 'nisn', 'field_label' => 'NISN', 'field_type' => 'text', 'is_required' => true],
                ['step' => 'asal_sekolah', 'field_key' => 'previous_school_name', 'field_label' => 'Nama Sekolah Asal', 'field_type' => 'text', 'is_required' => true],
                ['step' => 'tambahan', 'field_key' => 'major_choice_1', 'field_label' => 'Pilihan Program Keahlian 1', 'field_type' => 'select', 'options' => ['Teknik Komputer & Jaringan', 'Rekayasa Perangkat Lunak', 'Akuntansi & Keuangan', 'Teknik Kendaraan Ringan', 'Desain Komunikasi Visual'], 'is_required' => true],
                ['step' => 'tambahan', 'field_key' => 'major_choice_2', 'field_label' => 'Pilihan Program Keahlian 2', 'field_type' => 'select', 'options' => ['Teknik Komputer & Jaringan', 'Rekayasa Perangkat Lunak', 'Akuntansi & Keuangan', 'Teknik Kendaraan Ringan', 'Desain Komunikasi Visual'], 'is_required' => false],
                ['step' => 'tambahan', 'field_key' => 'is_colorblind', 'field_label' => 'Status Buta Warna', 'field_type' => 'select', 'options' => ['Tidak Buta Warna', 'Buta Warna Parsial', 'Buta Warna Total'], 'is_required' => true],
            ],
            self::TIER_PESANTREN => [
                ['step' => 'biodata', 'field_key' => 'nickname', 'field_label' => 'Nama Panggilan', 'field_type' => 'text', 'is_required' => true],
                ['step' => 'asal_sekolah', 'field_key' => 'previous_school_name', 'field_label' => 'Nama Sekolah / Madrasah Asal', 'field_type' => 'text', 'is_required' => true],
                ['step' => 'tambahan', 'field_key' => 'quran_reading_ability', 'field_label' => 'Kemampuan Membaca Al-Qur\'an', 'field_type' => 'select', 'options' => ['Belum Lancar', 'Lancar dengan Tajwid', 'Mahir / Tartil'], 'is_required' => true],
                ['step' => 'tambahan', 'field_key' => 'tahfidz_juz', 'field_label' => 'Jumlah Hafalan (Juz)', 'field_type' => 'number', 'is_required' => false],
                ['step' => 'tambahan', 'field_key' => 'residence_type', 'field_label' => 'Pilihan Status Santri', 'field_type' => 'select', 'options' => ['Santri Mukim (Tinggal di Asrama)', 'Santri Non-Mukim (Pulang Pergi)'], 'is_required' => true],
            ],
            default => throw new InvalidArgumentException("Tier [{$tier}] tidak dikenal."),
        };
    }
}

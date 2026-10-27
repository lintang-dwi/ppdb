<?php

namespace App\Http\Requests;

use App\Services\FormSchemaValidatorService;
use Illuminate\Foundation\Http\FormRequest;

class StoreApplicantPublicRequest extends FormRequest
{
    protected array $dynamicAttributes = [];

    public function authorize(): bool
    {
        return true; // Public registration
    }

    public function rules(FormSchemaValidatorService $validatorService): array
    {
        $waveId = $this->input('wave_id');
        $dynamic = $validatorService->buildRulesForWave($waveId ? (int) $waveId : null);
        $this->dynamicAttributes = $dynamic['attributes'];

        $coreRules = [
            'wave_id' => ['required', 'integer', 'exists:ppdb_waves,id'],
            'track_id' => ['required', 'integer', 'exists:ppdb_tracks,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'digits:16'],
            'birth_date' => ['required', 'date', 'before:today'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', 'in:L,P'],
            'phone_whatsapp' => ['required', 'string', 'max:25'],
            'is_physical_submitted' => ['nullable', 'boolean'],
            'custom_fields' => ['nullable', 'array'],
            'documents' => ['nullable', 'array'],
        ];

        return array_merge($coreRules, $dynamic['rules']);
    }

    public function attributes(): array
    {
        $coreAttributes = [
            'wave_id' => 'Gelombang Pendaftaran',
            'track_id' => 'Jalur Pendaftaran',
            'full_name' => 'Nama Lengkap Siswa',
            'nik' => 'Nomor Induk Kependudukan (NIK)',
            'birth_date' => 'Tanggal Lahir',
            'birth_place' => 'Tempat Lahir',
            'gender' => 'Jenis Kelamin',
            'phone_whatsapp' => 'Nomor WhatsApp Aktif',
            'is_physical_submitted' => 'Penyerahan Berkas Fisik Langsung',
        ];

        return array_merge($coreAttributes, $this->dynamicAttributes);
    }

    public function messages(): array
    {
        return [
            'nik.digits' => 'NIK harus berjumlah tepat 16 digit angka.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
            'phone_whatsapp.required' => 'Nomor WhatsApp wajib diisi untuk konfirmasi pendaftaran.',
        ];
    }
}

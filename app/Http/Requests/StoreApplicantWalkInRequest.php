<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicantWalkInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('ppdb')->check();
    }

    public function rules(): array
    {
        return [
            'wave_id' => ['required', 'integer', 'exists:ppdb_waves,id'],
            'track_id' => ['required', 'integer', 'exists:ppdb_tracks,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:20'],
            'birth_date' => ['required', 'date', 'before:today'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', 'in:L,P'],
            'phone_whatsapp' => ['required', 'string', 'max:25'],
            'is_physical_submitted' => ['nullable', 'boolean'],
            'custom_fields' => ['nullable', 'array'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['nullable', 'file', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'wave_id' => 'Gelombang Pendaftaran',
            'track_id' => 'Jalur Pendaftaran',
            'full_name' => 'Nama Lengkap Siswa',
            'nik' => 'Nomor Induk Kependudukan (NIK)',
            'birth_date' => 'Tanggal Lahir',
            'birth_place' => 'Tempat Lahir',
            'gender' => 'Jenis Kelamin',
            'phone_whatsapp' => 'Nomor WhatsApp',
            'is_physical_submitted' => 'Berkas fisik diterima langsung',
        ];
    }
}

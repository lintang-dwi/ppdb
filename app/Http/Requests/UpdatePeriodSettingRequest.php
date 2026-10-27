<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePeriodSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('ppdb')->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'academic_year' => ['required', 'string', 'max:20'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'announcement_date' => ['nullable', 'date', 'after:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after' => 'Tanggal penutupan harus setelah tanggal pembukaan.',
            'announcement_date.after' => 'Tanggal pengumuman harus setelah tanggal pembukaan.',
        ];
    }
}

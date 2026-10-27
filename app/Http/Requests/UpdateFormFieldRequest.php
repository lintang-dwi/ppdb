<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('ppdb')->check();
    }

    public function rules(): array
    {
        return [
            'wave_id' => ['nullable', 'integer', 'exists:ppdb_waves,id'],
            'step' => ['required', 'string', 'in:biodata,orang_tua,asal_sekolah,berkas,tambahan'],
            'field_key' => ['required', 'string', 'alpha_dash', 'max:100'],
            'field_label' => ['required', 'string', 'max:150'],
            'field_type' => ['required', 'string', 'in:text,number,select,radio,checkbox,textarea,date,file'],
            'options' => ['nullable'],
            'is_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'order_index' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

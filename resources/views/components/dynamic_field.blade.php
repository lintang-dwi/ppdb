@props(['field', 'value' => null])

@php
    $inputName = $field->field_type === 'file' ? "documents[{$field->field_key}]" : "custom_fields[{$field->field_key}]";
    $inputId = "field_{$field->field_key}";
@endphp

<div class="space-y-1.5">
    <div class="flex items-center justify-between">
        <label for="{{ $inputId }}" class="block text-xs font-semibold text-slate-700">
            {{ $field->field_label }}
            @if ($field->is_required)
                <span class="text-rose-500 font-bold ml-0.5">*</span>
            @endif
        </label>
        <span class="text-3xs font-medium {{ $field->is_required ? 'text-rose-600' : 'text-slate-400' }}">
            {{ $field->is_required ? 'Wajib Diisi' : 'Boleh Dikosongkan / Menyusul' }}
        </span>
    </div>

    @switch($field->field_type)
        @case('textarea')
            <textarea id="{{ $inputId }}" name="{{ $inputName }}" rows="3" {{ $field->is_required ? 'required' : '' }}
                class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">{{ old($inputName, $value) }}</textarea>
            @break

        @case('select')
            <select id="{{ $inputId }}" name="{{ $inputName }}" {{ $field->is_required ? 'required' : '' }}
                class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 bg-white">
                <option value="">-- Pilih {{ $field->field_label }} --</option>
                @if (is_array($field->options))
                    @foreach ($field->options as $opt)
                        <option value="{{ $opt }}" {{ old($inputName, $value) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                @endif
            </select>
            @break

        @case('radio')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-0.5">
                @if (is_array($field->options))
                    @foreach ($field->options as $opt)
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-emerald-600">
                            <input type="radio" name="{{ $inputName }}" value="{{ $opt }}" {{ old($inputName, $value) == $opt ? 'checked' : '' }}
                                class="text-emerald-700 focus:ring-emerald-600">
                            <span class="text-xs text-slate-700 font-medium">{{ $opt }}</span>
                        </label>
                    @endforeach
                @endif
            </div>
            @break

        @case('file')
            <div class="rounded-xl border border-dashed border-slate-300 p-4 text-center hover:border-emerald-500 bg-slate-50/60 transition">
                <input type="file" id="{{ $inputId }}" name="{{ $inputName }}" accept="image/jpeg,image/png,image/jpg,application/pdf"
                    {{ $field->is_required ? 'required' : '' }} data-originally-required="{{ $field->is_required ? 'true' : 'false' }}" class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                <p class="text-3xs text-slate-500 mt-1.5">Format: JPG, PNG, atau PDF (Maks. 5MB). Foto kamera HP otomatis dikompresi.</p>
            </div>
            @break

        @default
            <input type="{{ $field->field_type === 'number' ? 'number' : ($field->field_type === 'date' ? 'date' : 'text') }}"
                id="{{ $inputId }}" name="{{ $inputName }}" value="{{ old($inputName, $value) }}" {{ $field->is_required ? 'required' : '' }}
                class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
    @endswitch

    @error($inputName)
        <p class="text-2xs text-rose-600 flex items-center gap-1 mt-1" role="alert">
            <svg class="size-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ $message }}</span>
        </p>
    @enderror
</div>

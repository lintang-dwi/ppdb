@extends('layouts.ppdb_cms')

@section('title', 'Formulir Pendaftaran Dinamis')

@section('content')
<div class="space-y-6" x-data="{ presetModal: false, fieldModal: false, isEdit: false, form: { id: null, step: 'biodata', field_key: '', field_label: '', field_type: 'text', options: '', is_required: false, is_active: true } }">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Pengaturan Formulir Pendaftaran</h1>
            <p class="text-xs text-slate-500 mt-1">Formulir Builder untuk menyesuaikan isian dan berkas pendaftaran calon siswa sesuai kebutuhan sekolah.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if ($currentTier)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                    <span>Jenjang: {{ $currentTierLabel }}</span>
                </span>
            @endif
            <button type="button" @click="presetModal = true" class="btn-secondary text-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Template Jenjang</span>
            </button>
            <button type="button" @click="isEdit = false; form = { id: null, step: 'biodata', field_key: '', field_label: '', field_type: 'text', options: '', is_required: false, is_active: true }; fieldModal = true" class="btn-primary text-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Field</span>
            </button>
        </div>
    </div>

    <!-- Steps & Field Groups -->
    @foreach ($steps as $stepKey => $stepTitle)
        @php $stepFields = $fields->get($stepKey, collect()); @endphp
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold">{{ $loop->iteration }}</span>
                    <h2 class="text-sm font-bold text-slate-900">{{ $stepTitle }}</h2>
                    <span class="text-xs text-slate-500 font-medium">({{ $stepFields->count() }} field)</span>
                </div>
                <button type="button" @click="isEdit = false; form = { id: null, step: '{{ $stepKey }}', field_key: '', field_label: '', field_type: 'text', options: '', is_required: false, is_active: true }; fieldModal = true" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 transition rounded-md">+ Tambah di Bagian Ini</button>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($stepFields as $field)
                    <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 text-xs">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-slate-900 text-sm truncate">{{ $field->field_label }}</span>
                                <span class="font-mono text-slate-400 text-2xs">({{ $field->field_key }})</span>
                                @if ($field->is_required)
                                    <span class="pill pill-need-revision text-2xs">Wajib</span>
                                @else
                                    <span class="pill bg-slate-100 text-slate-600 text-2xs">Opsional</span>
                                @endif
                                <span class="pill bg-slate-100 text-slate-700 text-2xs uppercase font-mono">{{ $field->field_type }}</span>
                            </div>
                            @if ($field->options)
                                <p class="text-2xs text-slate-500 mt-1 truncate">Pilihan: {{ implode(', ', $field->options) }}</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-2.5 shrink-0">
                            <form method="POST" action="{{ tenant_route('cms.form-builder.toggle', ['field' => $field->id]) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded-lg text-2xs font-semibold {{ $field->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }} transition">
                                    {{ $field->is_active ? 'Tampil' : 'Sembunyi' }}
                                </button>
                            </form>
                            <button type="button" @click="isEdit = true; form = { id: {{ $field->id }}, step: '{{ $field->step }}', field_key: '{{ $field->field_key }}', field_label: '{{ addslashes($field->field_label) }}', field_type: '{{ $field->field_type }}', options: '{{ is_array($field->options) ? implode("\n", $field->options) : '' }}', is_required: {{ $field->is_required ? 'true' : 'false' }}, is_active: {{ $field->is_active ? 'true' : 'false' }} }; fieldModal = true" class="text-slate-600 hover:text-slate-900 font-medium px-2 py-1 rounded-md hover:bg-slate-50 transition">Edit</button>
                            <form method="POST" action="{{ tenant_route('cms.form-builder.destroy', ['field' => $field->id]) }}" onsubmit="return confirm('Hapus field ini?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-medium px-2 py-1 rounded-md hover:bg-rose-50 transition">Hapus</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-slate-400 text-xs italic">Belum ada field tambahan untuk bagian ini.</p>
                @endforelse
            </div>
        </div>
    @endforeach

    <!-- Modal Form Field -->
    <div x-show="fieldModal" x-cloak @keydown.escape.window="fieldModal = false" role="dialog" aria-modal="true" aria-labelledby="modal-field-title" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50">
        <div @click.away="fieldModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-lg w-full p-6 space-y-4">
            <h3 id="modal-field-title" class="text-base font-bold text-slate-900" x-text="isEdit ? 'Edit Field Formulir' : 'Tambah Field Baru'"></h3>
            <form :action="isEdit ? '{{ tenant_route('cms.form-builder.index') }}/' + form.id : '{{ tenant_route('cms.form-builder.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="field_step" class="block text-xs font-semibold text-slate-700">Kelompok / Langkah</label>
                        <select id="field_step" name="step" x-model="form.step" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                            @foreach ($steps as $key => $title)<option value="{{ $key }}">{{ $title }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label for="field_type" class="block text-xs font-semibold text-slate-700">Tipe Input</label>
                        <select id="field_type" name="field_type" x-model="form.field_type" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                            <option value="text">Teks Singkat (Text)</option>
                            <option value="number">Angka (Number)</option>
                            <option value="select">Pilihan Menu (Select Dropdown)</option>
                            <option value="radio">Pilihan Opsi (Radio Button)</option>
                            <option value="checkbox">Centang (Checkbox)</option>
                            <option value="textarea">Teks Panjang (Textarea)</option>
                            <option value="date">Tanggal (Date)</option>
                            <option value="file">Unggah Berkas (File Upload)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="field_label" class="block text-xs font-semibold text-slate-700">Label Pertanyaan / Isian</label>
                    <input type="text" id="field_label" name="field_label" x-model="form.field_label" required placeholder="Contoh: Nomor Kartu Keluarga (KK)" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label for="field_key" class="block text-xs font-semibold text-slate-700">Kunci Identitas (Field Key)</label>
                    <input type="text" id="field_key" name="field_key" x-model="form.field_key" required placeholder="Contoh: kk_number" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm font-mono focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                </div>
                <div x-show="['select', 'radio', 'checkbox'].includes(form.field_type)">
                    <label for="field_options" class="block text-xs font-semibold text-slate-700">Opsi Pilihan (Pisahkan dengan koma atau baris baru)</label>
                    <textarea id="field_options" name="options" x-model="form.options" rows="2" placeholder="Opsi A, Opsi B, Opsi C" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="field_is_required" name="is_required" value="1" x-model="form.is_required" class="rounded-sm border-slate-300 text-emerald-700 focus:ring-emerald-600">
                    <label for="field_is_required" class="text-xs font-medium text-slate-700">Wajib Diisi (Calon siswa tidak bisa lanjut jika kosong)</label>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="fieldModal = false" class="btn-secondary text-xs w-full sm:w-auto">Batal</button>
                    <button type="submit" class="btn-primary text-xs w-full sm:w-auto">Simpan Field</button>
                </div>
            </form>
        </div>
    </div>

    <x-form_preset_modal :current-tier="$currentTier" :current-wave-id="$currentWaveId" />
</div>
@endsection

@extends('layouts.ppdb_cms')

@section('title', 'Pendaftaran di Loket')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Daftar di Loket (Mode Meja)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Entri cepat data calon siswa dari berkas fisik yang dibawa langsung ke sekolah.</p>
        </div>
        <div>
            <span class="pill pill-verified">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                Loket Pendaftaran Aktif
            </span>
        </div>
    </div>

    @if (session('registered_applicant'))
        @php $reg = session('registered_applicant'); @endphp
        <div class="p-6 rounded-2xl bg-emerald-50 border border-emerald-200 space-y-3 shadow-xs" x-data="{ autoPrinted: false }" x-init="if (!autoPrinted) { window.open('{{ $reg['slip_url'] }}', '_blank'); autoPrinted = true; }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-2xs uppercase font-bold tracking-wider text-emerald-800">Berhasil Didaftarkan di Loket!</span>
                    <h2 class="text-xl font-bold text-slate-900">{{ $reg['name'] }}</h2>
                    <p class="text-xs font-mono font-bold text-emerald-700">{{ $reg['reg_number'] }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ $reg['slip_url'] }}" target="_blank" class="btn-primary text-xs flex items-center gap-1.5 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Cetak Bukti (PDF)</span>
                    </a>
                    <a href="{{ tenant_route('cms.applicants.show', ['applicant' => $reg['id']]) }}" class="btn-secondary text-xs">Lihat Data</a>
                </div>
            </div>
        </div>
    @endif

    <!-- Walk-in Entry Form -->
    <form method="POST" action="{{ tenant_route('cms.walk-in.store') }}" enctype="multipart/form-data" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-6">
        @csrf

        <!-- Track & Wave Selection -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-6 border-b border-slate-100">
            <div>
                <label for="walkin_wave_id" class="block text-xs font-semibold text-slate-700 mb-1">Gelombang Pendaftaran <span class="text-rose-500">*</span></label>
                <select id="walkin_wave_id" name="wave_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 bg-white">
                    @foreach ($waves as $w)
                        <option value="{{ $w->id }}" {{ old('wave_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->academic_year }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="walkin_track_id" class="block text-xs font-semibold text-slate-700 mb-1">Jalur Masuk <span class="text-rose-500">*</span></label>
                <select id="walkin_track_id" name="track_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 bg-white">
                    @foreach ($tracks as $t)
                        <option value="{{ $t->id }}" {{ old('track_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Student Identity -->
        <div class="space-y-4">
            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Identitas Calon Siswa</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label for="walkin_full_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                    <input type="text" id="walkin_full_name" name="full_name" value="{{ old('full_name') }}" required placeholder="Contoh: Muhammad Ihsan" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label for="walkin_nik" class="block text-xs font-semibold text-slate-700 mb-1">NIK (16 Digit) <span class="text-rose-500">*</span></label>
                    <input type="text" id="walkin_nik" name="nik" value="{{ old('nik') }}" required placeholder="16 digit NIK calon siswa" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-mono focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label for="walkin_gender" class="block text-xs font-semibold text-slate-700 mb-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select id="walkin_gender" name="gender" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 bg-white">
                        <option value="L" {{ old('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label for="walkin_birth_place" class="block text-xs font-semibold text-slate-700 mb-1">Tempat Lahir</label>
                    <input type="text" id="walkin_birth_place" name="birth_place" value="{{ old('birth_place') }}" placeholder="Kota / Kabupaten" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label for="walkin_birth_date" class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Lahir <span class="text-rose-500">*</span></label>
                    <input type="date" id="walkin_birth_date" name="birth_date" value="{{ old('birth_date') }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 bg-white">
                </div>
                <div class="sm:col-span-2">
                    <label for="walkin_phone_whatsapp" class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp Aktif <span class="text-rose-500">*</span></label>
                    <input type="tel" id="walkin_phone_whatsapp" name="phone_whatsapp" value="{{ old('phone_whatsapp') }}" required placeholder="Contoh: 081234567890" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                </div>
            </div>
        </div>

        <!-- Custom Fields (if configured) -->
        @if ($customFields->isNotEmpty())
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Data Tambahan Formulir</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ($customFields as $field)
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ $field->label }}</label>
                            <input type="text" name="custom_fields[{{ $field->field_name }}]" value="{{ old('custom_fields.'.$field->field_name) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Physical Document Reception & Digital Upload Option -->
        <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200 space-y-3">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="is_physical_submitted" value="1" checked class="mt-0.5 rounded border-amber-300 text-emerald-700 focus:ring-emerald-600 w-4 h-4">
                <div>
                    <span class="block text-xs font-bold text-slate-900">Berkas fisik diterima langsung di loket</span>
                    <span class="block text-2xs text-slate-500">Tandai jika pemohon telah menyerahkan dokumen fisik lengkap di loket (otomatis terverifikasi).</span>
                </div>
            </label>
            <div class="pt-2 border-t border-amber-200/60">
                <label class="block text-2xs font-semibold text-slate-700 mb-1">Scan / Foto Berkas (Opsional):</label>
                <input type="file" name="documents[berkas_loket]" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-white file:text-slate-700 hover:file:bg-slate-100 cursor-pointer">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            <span class="text-3xs text-slate-400">Pendaftaran akan otomatis mendapatkan nomor registrasi resmi.</span>
            <button type="submit" class="btn-primary w-full sm:w-auto text-xs text-center flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Daftar di Loket &amp; Cetak Slip</span>
            </button>
        </div>
    </form>
</div>
@endsection

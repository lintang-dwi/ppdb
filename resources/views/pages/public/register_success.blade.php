@extends('layouts.ppdb_public')

@section('title', 'Pendaftaran Berhasil - '.$applicant->registration_number)

@section('content')
<div class="max-w-xl mx-auto py-8 sm:py-12 px-4 sm:px-6" x-data="{ autoDownloaded: false }" x-init="setTimeout(() => { $refs.slipLink.click(); autoDownloaded = true; }, 800)">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6 text-center">
        <!-- Emerald Checkmark Icon -->
        <div class="w-14 h-14 mx-auto rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        </div>

        <div class="space-y-1">
            <span class="pill pill-verified">Pendaftaran Selesai</span>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 pt-1">Pendaftaran Berhasil, Data Telah Diterima</h1>
            <p class="text-xs text-slate-500">Simpan nomor registrasi Anda dengan baik untuk memeriksa perkembangan status pendaftaran.</p>
        </div>

        <!-- Monospace Dashed Registration Box -->
        <div class="p-4 rounded-2xl border-2 border-dashed border-emerald-300 bg-emerald-50/60 text-center space-y-1">
            <span class="text-2xs font-bold uppercase tracking-wider text-emerald-800 block">Nomor Registrasi Resmi</span>
            <div class="text-xl sm:text-2xl font-black font-mono tracking-wider text-slate-900 select-all break-all">
                {{ $applicant->registration_number }}
            </div>
        </div>

        <!-- Summary Card -->
        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5 text-left space-y-3">
            <div class="border-b border-slate-200/80 pb-2 flex items-center justify-between">
                <span class="text-2xs uppercase tracking-wider font-semibold text-slate-500">Ringkasan Pendaftar</span>
                <span class="pill pill-submitted">Tersimpan</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                    <span class="text-slate-500 block text-3xs">Nama Lengkap</span>
                    <strong class="text-slate-800 break-words">{{ $applicant->full_name }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-3xs">Tanggal Lahir</span>
                    <strong class="text-slate-800">{{ \Carbon\Carbon::parse($applicant->birth_date)->translatedFormat('d F Y') }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-3xs">Jalur Masuk</span>
                    <strong class="text-slate-800">{{ $applicant->track?->name ?? 'Reguler' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-3xs">Sekolah Tujuan</span>
                    <strong class="text-slate-800 break-words">{{ $school->name }}</strong>
                </div>
            </div>
        </div>

        <!-- 3 Action Buttons (PDF, WhatsApp, Cek Status) -->
        <div class="space-y-3 pt-2">
            <!-- 1. PDF Download (Layer 1 with Auto-download hook) -->
            <div>
                <a x-ref="slipLink" href="{{ tenant_route('applicant.slip.download', ['reg' => $applicant->registration_number, 'dob' => $applicant->birth_date]) }}" download
                   class="btn-primary w-full text-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Unduh Bukti Pendaftaran (PDF)</span>
                </a>
                <p class="text-3xs text-slate-500 mt-1">Bukti pendaftaran diunduh otomatis. Jika belum terunduh, klik tombol di atas.</p>
            </div>

            <!-- 2. WhatsApp Self-Share (Layer 2) -->
            <div>
                <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer"
                   class="min-h-[44px] w-full py-2.5 px-4 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-semibold text-xs transition flex items-center justify-center gap-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                    <svg class="w-4 h-4 fill-emerald-600" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    <span>Simpan Bukti ke WhatsApp (Self-Share)</span>
                </a>
                <p class="text-3xs text-slate-500 mt-1">Kirim ke nomor WhatsApp Anda sendiri atau keluarga agar nomor registrasi tersimpan aman.</p>
            </div>

            <!-- 3. Cek Status (Layer 3) -->
            <a href="{{ tenant_route('applicant.status.lookup') }}" class="btn-secondary w-full text-center text-xs">
                Cek Status Pendaftaran &rarr;
            </a>
        </div>

        <div class="pt-2 border-t border-slate-100 text-center">
            <a href="{{ tenant_route('tenant.home') }}" class="min-h-[44px] inline-flex items-center text-xs text-slate-500 hover:text-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                &larr; Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection


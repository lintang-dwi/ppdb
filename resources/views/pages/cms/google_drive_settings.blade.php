@extends('layouts.ppdb_cms')

@section('title', 'Pengaturan Google Drive')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Penyimpanan Google Drive</h1>
        <p class="text-xs text-slate-500 mt-1">Hubungkan akun Google Drive resmi sekolah untuk pengarsipan mandiri berkas calon siswa.</p>
    </div>

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <!-- Connection Status Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M7.71 3.5L1.15 15l3.43 6 6.55-11.5M9.73 15L6.3 21h13.12l3.43-6M22.85 15l-6.55-11.5H9.42L15.98 15"/></svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Google Drive Sekolah</h2>
                    <span class="text-xs text-slate-500">Penyimpanan cloud mandiri milik sekolah</span>
                </div>
            </div>
            <div>
                @if ($school->hasGoogleDriveConnected())
                    <span class="pill pill-verified">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                        Terhubung
                    </span>
                @else
                    <span class="pill bg-amber-50 text-amber-800 border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Belum Terhubung
                    </span>
                @endif
            </div>
        </div>

        @if ($school->hasGoogleDriveConnected())
            <!-- Connected Details -->
            <div class="space-y-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-1 sm:gap-2">
                        <span class="text-slate-500">Akun Google Terdaftar:</span>
                        <strong class="font-mono text-slate-800 break-all">{{ $school->google_drive_email ?: 'Akun Google Sekolah' }}</strong>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-1 sm:gap-2">
                        <span class="text-slate-500">Status Penyimpanan:</span>
                        <span class="font-semibold text-emerald-700">Aktif & Siap Menerima Berkas</span>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-1 sm:gap-2">
                        <span class="text-slate-500">Struktur Direktori Otomatis:</span>
                        <span class="font-mono text-2xs text-slate-600 break-all">/PPDB_{TAHUN}/{NO_REG}_{NAMA}/...</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <p class="text-2xs text-slate-500">
                        Berkas pendaftar baru akan otomatis diunggah ke Google Drive di atas secara aman.
                    </p>
                    <form method="POST" action="{{ tenant_route('cms.gdrive.disconnect') }}" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan hubungan akun Google Drive sekolah? Dokumen pendaftar baru tidak akan terunggah ke Google Drive hingga dihubungkan kembali.')" class="w-full sm:w-auto">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-secondary text-xs text-rose-700 border-rose-200 hover:bg-rose-50 w-full sm:w-auto">
                            Putus Hubungan Akun
                        </button>
                    </form>
                </div>
            </div>
        @else
            <!-- Disconnected State -->
            <div class="space-y-4">
                <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200 text-xs text-amber-900 space-y-1.5">
                    <strong class="font-bold text-amber-800 block text-xs">Penyimpanan Cloud Belum Aktif</strong>
                    <p class="leading-relaxed">
                        Dokumen pendaftaran (Akta, KK, Ijazah, dll) yang diunggah oleh calon siswa saat ini belum dapat disimpan ke cloud Google Drive sekolah Anda. Silakan hubungkan akun Google Drive resmi sekolah sekarang.
                    </p>
                </div>

                <div class="pt-2 flex justify-start">
                    <a href="{{ tenant_route('cms.gdrive.connect') }}" class="btn-primary text-xs w-full sm:w-auto">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M7.71 3.5L1.15 15l3.43 6 6.55-11.5M9.73 15L6.3 21h13.12l3.43-6M22.85 15l-6.55-11.5H9.42L15.98 15"/></svg>
                        <span>Hubungkan Akun Google Drive Sekolah</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Security Information -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs text-xs text-slate-500 space-y-1.5">
        <h3 class="font-bold text-slate-800 text-xs">Keamanan & Privasi Data</h3>
        <p class="leading-relaxed text-2xs">
            Platform PPDB hanya meminta izin untuk mengunggah dan membuat folder pendaftaran PPDB. Token akses dienkripsi secara ketat di database dan tidak akan disalahgunakan untuk keperluan di luar pengarsipan dokumen PPDB sekolah Anda.
        </p>
    </div>
</div>
@endsection

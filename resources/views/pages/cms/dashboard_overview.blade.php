@extends('layouts.ppdb_cms')

@section('title', 'Dashboard Ringkasan')

@section('content')
<div class="space-y-6">
    <!-- Top Welcome Banner without Gradient -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Selamat Datang, {{ auth('ppdb')->user()?->name }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                Panel Administrasi PPDB <strong class="text-slate-800">{{ $school->name }}</strong>
            </p>
        </div>
        <div>
            @if ($activeWave && $activeWave->isOpen())
                <span class="pill pill-verified">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                    {{ $activeWave->name }} (Buka)
                </span>
            @else
                <span class="pill pill-cancelled">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                    Tidak Ada Gelombang Aktif
                </span>
            @endif
        </div>
    </div>

    <!-- 8 Metrics in a 4-Column x 2-Row Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-semibold text-slate-500 block">Total Pendaftar</span>
            <p class="text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($counts['total']) }}</p>
            <span class="text-3xs text-slate-400 block">Semua jalur masuk</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-semibold text-blue-600 block">Terdaftar (Antrean Baru)</span>
            <p class="text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($counts['submitted']) }}</p>
            <span class="text-3xs text-slate-400 block">Menunggu verifikasi</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-semibold text-amber-600 block">Perlu Perbaikan</span>
            <p class="text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($counts['need_revision']) }}</p>
            <span class="text-3xs text-slate-400 block">Menunggu perbaikan siswa</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-semibold text-emerald-700 block">Berkas Lengkap</span>
            <p class="text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($counts['verified']) }}</p>
            <span class="text-3xs text-slate-400 block">Siap seleksi kuota</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-semibold text-purple-600 block">Diterima</span>
            <p class="text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($counts['accepted']) }}</p>
            <span class="text-3xs text-slate-400 block">Lolos seleksi</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-semibold text-teal-600 block">Selesai Daftar Ulang</span>
            <p class="text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($counts['completed']) }}</p>
            <span class="text-3xs text-slate-400 block">Siswa resmi</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-semibold text-rose-600 block">Dibatalkan / Gugur</span>
            <p class="text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($counts['cancelled']) }}</p>
            <span class="text-3xs text-slate-400 block">Mundur atau gugur</span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-semibold text-indigo-600 block">Daftar di Loket</span>
            <p class="text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($counts['walk_in']) }}</p>
            <span class="text-3xs text-slate-400 block">Entri meja panitia</span>
        </div>
    </div>

    <!-- Quick Action Navigation: 1 Primary (Buka Loket) + 2 Secondary -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-4">
        <div>
            <h2 class="text-sm font-bold text-slate-900">Aksi Cepat Panitia</h2>
            <p class="text-xs text-slate-500">Akses langsung ke menu verifikasi dan pelayanan pendaftar.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <!-- 1 Tombol Primer: Buka Loket -->
            <a href="{{ tenant_route('cms.walk-in.index') }}"
               class="btn-primary w-full text-center text-xs flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buka Loket Pendaftaran</span>
            </a>

            <!-- Tombol Sekunder 1: Verifikasi Berkas -->
            <a href="{{ tenant_route('cms.applicants.index') }}"
               class="btn-secondary w-full text-center text-xs flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Verifikasi Berkas Pendaftar</span>
            </a>

            <!-- Tombol Sekunder 2: Kelola Periode & Jalur -->
            <a href="{{ tenant_route('cms.waves.index') }}"
               class="btn-secondary w-full text-center text-xs flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Kelola Periode &amp; Jalur</span>
            </a>
        </div>
    </div>
</div>
@endsection


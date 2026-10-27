@extends('layouts.ppdb_public')

@section('title', 'Pendaftaran Ditutup - '.$school->name)

@section('content')
<div class="max-w-md mx-auto py-10 sm:py-16 px-4 sm:px-6 text-center">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6">
        <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <div class="space-y-1.5">
            <span class="pill pill-cancelled">Pendaftaran Sedang Ditutup</span>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 pt-1">Pendaftaran Belum Dibuka</h1>
            <p class="text-xs text-slate-600 leading-relaxed">
                Saat ini belum ada gelombang pendaftaran yang sedang dibuka untuk <span class="font-semibold text-slate-800">{{ $school->name }}</span>.
            </p>
        </div>

        @if ($upcomingWave)
            <div class="bg-slate-50 rounded-2xl border border-slate-200 p-4 text-left space-y-1.5">
                <span class="text-2xs uppercase tracking-wider font-bold text-slate-500">Jadwal Gelombang Berikutnya</span>
                <div class="font-bold text-slate-900 text-sm">{{ $upcomingWave->name }} (TA {{ $upcomingWave->academic_year }})</div>
                <p class="text-xs text-slate-600">
                    Dibuka: <strong class="text-slate-800">{{ \Carbon\Carbon::parse($upcomingWave->start_date)->translatedFormat('d F Y, H:i') }} WIB</strong>
                </p>
            </div>
        @endif

        <div class="pt-2 flex flex-col sm:flex-row gap-2.5 justify-center">
            <a href="{{ tenant_route('tenant.home') }}" class="btn-primary w-full sm:w-auto text-center text-xs">
                Kembali ke Beranda
            </a>
            <a href="{{ tenant_route('applicant.status.lookup') }}" class="btn-secondary w-full sm:w-auto text-center text-xs">
                Cek Status Pendaftaran
            </a>
        </div>
    </div>
</div>
@endsection


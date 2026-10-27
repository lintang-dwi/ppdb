@extends('layouts.ppdb_public')

@section('title', 'Cek Status Pendaftaran - '.$school->name)

@section('content')
<div class="max-w-md mx-auto py-10 sm:py-16 px-4 sm:px-6">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6">
        <div class="text-center space-y-1">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <h1 class="text-xl font-bold text-slate-900 pt-1">Cek Status Pendaftaran</h1>
            <p class="text-xs text-slate-500">Masukkan nomor registrasi dan tanggal lahir calon siswa untuk melihat perkembangan berkas.</p>
        </div>

        <form action="{{ tenant_route('applicant.status.check') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="reg_number" class="block text-xs font-semibold text-slate-700">Nomor Registrasi</label>
                <input type="text" id="reg_number" name="registration_number" required placeholder="Contoh: REG-SMAN1JKT-2026-0001"
                       class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm font-mono focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden">
            </div>

            <div>
                <label for="birth_date" class="block text-xs font-semibold text-slate-700">Tanggal Lahir Calon Siswa</label>
                <input type="date" id="birth_date" name="birth_date" required
                       class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden bg-white">
            </div>

            <button type="submit" class="btn-primary w-full text-center text-xs">
                Periksa Status Pendaftaran
            </button>
        </form>

        <div class="pt-2 text-center border-t border-slate-100">
            <a href="{{ tenant_route('tenant.home') }}" class="min-h-[44px] inline-flex items-center text-xs text-slate-500 hover:text-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                &larr; Kembali ke Beranda PPDB
            </a>
        </div>
    </div>
</div>
@endsection


@extends('layouts.ppdb_superadmin')

@section('title', 'Dashboard Superadmin')

@section('content')
<div class="space-y-6" x-data="superadminDashboard()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Monitoring Platform PPDB Multi-Tenant</h2>
            <p class="text-xs text-slate-400 mt-1">Pantau seluruh aktivitas pendaftaran, status Google Drive, dan login panitia sekolah.</p>
        </div>
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 border border-slate-800 text-slate-300 text-xs font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                Engine Aktif
            </span>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5">
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-3xs uppercase tracking-wider font-semibold text-slate-400">Sekolah Aktif</span>
            <p class="text-3xl font-extrabold text-white mt-1">{{ number_format($stats['total_schools']) }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-3xs uppercase tracking-wider font-semibold text-emerald-400">Gelombang Dibuka</span>
            <p class="text-3xl font-extrabold text-emerald-400 mt-1">{{ number_format($stats['total_waves']) }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-3xs uppercase tracking-wider font-semibold text-indigo-400">Total Pendaftar</span>
            <p class="text-3xl font-extrabold text-indigo-300 mt-1">{{ number_format($stats['total_applicants']) }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-3xs uppercase tracking-wider font-semibold text-teal-400">Berkas Terverifikasi</span>
            <p class="text-3xl font-extrabold text-teal-300 mt-1">{{ number_format($stats['total_verified']) }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 col-span-2 lg:col-span-1">
            <span class="text-3xs uppercase tracking-wider font-semibold text-amber-400">Google Drive Sekolah</span>
            <p class="text-3xl font-extrabold text-amber-300 mt-1">{{ $stats['total_gdrive_connected'] }} <span class="text-xs font-normal text-slate-400">/ {{ $stats['total_schools'] }}</span></p>
        </div>
    </div>

    <!-- Schools Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xs">
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-white">Daftar Sekolah &amp; Status PPDB</h3>
            <span class="text-xs text-slate-400">{{ count($schoolsData) }} sekolah terdaftar</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[700px]">
                <thead class="bg-slate-950/60 text-slate-400 uppercase text-3xs font-semibold tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-6">Sekolah</th>
                        <th class="py-3 px-6">Gelombang Aktif</th>
                        <th class="py-3 px-6">Google Drive</th>
                        <th class="py-3 px-6 text-center">Pendaftar</th>
                        <th class="py-3 px-6 text-right">Aksi Superadmin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-300">
                    @forelse ($schoolsData as $item)
                        @php $s = $item['school']; @endphp
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-4 px-6">
                                <div class="font-bold text-white text-sm">{{ $s->name }}</div>
                                <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                    <span class="text-3xs text-indigo-400 font-mono">Kode: {{ $s->school_code }}</span>
                                    @if ($s->custom_domain)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full {{ $s->custom_domain_verified ? 'bg-emerald-950/60 text-emerald-300 border border-emerald-800' : 'bg-amber-950/60 text-amber-300 border border-amber-800' }} text-3xs font-mono">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $s->custom_domain_verified ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                                            {{ $s->custom_domain }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                @if ($item['active_wave'])
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-950/60 text-emerald-300 border border-emerald-800 text-3xs font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        {{ $item['active_wave']->name }}
                                    </span>
                                @else
                                    <span class="text-slate-500 italic text-3xs">Belum ada gelombang aktif</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if ($item['gdrive_connected'])
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-950/60 text-amber-300 border border-amber-800 text-3xs font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        {{ $s->google_drive_email ?: 'Terhubung' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-400 text-3xs">
                                        Belum Terhubung
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center font-bold text-white">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-800 text-indigo-300 font-mono text-xs">
                                    {{ $item['applicants_count'] }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right space-x-1.5">
                                <button type="button" @click="openEditModal({ code: '{{ $s->school_code }}', name: '{{ addslashes($s->name) }}', logo: '{{ addslashes($s->logo ?? '') }}', favicon: '{{ addslashes($s->favicon ?? '') }}', custom_domain: '{{ $s->custom_domain }}', custom_domain_verified: {{ $s->custom_domain_verified ? 'true' : 'false' }} })" class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-300 border border-amber-900/50 text-3xs font-semibold transition inline-block">
                                    Atur Domain &amp; Brand
                                </button>
                                <a href="{{ url("/{$s->school_code}") }}" target="_blank" class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-3xs font-semibold transition inline-block">
                                    Portal &nearr;
                                </a>
                                <form method="POST" action="{{ route('superadmin.impersonate', $s->school_code) }}" class="inline-block">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-3xs font-bold shadow-xs transition">
                                        Login CMS &rarr;
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 italic">Belum ada sekolah yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-superadmin_edit_school_modal />
</div>
@endsection

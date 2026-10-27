@extends('layouts.ppdb_cms')

@section('title', 'Data Pendaftar PPDB')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Data Pendaftar</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola dan verifikasi berkas calon peserta didik baru.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="pill bg-white text-slate-700 border border-slate-200 shadow-2xs text-xs">
                Total Pendaftar: <strong>{{ $counts['all'] }}</strong>
            </span>
            <a href="{{ tenant_route('cms.applicants.export', request()->query()) }}"
               class="btn-secondary text-xs flex items-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Ekspor CSV</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar: Satu Baris di Desktop -->
    <form method="GET" action="{{ tenant_route('cms.applicants.index') }}" class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5 items-center">
            <div class="lg:col-span-5">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, no. registrasi, atau NIK..." aria-label="Cari nama, no. registrasi, atau NIK"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden">
            </div>
            <div class="lg:col-span-3">
                <select name="track_id" aria-label="Filter berdasarkan jalur pendaftaran" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden bg-white">
                    <option value="">Semua Jalur</option>
                    @foreach ($tracks as $t)
                        <option value="{{ $t->id }}" {{ request('track_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <select name="status" aria-label="Filter berdasarkan status verifikasi" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden bg-white">
                    <option value="">Semua Status</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Terdaftar</option>
                    <option value="need_revision" {{ request('status') === 'need_revision' ? 'selected' : '' }}>Perlu Perbaikan</option>
                    <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Berkas Lengkap</option>
                    <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Diterima</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai Daftar Ulang</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="lg:col-span-2 flex items-center gap-1.5">
                <button type="submit" class="btn-primary flex-1 text-xs py-2 px-3 text-center">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'track_id', 'status', 'origin']))
                    <a href="{{ tenant_route('cms.applicants.index') }}" class="btn-secondary text-xs py-2 px-2.5 text-center">
                        Reset
                    </a>
                @endif
            </div>
        </div>
    </form>

    <!-- Applicants Table: Tanpa Garis Tebal (Hanya border-b slate-100) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-500 font-semibold uppercase tracking-wider text-xs">
                        <th class="px-4 py-3.5">Pendaftar</th>
                        <th class="px-4 py-3.5">Jalur &amp; Gelombang</th>
                        <th class="px-4 py-3.5">Tipe</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($applicants as $applicant)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="p-4">
                                <a href="{{ tenant_route('cms.applicants.show', $applicant) }}" class="font-bold text-slate-900 hover:text-emerald-700 block text-xs">
                                    {{ $applicant->full_name }}
                                </a>
                                <span class="font-mono text-3xs text-emerald-700 font-bold">{{ $applicant->registration_number }}</span>
                                <span class="text-3xs text-slate-400 block">NIK: {{ $applicant->nik }}</span>
                            </td>
                            <td class="p-4">
                                <span class="font-semibold text-slate-800 block text-xs">{{ $applicant->track?->name ?? 'Jalur Reguler' }}</span>
                                <span class="text-3xs text-slate-500 block">{{ $applicant->wave?->name ?? 'Gelombang Umum' }}</span>
                            </td>
                            <td class="p-4">
                                @if ($applicant->registration_origin === 'offline_walk_in')
                                    <span class="pill bg-amber-50 text-amber-700 border border-amber-200 text-2xs">Loket</span>
                                @else
                                    <span class="pill bg-slate-100 text-slate-600 text-2xs">Online</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <x-status_toggle_switch :applicant="$applicant" />
                                @if ($applicant->status === 'need_revision' && $applicant->verification_notes)
                                    <span class="block text-3xs text-amber-700 italic mt-1 max-w-xs truncate" title="{{ $applicant->verification_notes }}">
                                        {{ $applicant->verification_notes }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ tenant_route('cms.applicants.show', $applicant) }}"
                                       class="btn-secondary text-2xs py-1 px-2.5">
                                        Periksa
                                    </a>
                                    <a href="{{ tenant_route('applicant.slip.download', ['reg' => $applicant->registration_number]) }}" target="_blank"
                                       class="p-1.5 rounded-xl border border-slate-200 text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition" title="Unduh Slip">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500">
                                @if (request()->hasAny(['search', 'track_id', 'status']))
                                    <p class="font-medium text-slate-700">Tidak ada calon siswa yang cocok dengan filter pencarian.</p>
                                    <a href="{{ tenant_route('cms.applicants.index') }}" class="inline-block mt-2 text-xs font-semibold text-emerald-700 hover:underline">
                                        Reset Filter Pencarian
                                    </a>
                                @else
                                    <p class="font-medium text-slate-700">Belum ada calon peserta didik yang mendaftar pada sistem.</p>
                                    <p class="text-2xs text-slate-500 mt-0.5">Pendaftaran baru dari jalur online atau entri loket akan otomatis tercatat di sini.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($applicants->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $applicants->links() }}
            </div>
        @endif
    </div>
</div>
@endsection


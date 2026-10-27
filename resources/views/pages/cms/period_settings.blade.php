@extends('layouts.ppdb_cms')

@section('title', 'Kelola Gelombang Pendaftaran')

@section('content')
<div class="space-y-6" x-data="{ modalOpen: false, isEdit: false, form: { id: null, name: '', academic_year: '{{ date('Y') }}/{{ date('Y') + 1 }}', start_date: '', end_date: '', announcement_date: '', is_active: true } }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Gelombang Pendaftaran</h1>
            <p class="text-xs text-slate-500 mt-0.5">Atur jadwal pembukaan, batas tutup, dan tahun ajaran PPDB sekolah.</p>
        </div>
        <button @click="isEdit = false; form = { id: null, name: '', academic_year: '{{ date('Y') }}/{{ date('Y') + 1 }}', start_date: '', end_date: '', announcement_date: '', is_active: true }; modalOpen = true" class="btn-primary text-xs flex items-center gap-1.5 shadow-2xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Gelombang</span>
        </button>
    </div>

    <!-- Table Card in White -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-xs border-collapse">
                <thead class="bg-slate-50/75 border-b border-slate-100 text-slate-500 font-semibold uppercase tracking-wider text-xs">
                    <tr>
                        <th class="px-6 py-3.5">Nama Gelombang</th>
                        <th class="px-6 py-3.5">Tahun Ajaran</th>
                        <th class="px-6 py-3.5">Rentang Tanggal</th>
                        <th class="px-6 py-3.5">Pengumuman</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($waves as $wave)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 font-bold text-slate-900 text-xs">{{ $wave->name }}</td>
                            <td class="px-6 py-4 text-slate-600 font-medium">{{ $wave->academic_year }}</td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                <span class="font-medium text-slate-800">{{ $wave->start_date->format('d M Y, H:i') }}</span>
                                <span class="text-slate-400 mx-1">s/d</span>
                                <span class="font-medium text-slate-800">{{ $wave->end_date->format('d M Y, H:i') }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $wave->announcement_date ? $wave->announcement_date->format('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($wave->isOpen())
                                    <span class="pill pill-verified text-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Buka
                                    </span>
                                @elseif ($wave->is_active)
                                    <span class="pill bg-amber-50 text-amber-800 border border-amber-200 text-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Menunggu
                                    </span>
                                @else
                                    <span class="pill bg-slate-100 text-slate-600 border border-slate-200 text-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Ditutup
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button @click="isEdit = true; form = { id: {{ $wave->id }}, name: '{{ addslashes($wave->name) }}', academic_year: '{{ $wave->academic_year }}', start_date: '{{ $wave->start_date->format('Y-m-d\TH:i') }}', end_date: '{{ $wave->end_date->format('Y-m-d\TH:i') }}', announcement_date: '{{ $wave->announcement_date ? $wave->announcement_date->format('Y-m-d\TH:i') : '' }}', is_active: {{ $wave->is_active ? 'true' : 'false' }} }; modalOpen = true" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 rounded-md">Edit</button>
                                <form method="POST" action="{{ tenant_route('cms.waves.toggle', ['wave' => $wave->id]) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold {{ $wave->is_active ? 'text-rose-600 hover:text-rose-800' : 'text-slate-600 hover:text-slate-800' }} focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 rounded-md">
                                        {{ $wave->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 text-xs">
                                <p>Belum ada gelombang pendaftaran yang dibuat.</p>
                                <button @click="isEdit = false; form = { id: null, name: '', academic_year: '{{ date('Y') }}/{{ date('Y') + 1 }}', start_date: '', end_date: '', announcement_date: '', is_active: true }; modalOpen = true" class="mt-2 text-xs font-bold text-emerald-700 hover:underline">
                                    + Tambah Gelombang Baru
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($waves->hasPages())
            <div class="p-4 border-t border-slate-100">{{ $waves->links() }}</div>
        @endif
    </div>

    <!-- Modal Form Tambah/Edit: rounded-2xl max-w-lg -->
    <div x-show="modalOpen" x-cloak @keydown.escape.window="modalOpen = false" role="dialog" aria-modal="true" aria-labelledby="modal-period-title" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50">
        <div @click.away="modalOpen = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-lg w-full p-6 space-y-4">
            <h3 id="modal-period-title" class="text-base font-bold text-slate-900" x-text="isEdit ? 'Edit Gelombang' : 'Tambah Gelombang Baru'"></h3>
            <form :action="isEdit ? '{{ tenant_route('cms.waves.index') }}/' + form.id : '{{ tenant_route('cms.waves.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                <div>
                    <label for="wave_name" class="block text-xs font-semibold text-slate-700">Nama Gelombang</label>
                    <input type="text" id="wave_name" name="name" x-model="form.name" required placeholder="Contoh: Gelombang 1 - Reguler" class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden">
                </div>
                <div>
                    <label for="wave_academic_year" class="block text-xs font-semibold text-slate-700">Tahun Ajaran</label>
                    <input type="text" id="wave_academic_year" name="academic_year" x-model="form.academic_year" required placeholder="2026/2027" class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="wave_start_date" class="block text-xs font-semibold text-slate-700">Tanggal &amp; Jam Buka</label>
                        <input type="datetime-local" id="wave_start_date" name="start_date" x-model="form.start_date" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden bg-white">
                    </div>
                    <div>
                        <label for="wave_end_date" class="block text-xs font-semibold text-slate-700">Tanggal &amp; Jam Tutup</label>
                        <input type="datetime-local" id="wave_end_date" name="end_date" x-model="form.end_date" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden bg-white">
                    </div>
                </div>
                <div>
                    <label for="wave_announcement_date" class="block text-xs font-semibold text-slate-700">Tanggal Pengumuman (Opsional)</label>
                    <input type="datetime-local" id="wave_announcement_date" name="announcement_date" x-model="form.announcement_date" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden bg-white">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" x-model="form.is_active" class="rounded-sm border-slate-300 text-emerald-700 focus:ring-emerald-500">
                    <label for="is_active" class="text-xs text-slate-700">Aktifkan gelombang ini</label>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" class="btn-secondary text-xs">Batal</button>
                    <button type="submit" class="btn-primary text-xs">Simpan Gelombang</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


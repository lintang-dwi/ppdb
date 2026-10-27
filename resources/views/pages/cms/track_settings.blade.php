@extends('layouts.ppdb_cms')

@section('title', 'Kelola Jalur Pendaftaran')

@section('content')
<div class="space-y-6" x-data="{ modalOpen: false, isEdit: false, form: { id: null, name: '', quota: '', description: '', is_active: true } }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Jalur Masuk &amp; Kuota</h1>
            <p class="text-xs text-slate-500 mt-0.5">Tentukan jalur penerimaan siswa (Zonasi, Prestasi, Reguler, Afirmasi, Tahfidz) beserta kuotanya.</p>
        </div>
        <button @click="isEdit = false; form = { id: null, name: '', quota: '', description: '', is_active: true }; modalOpen = true" class="btn-primary text-xs flex items-center gap-1.5 shadow-2xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Jalur</span>
        </button>
    </div>

    <!-- Table Card in White -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-left text-xs border-collapse">
                <thead class="bg-slate-50/75 border-b border-slate-100 text-slate-500 font-semibold uppercase tracking-wider text-xs">
                    <tr>
                        <th class="px-6 py-3.5">Nama Jalur</th>
                        <th class="px-6 py-3.5">Slug URL</th>
                        <th class="px-6 py-3.5">Kuota</th>
                        <th class="px-6 py-3.5">Keterangan / Ketentuan Berkas</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tracks as $track)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 font-bold text-slate-900 text-xs">{{ $track->name }}</td>
                            <td class="px-6 py-4 text-xs font-mono text-slate-400">{{ $track->slug }}</td>
                            <td class="px-6 py-4 text-slate-700 font-medium">
                                {{ $track->quota ? number_format($track->quota).' Siswa' : 'Tidak Dibatasi' }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500 max-w-xs truncate">{{ $track->description ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                @if ($track->is_active)
                                    <span class="pill pill-verified text-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Buka
                                    </span>
                                @else
                                    <span class="pill bg-slate-100 text-slate-600 border border-slate-200 text-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Ditutup
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button @click="isEdit = true; form = { id: {{ $track->id }}, name: '{{ addslashes($track->name) }}', quota: '{{ $track->quota ?? '' }}', description: '{{ addslashes($track->description ?? '') }}', is_active: {{ $track->is_active ? 'true' : 'false' }} }; modalOpen = true" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 rounded-md">Edit</button>
                                <form method="POST" action="{{ tenant_route('cms.tracks.toggle', ['track' => $track->id]) }}" class="inline">
                                     @csrf
                                     <button type="submit" class="text-xs font-semibold {{ $track->is_active ? 'text-rose-600 hover:text-rose-800' : 'text-slate-600 hover:text-slate-800' }} focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 rounded-md">
                                         {{ $track->is_active ? 'Tutup Jalur' : 'Buka Jalur' }}
                                     </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 text-xs">
                                <p>Belum ada jalur pendaftaran yang dibuat.</p>
                                <button @click="isEdit = false; form = { id: null, name: '', quota: '', description: '', is_active: true }; modalOpen = true" class="mt-2 text-xs font-bold text-emerald-700 hover:underline">
                                    + Tambah Jalur Masuk
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Tambah/Edit: rounded-2xl max-w-lg -->
    <div x-show="modalOpen" x-cloak @keydown.escape.window="modalOpen = false" role="dialog" aria-modal="true" aria-labelledby="modal-track-title" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50">
        <div @click.away="modalOpen = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-lg w-full p-6 space-y-4">
            <h3 id="modal-track-title" class="text-base font-bold text-slate-900" x-text="isEdit ? 'Edit Jalur Pendaftaran' : 'Tambah Jalur Baru'"></h3>
            <form :action="isEdit ? '{{ tenant_route('cms.tracks.index') }}/' + form.id : '{{ tenant_route('cms.tracks.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                <div>
                    <label for="track_name" class="block text-xs font-semibold text-slate-700">Nama Jalur</label>
                    <input type="text" id="track_name" name="name" x-model="form.name" required placeholder="Contoh: Jalur Prestasi / Reguler" class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden">
                </div>
                <div>
                    <label for="track_quota" class="block text-xs font-semibold text-slate-700">Kuota Siswa (Kosongkan jika tidak dibatasi)</label>
                    <input type="number" id="track_quota" name="quota" x-model="form.quota" min="1" placeholder="Contoh: 100" class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden">
                </div>
                <div>
                    <label for="track_description" class="block text-xs font-semibold text-slate-700">Persyaratan &amp; Ketentuan Berkas</label>
                    <textarea id="track_description" name="description" x-model="form.description" rows="3" placeholder="Jelaskan berkas yang dibutuhkan dan ketentuan penerimaan jalur ini..." class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="track_is_active" name="is_active" value="1" x-model="form.is_active" class="rounded-sm border-slate-300 text-emerald-700 focus:ring-emerald-500">
                    <label for="track_is_active" class="text-xs text-slate-700">Buka pendaftaran untuk jalur ini</label>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" class="btn-secondary text-xs">Batal</button>
                    <button type="submit" class="btn-primary text-xs">Simpan Jalur</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


@extends('layouts.ppdb_cms')

@section('title', 'Detail Pendaftar - '.$applicant->registration_number)

@section('content')
<div class="space-y-6" x-data="{ showRevisionModal: false, revisionNote: '{{ $applicant->verification_notes }}' }">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ tenant_route('cms.applicants.index') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium inline-flex items-center gap-1 mb-1">
                &larr; Kembali ke Daftar
            </a>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900">{{ $applicant->full_name }}</h1>
                <x-applicant_status_badge :status="$applicant->status" />
            </div>
            <p class="text-xs font-mono text-emerald-700 font-bold mt-0.5">{{ $applicant->registration_number }}</p>
        </div>

        <!-- Quick Actions: Slip & WhatsApp -->
        <div class="flex items-center gap-2">
            <a href="{{ $waUrl }}" target="_blank" class="btn-primary text-xs flex items-center gap-1.5 shadow-2xs">
                <span>Hubungi via WA</span>
            </a>
            <a href="{{ tenant_route('applicant.slip.download', ['reg' => $applicant->registration_number]) }}" target="_blank" class="btn-secondary text-xs flex items-center gap-1.5 shadow-2xs">
                <span>Unduh Bukti (PDF)</span>
            </a>
        </div>
    </div>

    <!-- Status Change Workflow Bar as Small Button Row -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-2.5">
        <span class="text-3xs font-bold uppercase tracking-wider text-slate-500 block">Ubah Status Pendaftaran</span>
        <div class="flex flex-wrap items-center gap-2">
            @php
                $statusButtons = [
                    'submitted' => ['label' => 'Terdaftar (Reset)', 'class' => 'bg-slate-100 hover:bg-slate-200 text-slate-700'],
                    'verified' => ['label' => '✓ Berkas Lengkap', 'class' => 'bg-emerald-700 hover:bg-emerald-800 text-white'],
                    'accepted' => ['label' => '✓ Diterima', 'class' => 'bg-purple-600 hover:bg-purple-700 text-white'],
                    'completed' => ['label' => '✓ Selesai Daftar Ulang', 'class' => 'bg-teal-600 hover:bg-teal-700 text-white'],
                    'cancelled' => ['label' => '✕ Batalkan', 'class' => 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200'],
                ];
            @endphp
            @foreach ($statusButtons as $stKey => $stData)
                @if ($stKey !== $applicant->status)
                    <form method="POST" action="{{ tenant_route('cms.applicants.update-status', $applicant) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $stKey }}">
                        <button type="submit" class="px-3 py-1.5 rounded-xl font-bold text-2xs transition shadow-2xs focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 {{ $stData['class'] }}">
                            {{ $stData['label'] }}
                        </button>
                    </form>
                @endif
            @endforeach

            <!-- Need Revision Trigger -->
            @if ($applicant->status !== 'need_revision')
                <button type="button" @click="showRevisionModal = true" class="px-3 py-1.5 rounded-xl font-bold text-2xs bg-amber-600 hover:bg-amber-700 text-white transition shadow-2xs focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600">
                    ⚠ Minta Perbaikan Berkas
                </button>
            @endif
        </div>
    </div>

    <!-- 2-Column Grid in White Cards (Biodata | Dokumen) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Kolom 1: Biodata Card -->
        <div class="lg:col-span-5 bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
            <h2 class="font-bold text-sm text-slate-900 border-b border-slate-100 pb-3">Informasi Calon Siswa</h2>
            <div class="space-y-3 text-xs">
                <div><span class="text-3xs text-slate-400 block">Jalur &amp; Gelombang</span><strong class="text-slate-800">{{ $applicant->track?->name ?? 'Reguler' }}</strong> ({{ $applicant->wave?->name ?? '-' }})</div>
                <div><span class="text-3xs text-slate-400 block">NIK</span><span class="font-mono text-slate-800 font-bold">{{ $applicant->nik }}</span></div>
                <div><span class="text-3xs text-slate-400 block">Tempat, Tanggal Lahir</span><span class="text-slate-800">{{ $applicant->birth_place ?? '-' }}, {{ $applicant->birth_date?->format('d/m/Y') }}</span></div>
                <div><span class="text-3xs text-slate-400 block">Jenis Kelamin</span><span class="text-slate-800">{{ $applicant->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span></div>
                <div><span class="text-3xs text-slate-400 block">No. WhatsApp</span><span class="font-mono text-slate-800 font-bold">{{ $applicant->phone_whatsapp }}</span></div>
                <div><span class="text-3xs text-slate-400 block">Tipe Entri</span><span class="capitalize text-slate-800">{{ $applicant->registration_origin === 'offline_walk_in' ? 'Offline (Loket)' : 'Online' }}</span></div>
            </div>

            @if ($applicant->custom_fields && count($applicant->custom_fields) > 0)
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <h3 class="font-bold text-xs text-slate-800">Data Tambahan</h3>
                    @foreach ($applicant->custom_fields as $fieldKey => $fieldVal)
                        <div class="text-xs">
                            <span class="text-3xs text-slate-400 block">{{ $fieldLabels[$fieldKey] ?? ucwords(str_replace('_', ' ', $fieldKey)) }}</span>
                            <span class="text-slate-800 font-medium">{{ is_array($fieldVal) ? implode(', ', $fieldVal) : ($fieldVal ?: '-') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Kolom 2: Documents Card -->
        <div class="lg:col-span-7 bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="font-bold text-sm text-slate-900">Pemeriksaan Berkas Persyaratan</h2>
                <span class="pill bg-slate-100 text-slate-700 text-2xs">{{ $applicant->documents->count() }} Dokumen Terlampir</span>
            </div>

            <div class="space-y-3">
                @forelse ($applicant->documents as $doc)
                    <x-applicant_doc_row :doc="$doc" />
                @empty
                    <div class="p-8 text-center text-xs text-slate-500">Tidak ada lampiran dokumen digital yang diunggah.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Need Revision Modal -->
    <div x-show="showRevisionModal" x-cloak @keydown.escape.window="showRevisionModal = false" role="dialog" aria-modal="true" aria-labelledby="modal-revision-title" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-200" @click.away="showRevisionModal = false">
            <h3 id="modal-revision-title" class="text-sm font-bold text-slate-900">Minta Perbaikan Berkas</h3>
            <p class="text-xs text-slate-500">Tuliskan instruksi atau dokumen apa saja yang perlu diunggah ulang oleh pendaftar.</p>
            <form method="POST" action="{{ tenant_route('cms.applicants.update-status', $applicant) }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="need_revision">
                <label for="verification_notes" class="sr-only">Instruksi perbaikan berkas</label>
                <textarea id="verification_notes" name="verification_notes" x-model="revisionNote" rows="3" required placeholder="Contoh: Foto Kartu Keluarga terpotong, silakan unggah ulang."
                    class="w-full p-3 rounded-xl border border-slate-300 text-xs focus:border-amber-600 focus:ring-2 focus:ring-amber-500/20 focus:outline-hidden"></textarea>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" @click="showRevisionModal = false" class="btn-secondary text-xs">Batal</button>
                    <button type="submit" class="btn-primary text-xs bg-amber-700 hover:bg-amber-800">Simpan &amp; Minta Perbaikan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


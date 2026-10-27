@extends('layouts.ppdb_public')

@section('title', 'Status Pendaftaran - '.$applicant->registration_number)

@section('content')
<div class="max-w-2xl mx-auto py-6 sm:py-10 px-4 sm:px-6 space-y-6">
    <!-- Status Detail Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6">
        <!-- 1. Header: Nama + Nomor + Pill Status -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
            <div class="min-w-0">
                <span class="text-xs uppercase font-bold tracking-wider text-slate-400">Data Pendaftaran</span>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 mt-0.5 break-words">{{ $applicant->full_name }}</h1>
                <p class="text-xs font-mono text-emerald-700 font-bold break-all mt-0.5">{{ $applicant->registration_number }}</p>
            </div>
            <div class="shrink-0">
                @php
                    $statusPills = [
                        'submitted' => 'pill-submitted',
                        'need_revision' => 'pill-need-revision',
                        'verified' => 'pill-verified',
                        'accepted' => 'pill-accepted',
                        'completed' => 'pill-completed',
                        'cancelled' => 'pill-cancelled',
                    ];
                @endphp
                <span class="pill {{ $statusPills[$applicant->status] ?? 'bg-slate-100 text-slate-700' }}">
                    {{ $applicant->status_label }}
                </span>
            </div>
        </div>

        <!-- 2. Alert Revisi amber-50 (Hanya Bila need_revision) -->
        @if ($applicant->status === 'need_revision')
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 space-y-2">
                <div class="flex items-center gap-2 text-amber-900 font-bold text-xs">
                    <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Catatan Perbaikan dari Panitia:</span>
                </div>
                <p class="text-xs text-amber-950 leading-relaxed pl-6">
                    {{ $applicant->verification_notes ?: 'Terdapat berkas yang kurang jelas atau tidak sesuai. Silakan unggah ulang berkas yang diminta.' }}
                </p>
                <div class="pt-2 pl-6">
                    <a href="{{ tenant_route('applicant.status.reupload', ['registration_number' => $applicant->registration_number]) }}"
                       class="min-h-[44px] inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white text-xs font-bold transition shadow-xs focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-700">
                        <span>Perbaiki Berkas Sekarang</span>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- 3. Timeline 4 Tahap Horizontal -->
        <div class="space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Tahapan Seleksi</h2>
            <div class="grid grid-cols-4 gap-2 text-center text-2xs">
                @php
                    $steps = ['submitted' => 'Terdaftar', 'verified' => 'Berkas Lengkap', 'accepted' => 'Diterima', 'completed' => 'Selesai'];
                    $currentIndex = array_search($applicant->status, array_keys($steps));
                    if ($applicant->status === 'need_revision') $currentIndex = 0;
                    if ($applicant->status === 'cancelled') $currentIndex = -1;
                @endphp
                @foreach (array_values($steps) as $idx => $label)
                    <div class="space-y-1.5">
                        <div class="h-2 rounded-full transition-all duration-300 {{ $currentIndex !== false && $idx <= $currentIndex ? 'bg-emerald-600' : 'bg-slate-200' }}"></div>
                        <span class="block text-2xs {{ $currentIndex !== false && $idx <= $currentIndex ? 'font-bold text-emerald-800' : 'text-slate-500' }}">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Ringkasan Singkat -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                <span class="text-slate-500 block text-3xs">Jalur Pendaftaran</span>
                <strong class="text-slate-800">{{ $applicant->track?->name ?? 'Jalur Reguler' }}</strong>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                <span class="text-slate-500 block text-3xs">Gelombang Pendaftaran</span>
                <strong class="text-slate-800">{{ $applicant->wave?->name ?? 'Gelombang Reguler' }}</strong>
            </div>
        </div>

        <!-- 4. Daftar Berkas Sebagai Baris dengan Badge Status Kecil -->
        <div class="space-y-3 pt-1">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Berkas Persyaratan</h2>
            <div class="divide-y divide-slate-100 border border-slate-200 rounded-2xl overflow-hidden">
                @forelse ($applicant->documents as $doc)
                    <div class="p-3.5 flex items-center justify-between gap-3 text-xs bg-white">
                        <div class="min-w-0">
                            <span class="font-bold text-slate-800 block capitalize">{{ str_replace('_', ' ', $doc->document_type) }}</span>
                            <span class="text-3xs text-slate-500 truncate max-w-[200px] sm:max-w-xs block">{{ $doc->is_physical_submitted ? 'Berkas fisik diterima panitia' : ($doc->original_file_name ?: 'Ditunjukkan fisik langsung ke sekolah') }}</span>
                            @if ($doc->rejection_reason)
                                <span class="text-2xs text-amber-700 font-medium block mt-0.5">Alasan: {{ $doc->rejection_reason }}</span>
                            @endif
                        </div>
                        <div class="shrink-0">
                            @if ($doc->verification_status === 'valid')
                                <span class="pill pill-verified text-2xs">Lengkap</span>
                            @elseif ($doc->verification_status === 'invalid')
                                <span class="pill pill-need-revision text-2xs">Perlu Perbaikan</span>
                            @else
                                <span class="pill pill-submitted text-2xs">Menunggu Verifikasi</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-xs text-slate-500">Tidak ada lampiran berkas yang diunggah.</div>
                @endforelse
            </div>
        </div>

        <!-- Aksi Slip Download & Kembali -->
        <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <a href="{{ tenant_route('applicant.status.lookup') }}" class="min-h-[44px] inline-flex items-center justify-center sm:justify-start text-xs text-slate-500 hover:text-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                &larr; Cek Nomor Lain
            </a>
            <a href="{{ tenant_route('applicant.slip.download', ['reg' => $applicant->registration_number, 'dob' => $applicant->birth_date]) }}" download
               class="btn-primary w-full sm:w-auto text-xs text-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Unduh Ulang Bukti Pendaftaran (PDF)</span>
            </a>
        </div>
    </div>
</div>
@endsection


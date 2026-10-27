@extends('layouts.ppdb_public')

@section('title', 'Perbaikan Berkas - '.$applicant->registration_number)

@section('content')
<div class="max-w-2xl mx-auto py-6 sm:py-10 px-4 sm:px-6 space-y-6">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <span class="pill pill-need-revision">Perbaikan Berkas Pendaftaran</span>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 mt-2">{{ $applicant->full_name }}</h1>
            <p class="text-xs font-mono text-emerald-700 font-bold mt-0.5">{{ $applicant->registration_number }}</p>
        </div>

        @if ($applicant->verification_notes)
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-950 space-y-1">
                <strong class="font-bold block text-amber-900">Catatan Panitia:</strong>
                <p class="leading-relaxed">{{ $applicant->verification_notes }}</p>
            </div>
        @endif

        <form action="{{ tenant_route('applicant.status.submit-revision', ['registration_number' => $applicant->registration_number]) }}"
              method="POST" enctype="multipart/form-data" class="space-y-5" x-data="{ uploading: false }" @submit="uploading = true">
            @csrf

            <div class="space-y-4">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Unggah Berkas Pengganti</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Format file: JPG, PNG, atau PDF (Maks. 5MB per berkas). Bingkai kuning menandakan berkas yang perlu diperbaiki.
                    </p>
                </div>

                <div class="space-y-3">
                    @forelse ($applicant->documents as $doc)
                        <div class="p-4 rounded-2xl {{ $doc->verification_status === 'invalid' ? 'border-2 border-amber-400 bg-amber-50/40 ring-1 ring-amber-300' : 'border border-slate-200 bg-slate-50/50' }} space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-slate-800 capitalize">{{ str_replace('_', ' ', $doc->document_type) }}</span>
                                @if ($doc->verification_status === 'invalid')
                                    <span class="pill pill-need-revision text-2xs">Perlu Perbaikan</span>
                                @else
                                    <span class="pill bg-slate-200 text-slate-700 text-2xs">Berkas Lama</span>
                                @endif
                            </div>

                            @if ($doc->rejection_reason)
                                <p class="text-2xs text-amber-800 font-medium">Alasan: {{ $doc->rejection_reason }}</p>
                            @endif

                            <div class="pt-1">
                                <input type="file" name="documents[{{ $doc->document_type }}]" accept=".jpg,.jpeg,.png,.pdf"
                                    class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                            </div>
                        </div>
                    @empty
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 space-y-2">
                            <label class="font-bold text-xs text-slate-800 block">Lampiran Dokumen</label>
                            <input type="file" name="documents[berkas_pendukung]" accept=".jpg,.jpeg,.png,.pdf"
                                class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <a href="{{ tenant_route('applicant.status.show', ['registration_number' => $applicant->registration_number]) }}"
                   class="btn-secondary w-full sm:w-auto text-xs text-center">
                    Batal
                </a>
                <button type="submit" :disabled="uploading"
                    class="btn-primary w-full sm:w-auto text-xs text-center disabled:opacity-50">
                    <span x-show="!uploading">Kirim Berkas Perbaikan</span>
                    <span x-show="uploading" class="flex items-center justify-center gap-2" style="display: none;">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Mengirim Berkas...</span>
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


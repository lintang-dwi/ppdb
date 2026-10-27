@props(['doc'])

<div class="p-4 rounded-xl border {{ $doc->verification_status === 'invalid' ? 'border-amber-200 bg-amber-50/40' : ($doc->verification_status === 'valid' ? 'border-emerald-200 bg-emerald-50/30' : 'border-slate-200 bg-white') }} space-y-3"
     x-data="{ showReject: false, reason: '{{ $doc->rejection_reason }}' }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
        <div>
            <div class="flex items-center gap-2">
                <span class="font-bold text-xs text-slate-800 capitalize">{{ str_replace('_', ' ', $doc->document_type) }}</span>
                @if ($doc->verification_status === 'valid')
                    <span class="pill pill-verified text-2xs">Valid</span>
                @elseif ($doc->verification_status === 'invalid')
                    <span class="pill pill-need-revision text-2xs">Perlu Perbaikan</span>
                @else
                    <span class="pill bg-slate-100 text-slate-600 text-2xs">Belum Diverifikasi</span>
                @endif
            </div>
            <div class="flex items-center gap-3 text-2xs text-slate-500 mt-1">
                <span class="truncate max-w-[160px] sm:max-w-xs">{{ $doc->original_file_name ?: ($doc->is_physical_submitted ? 'Fisik diserahkan' : 'Menunggu penyerahan fisik') }}</span>
                @if ($doc->drive_view_url)
                    <a href="{{ $doc->drive_view_url }}" target="_blank" class="text-emerald-700 hover:text-emerald-800 font-semibold flex items-center gap-1 shrink-0">
                        <span>Lihat di Drive</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Physical doc toggle -->
            <form method="POST" action="{{ tenant_route('cms.documents.physical', $doc) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="px-2.5 py-1.5 rounded-lg text-2xs font-semibold border transition {{ $doc->is_physical_submitted ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                    {{ $doc->is_physical_submitted ? '✓ Fisik Diterima' : '+ Terima Fisik' }}
                </button>
            </form>

            <!-- Mark Valid -->
            <form method="POST" action="{{ tenant_route('cms.documents.verify', $doc) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="verification_status" value="valid">
                <button type="submit" class="px-3 py-1.5 rounded-lg text-2xs font-semibold bg-emerald-700 hover:bg-emerald-800 text-white transition shadow-xs">
                    Setujui
                </button>
            </form>

            <!-- Reject button -->
            <button @click="showReject = !showReject" type="button" class="px-2.5 py-1.5 rounded-lg text-2xs font-semibold bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 transition">
                Minta Perbaikan
            </button>
        </div>
    </div>

    <!-- Reject notes form -->
    <div x-show="showReject" x-cloak class="pt-2.5 border-t border-amber-200">
        <form method="POST" action="{{ tenant_route('cms.documents.verify', $doc) }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            @csrf
            @method('PATCH')
            <input type="hidden" name="verification_status" value="invalid">
            <input type="hidden" name="flag_revision" value="1">
            <input type="text" name="rejection_reason" x-model="reason" placeholder="Alasan penolakan / instruksi perbaikan..." required
                class="flex-1 px-3 py-1.5 rounded-xl border border-amber-300 text-xs bg-white focus:outline-hidden focus:ring-1 focus:ring-amber-500">
            <button type="submit" class="min-h-[36px] sm:min-h-0 px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs flex items-center justify-center transition">
                Kirim Catatan
            </button>
        </form>
    </div>
</div>

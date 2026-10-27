@props(['currentTier' => null, 'currentWaveId' => null])

<!-- Modal Template Jenjang -->
<div x-show="presetModal" x-cloak @keydown.escape.window="presetModal = false" role="dialog" aria-modal="true" aria-labelledby="preset-modal-title" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50">
    <div @click.away="presetModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-4">
        <div>
            <h3 id="preset-modal-title" class="text-base font-bold text-slate-900">Terapkan Template Jenjang</h3>
            <p class="text-xs text-slate-500 mt-0.5">Pilih jenjang sekolah untuk menerapkan field formulir standar secara otomatis.</p>
        </div>
        <form action="{{ tenant_route('cms.form-builder.apply-preset') }}" method="POST" class="space-y-3">
            @csrf
            @if ($currentWaveId)
                <input type="hidden" name="wave_id" value="{{ $currentWaveId }}">
            @endif

            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                <label class="flex items-center gap-3 p-2.5 rounded-xl border {{ $currentTier === 'paud_tk' ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-200' }} hover:bg-slate-50 cursor-pointer has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-emerald-600">
                    <input type="radio" name="tier" value="paud_tk" {{ $currentTier === 'paud_tk' ? 'checked' : '' }} class="text-emerald-700 focus:ring-emerald-600">
                    <div><span class="block text-xs font-bold text-slate-800">PAUD / TK</span><span class="text-2xs text-slate-500">Nama panggilan, anak ke-, berat/tinggi badan, seragam</span></div>
                </label>
                <label class="flex items-center gap-3 p-2.5 rounded-xl border {{ $currentTier === 'sd_mi' ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-200' }} hover:bg-slate-50 cursor-pointer has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-emerald-600">
                    <input type="radio" name="tier" value="sd_mi" {{ $currentTier === 'sd_mi' ? 'checked' : '' }} class="text-emerald-700 focus:ring-emerald-600">
                    <div><span class="block text-xs font-bold text-slate-800">SD / Madrasah Ibtidaiyah</span><span class="text-2xs text-slate-500">No KK, jarak ke sekolah, transportasi, data ortu</span></div>
                </label>
                <label class="flex items-center gap-3 p-2.5 rounded-xl border {{ $currentTier === 'smp_mts' ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-200' }} hover:bg-slate-50 cursor-pointer has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-emerald-600">
                    <input type="radio" name="tier" value="smp_mts" {{ $currentTier === 'smp_mts' ? 'checked' : '' }} class="text-emerald-700 focus:ring-emerald-600">
                    <div><span class="block text-xs font-bold text-slate-800">SMP / MTs</span><span class="text-2xs text-slate-500">NISN, NPSN sekolah asal, nama sekolah asal, nilai rapor</span></div>
                </label>
                <label class="flex items-center gap-3 p-2.5 rounded-xl border {{ ($currentTier === 'sma_smk' || (!$currentTier && $currentTier !== 'smk' && $currentTier !== 'pesantren')) ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-200' }} hover:bg-slate-50 cursor-pointer has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-emerald-600">
                    <input type="radio" name="tier" value="sma_smk" {{ ($currentTier === 'sma_smk' || (!$currentTier && $currentTier !== 'smk' && $currentTier !== 'pesantren')) ? 'checked' : '' }} class="text-emerald-700 focus:ring-emerald-600">
                    <div><span class="block text-xs font-bold text-slate-800">SMA / MA</span><span class="text-2xs text-slate-500">NISN, pilihan peminatan IPA/IPS/Bahasa, nilai rapor</span></div>
                </label>
                <label class="flex items-center gap-3 p-2.5 rounded-xl border {{ $currentTier === 'smk' ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-200' }} hover:bg-slate-50 cursor-pointer has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-emerald-600">
                    <input type="radio" name="tier" value="smk" {{ $currentTier === 'smk' ? 'checked' : '' }} class="text-emerald-700 focus:ring-emerald-600">
                    <div><span class="block text-xs font-bold text-slate-800">SMK (Kejuruan)</span><span class="text-2xs text-slate-500">Pilihan kompetensi keahlian 1 &amp; 2, tes buta warna</span></div>
                </label>
                <label class="flex items-center gap-3 p-2.5 rounded-xl border {{ $currentTier === 'pesantren' ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-200' }} hover:bg-slate-50 cursor-pointer has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-emerald-600">
                    <input type="radio" name="tier" value="pesantren" {{ $currentTier === 'pesantren' ? 'checked' : '' }} class="text-emerald-700 focus:ring-emerald-600">
                    <div><span class="block text-xs font-bold text-slate-800">Pesantren / Boarding</span><span class="text-2xs text-slate-500">Program tahfidz/kitab, asrama, riwayat mengaji</span></div>
                </label>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-700">
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="checkbox" name="replace_existing" value="1" checked class="mt-0.5 rounded-sm border-slate-300 text-emerald-700 focus:ring-emerald-600">
                    <div>
                        <span class="font-semibold text-slate-900">Ganti seluruh field template jenjang sebelumnya</span>
                        <p class="text-2xs text-slate-500 mt-0.5">Menghapus field bawaan jenjang lain agar formulir bersih. Field kustom panitia tetap aman.</p>
                    </div>
                </label>
            </div>

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" @click="presetModal = false" class="btn-secondary text-xs w-full sm:w-auto">Batal</button>
                <button type="submit" class="btn-primary text-xs w-full sm:w-auto">Terapkan Sekarang</button>
            </div>
        </form>
    </div>
</div>

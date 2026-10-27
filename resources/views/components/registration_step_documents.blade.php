@props(['fields'])

@php
    $docFields = $fields->get('berkas', collect())->merge($fields->flatten()->where('field_type', 'file'))->unique('field_key');
@endphp

<!-- STEP 4: Unggah Dokumen & Berkas Persyaratan -->
<div x-show="step === 4" id="step-documents" class="space-y-4">
    <div class="border-b border-slate-100 pb-3">
        <h2 class="text-base font-bold text-slate-900">Unggah Berkas Persyaratan</h2>
        <p class="text-xs text-slate-500">Bisa difoto langsung dari ponsel Anda. Berkas otomatis diperkecil.</p>
    </div>

    <!-- Opsi Tunjukkan Berkas Fisik Langsung (Fleksibel / Hybrid) -->
    <div class="p-4 rounded-2xl border border-emerald-200 bg-emerald-50/70 transition">
        <label class="flex items-start gap-3 cursor-pointer">
            <input type="checkbox"
                   name="is_physical_submitted"
                   value="1"
                   x-model="formData.is_physical_submitted"
                   @change="togglePhysicalOption()"
                   class="mt-0.5 size-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <div>
                <span class="block text-xs font-bold text-emerald-950">
                    Tunjukkan berkas fisik langsung ke sekolah (tanpa unggah file)
                </span>
                <span class="block text-3xs text-emerald-800/80 mt-0.5 leading-relaxed">
                    Centang opsi ini jika Anda ingin menyerahkan fotokopi / dokumen fisik langsung ke panitia saat verifikasi. Berkas di bawah ini menjadi opsional.
                </span>
            </div>
        </label>
    </div>

    <div :class="formData.is_physical_submitted ? 'opacity-85' : ''" class="space-y-4">
        @forelse ($docFields as $docField)
            <x-dynamic_field :field="$docField" />
        @empty
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-500">
                Tidak ada berkas unggahan wajib saat ini. Anda dapat langsung mengirim pendaftaran.
            </div>
        @endforelse
    </div>
</div>

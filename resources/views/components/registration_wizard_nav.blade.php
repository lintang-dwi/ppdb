<div class="flex items-center justify-between gap-3 pt-6 border-t border-slate-100 mt-6">
    <button type="button" x-show="step > 1" @click="prevStep()" class="btn-secondary text-xs">
        &larr; Kembali
    </button>
    <div x-show="step === 1"></div>
    <button type="button" x-show="step < 4" @click="nextStep()" class="btn-primary text-xs">
        <span>Lanjut</span>
        <span aria-hidden="true">&rarr;</span>
    </button>
    <button type="submit" x-show="step === 4" :disabled="isSubmitting" class="btn-primary text-xs disabled:opacity-50 disabled:cursor-not-allowed">
        <svg x-show="isSubmitting" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
        <span x-text="isSubmitting ? 'Mengirim Formulir...' : 'Kirim Pendaftaran'"></span>
    </button>
</div>


@extends('layouts.ppdb_public')

@section('title', 'Formulir Pendaftaran Siswa Baru - '.$school->name)

@section('content')
<div class="max-w-2xl mx-auto py-6 sm:py-10 px-4 sm:px-6" x-data="registrationWizard()" x-init="initWizard()">
    <x-registration_stepper />

    <!-- Main Registration Form Container -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <form id="regForm" action="{{ tenant_route('applicant.register.store') }}" method="POST" enctype="multipart/form-data" @submit="saveDraft(); isSubmitting = true">
            @csrf
            <input type="hidden" name="wave_id" value="{{ $wave->id }}">

            <!-- STEP 1: Pilih Jalur -->
            <div x-show="step === 1" class="space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900">Pilih Jalur Pendaftaran</h2>
                    <p class="text-xs text-slate-500">Gelombang: <strong class="text-emerald-700">{{ $wave->name }}</strong> (TA {{ $wave->academic_year }})</p>
                </div>
                <div class="space-y-3">
                    @foreach ($tracks as $track)
                        <label :class="formData.track_id == '{{ $track->id }}' ? 'border-emerald-600 bg-emerald-50/40 ring-1 ring-emerald-600' : 'border-slate-200 bg-white'"
                               class="flex items-start gap-3 p-4 rounded-2xl border hover:border-emerald-300 hover:bg-emerald-50/20 cursor-pointer transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-emerald-500">
                            <input type="radio" name="track_id" value="{{ $track->id }}" x-model="formData.track_id" required class="mt-1 text-emerald-700 focus:ring-emerald-500">
                            <div>
                                <span class="block text-sm font-bold text-slate-900">{{ $track->name }}</span>
                                <span class="text-xs text-slate-500">{{ $track->description ?: 'Pendaftaran reguler sekolah.' }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- STEP 2: Data Pribadi & Kontak -->
            <div x-show="step === 2" class="space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900">Identitas Calon Siswa &amp; Kontak</h2>
                    <p class="text-xs text-slate-500">Isi data pokok siswa sesuai dengan Kartu Keluarga (KK).</p>
                </div>
                <div>
                    <label for="full_name" class="block text-xs font-semibold text-slate-700">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                    <input type="text" id="full_name" name="full_name" x-model="formData.full_name" required placeholder="Sesuai Akta Kelahiran / KK" class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="nik" class="block text-xs font-semibold text-slate-700">NIK (16 Digit) <span class="text-rose-500">*</span></label>
                        <input type="text" id="nik" name="nik" x-model="formData.nik" maxlength="16" required placeholder="3201xxxxxxxxxxxx" class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm font-mono focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden">
                    </div>
                    <div>
                        <label for="gender" class="block text-xs font-semibold text-slate-700">Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <select id="gender" name="gender" x-model="formData.gender" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden bg-white">
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="birth_place" class="block text-xs font-semibold text-slate-700">Tempat Lahir</label>
                        <input type="text" id="birth_place" name="birth_place" x-model="formData.birth_place" placeholder="Kota / Kabupaten" class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden">
                    </div>
                    <div>
                        <label for="birth_date" class="block text-xs font-semibold text-slate-700">Tanggal Lahir <span class="text-rose-500">*</span></label>
                        <input type="date" id="birth_date" name="birth_date" x-model="formData.birth_date" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden bg-white">
                    </div>
                </div>
                <div>
                    <label for="phone_whatsapp" class="block text-xs font-semibold text-slate-700">Nomor WhatsApp Aktif <span class="text-rose-500">*</span></label>
                    <input type="tel" id="phone_whatsapp" name="phone_whatsapp" x-model="formData.phone_whatsapp" required placeholder="Contoh: 081234567890" class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden">
                    <span class="text-3xs text-slate-500 mt-1 block">Nomor ini digunakan untuk pengiriman bukti slip dan informasi kelulusan.</span>
                </div>
            </div>

            <!-- STEP 3: Informasi Tambahan / Isian Khusus Dinamis -->
            <div x-show="step === 3" class="space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900">Informasi Tambahan &amp; Asal Sekolah</h2>
                    <p class="text-xs text-slate-500">Lengkapi data pendukung yang diminta oleh pihak sekolah.</p>
                </div>
                @php $step3Fields = $fields->filter(fn($grp, $key) => in_array($key, ['orang_tua', 'asal_sekolah', 'tambahan', 'biodata']))->flatten()->unique('field_key'); @endphp
                @forelse ($step3Fields->where('field_type', '!=', 'file') as $dynField)
                    <x-dynamic_field :field="$dynField" />
                @empty
                    <p class="text-xs text-slate-500 italic py-4 text-center">Tidak ada isian tambahan khusus yang wajib diisi.</p>
                @endforelse
            </div>

            <x-registration_step_documents :fields="$fields" />

            <x-registration_wizard_nav />
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function registrationWizard() {
    return {
        step: 1,
        isSubmitting: false,
        formData: { track_id: '', full_name: '', nik: '', gender: '', birth_place: '', birth_date: '', phone_whatsapp: '', is_physical_submitted: false },
        initWizard() {
            const urlTrack = new URLSearchParams(window.location.search).get('track');
            if (urlTrack) this.formData.track_id = urlTrack;
            const saved = localStorage.getItem('ppdb_draft_{{ $school->school_code }}');
            if (saved) {
                try {
                    Object.assign(this.formData, JSON.parse(saved));
                    if (urlTrack) this.formData.track_id = urlTrack;
                } catch(e){}
            }
            this.$nextTick(() => this.togglePhysicalOption());
        },
        togglePhysicalOption() {
            const isPhysical = Boolean(this.formData.is_physical_submitted);
            document.querySelectorAll('#step-documents input[type="file"]').forEach(el => {
                if (isPhysical) {
                    el.removeAttribute('required');
                } else if (el.dataset.originallyRequired === 'true') {
                    el.setAttribute('required', 'required');
                }
            });
        },
        saveDraft() { localStorage.setItem('ppdb_draft_{{ $school->school_code }}', JSON.stringify(this.formData)); },
        nextStep() {
            if (this.step === 1 && !this.formData.track_id) { alert('Silakan pilih salah satu jalur pendaftaran.'); return; }
            if (this.step === 2 && (!this.formData.full_name || !this.formData.nik || !this.formData.birth_date || !this.formData.phone_whatsapp)) {
                alert('Mohon lengkapi Nama, NIK, Tanggal Lahir, dan No WhatsApp.'); return;
            }
            this.saveDraft();
            this.step++;
            if (this.step === 4) this.$nextTick(() => this.togglePhysicalOption());
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        prevStep() { this.step--; window.scrollTo({ top: 0, behavior: 'smooth' }); }
    };
}
</script>
@endpush


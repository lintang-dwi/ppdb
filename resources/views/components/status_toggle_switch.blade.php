@props(['applicant'])

<div x-data="{
    status: '{{ $applicant->status }}',
    saving: false,
    updateStatus(newStatus) {
        if (newStatus === this.status) return;
        this.saving = true;
        fetch('{{ tenant_route('cms.applicants.update-status', $applicant) }}', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.status = data.status;
            }
        })
        .catch(() => alert('Gagal memperbarui status.'))
        .finally(() => this.saving = false);
    }
}" class="relative inline-flex items-center">
    <select x-model="status" @change="updateStatus($event.target.value)" :disabled="saving"
        aria-label="Ubah status pendaftaran {{ $applicant->full_name }}"
        :class="{
            'bg-blue-50 text-blue-800 border-blue-200': status === 'submitted',
            'bg-amber-50 text-amber-900 border-amber-200': status === 'need_revision',
            'bg-emerald-50 text-emerald-800 border-emerald-200': status === 'verified',
            'bg-purple-50 text-purple-800 border-purple-200': status === 'accepted',
            'bg-teal-50 text-teal-800 border-teal-200': status === 'completed',
            'bg-rose-50 text-rose-800 border-rose-200': status === 'cancelled',
            'opacity-50 pointer-events-none': saving
        }"
        class="py-1 px-2.5 rounded-full text-2xs font-bold border transition cursor-pointer focus:outline-hidden focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 pr-6">
        <option value="submitted">Terdaftar</option>
        <option value="verified">Berkas Lengkap</option>
        <option value="accepted">Diterima</option>
        <option value="completed">Selesai Daftar Ulang</option>
        <option value="need_revision">Perlu Perbaikan</option>
        <option value="cancelled">Dibatalkan</option>
    </select>
    <div x-show="saving" x-cloak class="absolute right-2 pointer-events-none">
        <svg class="animate-spin h-3 w-3 text-slate-500" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
    </div>
</div>

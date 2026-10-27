<!-- Modal Edit Domain CNAME Sekolah -->
<div x-show="editModalOpen" x-cloak @keydown.escape.window="editModalOpen = false" role="dialog" aria-modal="true" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs">
    <div @click.away="editModalOpen = false" class="bg-slate-900 rounded-2xl border border-slate-800 shadow-2xl max-w-md w-full p-6 space-y-4">
        <div>
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                <span>Pengaturan Domain CNAME</span>
            </h3>
            <p class="text-xs text-slate-400 mt-1" x-text="`Kelola CNAME untuk ${selectedSchool.name}`"></p>
        </div>

        <form :action="updateUrl" method="POST" class="space-y-3.5">
            @csrf
            @method('PUT')

            <div>
                <label for="school_name" class="block text-xs font-semibold text-slate-300">Nama Resmi Sekolah</label>
                <input type="text" id="school_name" name="name" x-model="selectedSchool.name" required
                    class="mt-1 w-full rounded-xl bg-slate-950 border border-slate-700 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label for="school_logo" class="block text-xs font-semibold text-slate-300">URL Logo Sekolah (Header &amp; Kop Surat)</label>
                <input type="text" id="school_logo" name="logo" x-model="selectedSchool.logo" placeholder="https://namasekolah.sch.id/logo.png"
                    class="mt-1 w-full rounded-xl bg-slate-950 border border-slate-700 px-3.5 py-2 text-xs text-white placeholder-slate-500 font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label for="school_favicon" class="block text-xs font-semibold text-slate-300">URL Favicon Tab Browser (Opsional)</label>
                <input type="text" id="school_favicon" name="favicon" x-model="selectedSchool.favicon" placeholder="Icon persegi 1:1 (Kosongkan = otomatis ikon inisial)"
                    class="mt-1 w-full rounded-xl bg-slate-950 border border-slate-700 px-3.5 py-2 text-xs text-white placeholder-slate-500 font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                <p class="text-3xs text-slate-400 mt-0.5">Gunakan gambar persegi 1:1 (32x32) agar tidak gepeng. Jika kosong, sistem membuatkan ikon inisial otomatis.</p>
            </div>

            <div>
                <label for="custom_domain" class="block text-xs font-semibold text-slate-300">Custom Domain / Subdomain CNAME</label>
                <input type="text" id="custom_domain" name="custom_domain" x-model="selectedSchool.custom_domain" placeholder="Contoh: ppdb.sman1jkt.sch.id"
                    class="mt-1 w-full rounded-xl bg-slate-950 border border-slate-700 px-3.5 py-2 text-xs text-white placeholder-slate-500 font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800 text-xs">
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input type="checkbox" name="custom_domain_verified" value="1" x-model="selectedSchool.custom_domain_verified" class="mt-0.5 rounded-sm bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <span class="font-semibold text-white text-xs block">Verifikasi Domain Ini</span>
                        <p class="text-3xs text-slate-400 mt-0.5">Bila dicentang, portal PPDB sekolah langsung aktif pada domain CNAME tersebut.</p>
                    </div>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition shadow-xs">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function superadminDashboard() {
    return {
        editModalOpen: false,
        selectedSchool: { code: '', name: '', logo: '', favicon: '', custom_domain: '', custom_domain_verified: false },
        updateUrl: '',
        openEditModal(s) {
            this.selectedSchool = { ...s };
            this.updateUrl = `{{ url('/superadmin/schools') }}/${s.code}`;
            this.editModalOpen = true;
        }
    };
}
</script>

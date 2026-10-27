<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syarat & Ketentuan (Terms of Service) - PPDB Multi-Tenant</title>
    <link rel="icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 font-sans p-4 sm:p-8 md:p-12 min-h-screen flex flex-col justify-between">
    <div class="max-w-4xl mx-auto w-full bg-white p-6 sm:p-10 md:p-12 rounded-2xl border border-slate-200 shadow-2xs space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b border-slate-100 pb-6 gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-700 flex items-center justify-center text-white font-bold text-base shadow-xs shrink-0">
                    P
                </div>
                <div>
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider block">Dokumen Resmi</span>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Syarat & Ketentuan (Terms of Service)</h1>
                </div>
            </div>
            <a href="/" class="btn-secondary text-xs">
                <span>&larr; Kembali ke Beranda</span>
            </a>
        </div>

        <!-- Body Content -->
        <div class="space-y-6 text-xs sm:text-sm text-slate-700 leading-relaxed">
            <p class="font-semibold text-slate-900">
                Selamat datang di platform <strong>PPDB Multi-Tenant Monolith</strong> (<code>https://ppdb.bitserver.cloud</code>). Dengan mengakses portal pendaftaran, menggunakan panel operator sekolah, atau mendaftar sebagai peserta didik, Anda menyetujui ketentuan berikut:
            </p>

            <section class="space-y-2">
                <h2 class="text-sm sm:text-base font-bold text-slate-900 border-l-4 border-emerald-600 pl-3">1. Hak & Akses Operator Sekolah</h2>
                <p>
                    Panitia sekolah diberikan kewenangan penuh untuk mengonfigurasi kuota gelombang, jalur seleksi, preset formulir, verifikasi dokumen fisik/digital, serta menerbitkan hasil seleksi pendaftar sesuai regulasi pendidikan yang berlaku.
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="text-sm sm:text-base font-bold text-slate-900 border-l-4 border-emerald-600 pl-3">2. Kewajiban Calon Peserta Didik & Orang Tua</h2>
                <p>
                    Calon siswa atau wali wajib memberikan data yang jujur, valid, dan dapat dipertanggungjawabkan. Pemalsuan dokumen persyaratan (Kartu Keluarga, Akta Kelahiran, SKL) dapat mengakibatkan diskualifikasi pendaftaran oleh panitia sekolah.
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="text-sm sm:text-base font-bold text-slate-900 border-l-4 border-emerald-600 pl-3">3. Penggunaan Dokumen Fisik Langsung</h2>
                <p>
                    Sistem mendukung penyerahan berkas fisik secara langsung ke sekolah tanpa kewajiban unggah file digital. Calon siswa bertanggung jawab menyerahkan dokumen asli/fotokopi legalisir ke meja loket panitia sebelum batas akhir verifikasi gelombang.
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="text-sm sm:text-base font-bold text-slate-900 border-l-4 border-emerald-600 pl-3">4. Integrasi Layanan Google Drive</h2>
                <p>
                    Penggunaan API Google Drive tunduk pada ketentuan Google APIs Terms of Service. Panitia sekolah bertanggung jawab menyediakan kapasitas penyimpanan yang cukup pada akun Google Drive / Google Workspace for Education yang ditautkan.
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="text-sm sm:text-base font-bold text-slate-900 border-l-4 border-emerald-600 pl-3">5. Batasan Tanggung Jawab</h2>
                <p>
                    Platform BitServer Cloud bertindak sebagai penyedia infrastruktur perangkat lunak monolit multi-tenant. Keputusan penerimaan, penolakan, atau kelulusan siswa sepenuhnya merupakan hak prerogatif masing-masing panitia sekolah.
                </p>
            </section>
        </div>

        <!-- Footer -->
        <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between text-2xs sm:text-xs text-slate-500 gap-3">
            <div>
                &copy; {{ date('Y') }} <strong>PPDB Multi-Tenant</strong> — BitServer Cloud. All rights reserved.
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('privacy') }}" class="font-semibold text-slate-700 hover:text-emerald-700 transition">Kebijakan Privasi</a>
                <a href="mailto:admin@bitserver.cloud" class="hover:text-emerald-700 transition">Hubungi Support</a>
            </div>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPDB Multi-Tenant Engine - Platform Penerimaan Peserta Didik Baru Terpadu</title>
    <link rel="icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex flex-col justify-between">
    <!-- Navigation Header -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">
            <a href="/" class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-700 flex items-center justify-center text-white font-extrabold text-sm shadow-xs shrink-0">
                    P
                </div>
                <div class="leading-tight">
                    <span class="font-extrabold text-slate-900 text-sm sm:text-base tracking-tight block">PPDB Multi-Tenant</span>
                    <span class="text-3xs text-emerald-700 font-bold uppercase tracking-wider block">BitServer Cloud Platform</span>
                </div>
            </a>
            <nav class="flex items-center space-x-2 sm:space-x-4">
                <a href="{{ route('privacy') }}" class="text-xs font-semibold text-slate-600 hover:text-emerald-700 transition hidden md:inline-block">Kebijakan Privasi</a>
                <a href="{{ route('terms') }}" class="text-xs font-semibold text-slate-600 hover:text-emerald-700 transition hidden md:inline-block">Syarat & Ketentuan</a>
                <a href="{{ route('superadmin.login') }}" class="btn-primary text-xs py-2 px-3 sm:px-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Masuk Superadmin</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1 max-w-5xl mx-auto px-4 sm:px-6 py-12 md:py-16 space-y-12 text-center flex flex-col items-center justify-center">
        <div class="inline-flex items-center space-x-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-2xs sm:text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wider">
            <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
            <span>Platform Resmi PPDB Multi-Tenant Monolith</span>
        </div>

        <div class="space-y-4 max-w-3xl">
            <h1 class="text-2xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                Sistem PPDB Terpadu &amp; Fleksibel untuk Sekolah dan Madrasah
            </h1>
            <p class="text-slate-600 text-xs sm:text-sm md:text-base leading-relaxed max-w-2xl mx-auto">
                Mendukung pendaftaran online mandiri dengan opsi berkas fisik tanpa upload, pendaftaran langsung di meja loket (walk-in desk), slip bukti anti-hilang, dan integrasi Google Drive mandiri per-sekolah.
            </p>
        </div>

        <!-- Pencarian Cepat Portal Sekolah & Aksi Utama -->
        <div class="w-full max-w-lg bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-3" x-data="{ code: '' }">
            <label class="block text-left text-xs font-bold text-slate-700">Akses Portal PPDB Sekolah Anda:</label>
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-slate-400 font-mono">/</span>
                    <input type="text" x-model="code" placeholder="masukkan-kode-sekolah (contoh: sman1jakarta)"
                           @keydown.enter="if(code.trim()) window.location.href = '/' + code.trim()"
                           class="w-full pl-6 pr-3 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 font-mono">
                </div>
                <button type="button" @click="if(code.trim()) window.location.href = '/' + code.trim()"
                        class="btn-primary text-xs py-2.5 px-4 shrink-0">
                    <span>Buka Portal</span>
                    <span>&rarr;</span>
                </button>
            </div>
            <p class="text-left text-3xs text-slate-400">Contoh demo aktif: <a href="/sman1jakarta" class="text-emerald-700 font-semibold underline">sman1jakarta</a></p>
        </div>

        <!-- 6 Fitur Unggulan Platform -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-left w-full pt-4">
            <div class="card p-5 space-y-2">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Dual-Entry Registration</h3>
                <p class="text-2xs text-slate-500 leading-relaxed">Pendaftaran mandiri online dari rumah serta mode pendaftaran kilat di meja loket panitia sekolah.</p>
            </div>

            <div class="card p-5 space-y-2">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Fleksibilitas Berkas Fisik</h3>
                <p class="text-2xs text-slate-500 leading-relaxed">Calon siswa tanpa scanner/file digital dapat memilih opsi bawa berkas fisik langsung ke loket sekolah.</p>
            </div>

            <div class="card p-5 space-y-2">
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Slip Anti-Hilang &amp; Zero Password</h3>
                <p class="text-2xs text-slate-500 leading-relaxed">Cek status dan cetak ulang slip PDF aman hanya dengan Nomor Registrasi + Tanggal Lahir siswa.</p>
            </div>

            <div class="card p-5 space-y-2">
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Form Builder Preset Jenjang</h3>
                <p class="text-2xs text-slate-500 leading-relaxed">Template siap pakai per jenjang PAUD/TK, SD/MI, SMP/MTs, dan SMA/SMK yang dapat dikustomisasi.</p>
            </div>

            <div class="card p-5 space-y-2">
                <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Penyimpanan Google Drive (BYOD)</h3>
                <p class="text-2xs text-slate-500 leading-relaxed">Setiap sekolah menghubungkan Google Drive sendiri via OAuth sehingga kepemilikan berkas tetap mandiri.</p>
            </div>

            <div class="card p-5 space-y-2">
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Verifikasi Cepat &amp; Ekspor CSV</h3>
                <p class="text-2xs text-slate-500 leading-relaxed">Catatan revisi berkas instan ke HP orang tua, toggle kelulusan cepat, dan ekspor data ke Excel/CSV.</p>
            </div>
        </div>
    </main>

    <!-- Public Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 px-4 sm:px-6 text-center text-xs text-slate-500">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center space-x-2">
                <div class="w-5 h-5 rounded-md bg-emerald-700 flex items-center justify-center text-white font-bold text-3xs">P</div>
                <span class="font-bold text-slate-800">PPDB Multi-Tenant</span>
                <span>&copy; {{ date('Y') }} BitServer Cloud. All rights reserved.</span>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-4 text-2xs sm:text-xs">
                <a href="{{ route('privacy') }}" class="font-semibold text-slate-700 hover:text-emerald-700 transition">Kebijakan Privasi</a>
                <span class="text-slate-300">&bull;</span>
                <a href="{{ route('terms') }}" class="font-semibold text-slate-700 hover:text-emerald-700 transition">Syarat &amp; Ketentuan</a>
                <span class="text-slate-300">&bull;</span>
                <a href="{{ route('superadmin.login') }}" class="font-semibold text-slate-700 hover:text-emerald-700 transition">Superadmin Login</a>
                <span class="text-slate-300">&bull;</span>
                <a href="mailto:admin@bitserver.cloud" class="text-slate-500 hover:text-emerald-700 transition">Kontak Support</a>
            </div>
        </div>
    </footer>
</body>
</html>

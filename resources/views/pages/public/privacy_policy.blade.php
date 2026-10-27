<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi (Privacy Policy) - PPDB Multi-Tenant</title>
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
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Kebijakan Privasi (Privacy Policy)</h1>
                </div>
            </div>
            <a href="/" class="btn-secondary text-xs">
                <span>&larr; Kembali ke Beranda</span>
            </a>
        </div>

        <!-- Body Content -->
        <div class="space-y-6 text-xs sm:text-sm text-slate-700 leading-relaxed">
            <p class="font-semibold text-slate-900">
                Terakhir diperbarui: {{ date('d F Y') }}. Platform <strong>PPDB Multi-Tenant</strong> berkomitmen penuh untuk melindungi kerahasiaan dan privasi data pribadi calon peserta didik, orang tua/wali, serta pihak sekolah penyelenggara penerimaan siswa baru.
            </p>

            <section class="space-y-2">
                <h2 class="text-sm sm:text-base font-bold text-slate-900 border-l-4 border-emerald-600 pl-3">1. Informasi Umum Aplikasi</h2>
                <p>
                    Aplikasi ini adalah <strong>Platform PPDB Multi-Tenant</strong> (diakses melalui <code>https://ppdb.bitserver.cloud</code> dan domain kustom masing-masing sekolah). Sistem ini memfasilitasi proses pendaftaran peserta didik baru baik secara daring (online) maupun luring di meja loket (walk-in desk).
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="text-sm sm:text-base font-bold text-slate-900 border-l-4 border-emerald-600 pl-3">2. Data yang Dikumpulkan</h2>
                <p>Data yang dikumpulkan semata-mata digunakan untuk kebutuhan verifikasi dan seleksi pendaftaran sekolah:</p>
                <ul class="list-disc pl-5 space-y-1 text-slate-600">
                    <li>Data Identitas Calon Siswa: Nama lengkap, NIK, NISN, tempat dan tanggal lahir, jenis kelamin.</li>
                    <li>Data Kontak: Nomor WhatsApp aktif untuk pengiriman bukti slip dan pemberitahuan perbaikan berkas.</li>
                    <li>Data Asal Sekolah & Nilai: Nama sekolah asal, NPSN, dan nilai rapor (sesuai jenjang yang didaftar).</li>
                    <li>Dokumen Persyaratan: Berkas digital seperti Kartu Keluarga (KK), Akta Kelahiran, dan Ijazah/SKL.</li>
                </ul>
            </section>

            <section class="space-y-3 bg-emerald-50/50 p-5 rounded-xl border border-emerald-100">
                <h2 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM19 18H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3z"/></svg>
                    <span>3. Integrasi Google Drive API (OAuth 2.0)</span>
                </h2>
                <p class="text-slate-600">
                    Platform menggunakan model <em>Bring Your Own Drive</em> (BYOD). Setiap sekolah menghubungkan akun Google Drive sendiri melalui OAuth resmi dengan scope <code>https://www.googleapis.com/auth/drive.file</code>.
                </p>
                <ul class="list-disc pl-5 space-y-1 text-slate-600">
                    <li>Platform hanya membuat dan mengunggah berkas pada folder pendaftaran sekolah bersangkutan.</li>
                    <li>Platform <strong>tidak membaca, memodifikasi, ataupun menghapus</strong> berkas pribadi lain di luar folder aplikasi.</li>
                    <li>Token akses disimpan dalam format terenkripsi pada database dan diperbarui secara otomatis.</li>
                </ul>
            </section>

            <section class="space-y-2">
                <h2 class="text-sm sm:text-base font-bold text-slate-900 border-l-4 border-emerald-600 pl-3">4. Keamanan & Prinsip Zero-Password</h2>
                <p>
                    Calon siswa dan orang tua tidak diwajibkan membuat akun atau password. Akses pengecekan status dan unduh ulang slip PDF dilindungi dengan kombinasi unik <strong>Nomor Registrasi</strong> dan <strong>Tanggal Lahir</strong> siswa guna mencegah kebocoran data.
                </p>
            </section>

            <section class="space-y-2">
                <h2 class="text-sm sm:text-base font-bold text-slate-900 border-l-4 border-emerald-600 pl-3">5. Kontak & Pengaduan</h2>
                <p>
                    Apabila Anda memiliki pertanyaan, permohonan koreksi, atau permintaan penghapusan data, silakan hubungi tim kami melalui email: <code>admin@bitserver.cloud</code>.
                </p>
            </section>
        </div>

        <!-- Footer -->
        <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between text-2xs sm:text-xs text-slate-500 gap-3">
            <div>
                &copy; {{ date('Y') }} <strong>PPDB Multi-Tenant</strong> — BitServer Cloud. All rights reserved.
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('terms') }}" class="font-semibold text-slate-700 hover:text-emerald-700 transition">Syarat & Ketentuan</a>
                <a href="mailto:admin@bitserver.cloud" class="hover:text-emerald-700 transition">Hubungi Support</a>
            </div>
        </div>
    </div>
</body>
</html>

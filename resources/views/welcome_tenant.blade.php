<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PPDB - {{ $school->name ?? 'Portal Sekolah' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col justify-between">
    <header class="bg-white border-b border-slate-200 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                    {{ strtoupper(substr($school->school_code ?? 'P', 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-base font-semibold leading-tight text-slate-900">{{ $school->name ?? 'Portal PPDB' }}</h1>
                    <span class="text-xs text-slate-500 font-mono">Kode: {{ $school->school_code ?? '-' }}</span>
                </div>
            </div>
            <div class="text-xs px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-medium border border-emerald-200">
                Mode: {{ $isCustomDomain ? 'Custom Domain (CNAME)' : 'Path Prefix' }}
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-16 flex-1 flex flex-col items-center justify-center text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-medium mb-6">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            Sistem PPDB Online Aktif
        </div>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mb-4">
            Selamat Datang di Portal Penerimaan Peserta Didik Baru
        </h2>
        <p class="text-base sm:text-lg text-slate-600 max-w-2xl mb-8 leading-relaxed">
            Portal resmi pendaftaran peserta didik baru <strong class="text-slate-900">{{ $school->name }}</strong>. Layanan pendaftaran mandiri daring dan loket sekolah berjalan berdampingan.
        </p>

        <div class="flex flex-wrap gap-4 justify-center" x-data="{ open: false }">
            <button @click="open = !open" class="px-6 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm transition shadow-sm">
                Cek Informasi Sekolah
            </button>
            <div x-show="open" x-cloak class="w-full mt-4 p-4 rounded-xl bg-white border border-slate-200 text-left text-sm text-slate-600 max-w-md shadow-xs">
                <p><strong>Subdomain:</strong> {{ $school->subdomain ?? '-' }}</p>
                <p><strong>Status Sekolah:</strong> {{ $school->status ?? '-' }}</p>
                <p><strong>Host Request:</strong> {{ request()->getHost() }}</p>
            </div>
        </div>
    </main>

    <footer class="border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} {{ $school->name }}. Portal Resmi Penerimaan Peserta Didik Baru Online.
    </footer>
</body>
</html>

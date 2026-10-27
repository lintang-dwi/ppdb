<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PPDB Online') - {{ tenant_context()->getSchoolName() ?? config('app.name') }}</title>
    @if (tenant_context()->hasTenant() && tenant_context()->getTenant()?->favicon_url)
        <link rel="icon" href="{{ tenant_context()->getTenant()->favicon_url }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full font-sans antialiased text-slate-800 flex flex-col bg-slate-50 selection:bg-emerald-500 selection:text-white">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:px-4 focus:py-2 focus:bg-emerald-700 focus:text-white focus:rounded-xl focus:shadow-lg focus:outline-hidden text-xs font-bold">Lewati ke konten utama</a>
    <!-- Navbar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">
            <a href="{{ tenant_route('tenant.home') }}" class="flex items-center gap-2.5 sm:gap-3 group min-h-[44px] min-w-0 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 rounded-xl">
                @if (tenant_context()->hasTenant() && tenant_context()->getTenant()->logo_url)
                    <img src="{{ tenant_context()->getTenant()->logo_url }}" alt="Logo {{ tenant_context()->getSchoolName() }}" class="w-9 h-9 rounded-xl object-contain border border-slate-200 shrink-0">
                @else
                    <div class="w-9 h-9 rounded-xl bg-emerald-700 flex items-center justify-center text-white font-bold text-sm shadow-2xs shrink-0">
                        {{ strtoupper(substr(tenant_context()->getSchoolName() ?? 'P', 0, 1)) }}
                    </div>
                @endif
                <div class="leading-tight min-w-0">
                    <span class="block text-sm font-extrabold text-slate-900 group-hover:text-emerald-700 transition truncate">{{ tenant_context()->getSchoolName() ?? 'PPDB Online' }}</span>
                    <span class="text-2xs font-medium text-slate-500 hidden sm:block">Penerimaan Peserta Didik Baru</span>
                </div>
            </a>

            <nav class="flex items-center gap-2 sm:gap-3 text-xs font-semibold shrink-0">
                <a href="{{ tenant_route('applicant.status.lookup') }}" class="btn-secondary text-xs">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Cek Status</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main id="main-content" class="flex-1">
        @if (session('success'))
            <div class="max-w-5xl mx-auto px-4 sm:px-6 pt-4">
                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2.5 shadow-2xs">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="max-w-5xl mx-auto px-4 sm:px-6 pt-4">
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-2.5 shadow-2xs">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer Sederhana Satu Baris -->
    <footer class="mt-auto border-t border-slate-200 bg-white py-4">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} {{ tenant_context()->getSchoolName() ?? 'Sekolah' }}. PPDB Online.</p>
            <div class="flex items-center gap-4 text-xs">
                <a href="{{ tenant_route('tenant.home') }}" class="hover:text-emerald-700 transition">Beranda</a>
                <span class="text-slate-300">&bull;</span>
                <a href="{{ tenant_route('applicant.status.lookup') }}" class="hover:text-emerald-700 transition">Cek Status</a>
                <span class="text-slate-300">&bull;</span>
                <a href="{{ tenant_route('cms.login') }}" class="hover:text-emerald-700 transition">Login Panitia</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

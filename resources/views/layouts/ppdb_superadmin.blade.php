<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Superadmin') - Platform PPDB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col font-sans antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:px-4 focus:py-2 focus:bg-indigo-600 focus:text-white focus:rounded-xl text-xs font-bold">Lewati ke konten utama</a>

    <!-- Navbar Bersih Superadmin -->
    <header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('superadmin.dashboard') }}" class="flex items-center gap-3 hover:opacity-90 transition">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-extrabold text-sm shrink-0">
                    SA
                </div>
                <div>
                    <h1 class="text-sm font-extrabold text-white tracking-tight">Superadmin PPDB</h1>
                    <span class="text-3xs text-indigo-400 uppercase tracking-wider font-semibold">Central Platform Control</span>
                </div>
            </a>

            @auth('superadmin')
            <div class="flex items-center gap-4">
                <span class="text-xs text-slate-400 hidden sm:inline">Masuk sebagai: <strong class="text-slate-200">{{ auth('superadmin')->user()->name }}</strong></span>
                <form method="POST" action="{{ route('superadmin.logout') }}">
                    @csrf
                    <button type="submit" class="min-h-[40px] px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold inline-flex items-center justify-center transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                        Keluar
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </header>

    <!-- Main Container -->
    <main id="main-content" class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="mb-5 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-900 border border-emerald-700/80 text-emerald-300 text-xs font-semibold">
                <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-900 border border-rose-700/80 text-rose-300 text-xs font-semibold">
                <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="py-4 border-t border-slate-900 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} PPDB Multi-Tenant Platform Engine &bull; Central Control
    </footer>
</body>
</html>

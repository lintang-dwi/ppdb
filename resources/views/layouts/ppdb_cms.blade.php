<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CMS PPDB') - {{ tenant_context()->getName() }}</title>
    @if (tenant_context()->hasTenant() && tenant_context()->getTenant()?->favicon_url)
        <link rel="icon" href="{{ tenant_context()->getTenant()->favicon_url }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans" x-data="{ sidebarOpen: false }">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:px-4 focus:py-2 focus:bg-emerald-700 focus:text-white focus:rounded-xl focus:shadow-lg focus:outline-hidden text-xs font-bold">Lewati ke konten utama</a>
    <div class="min-h-screen flex" @keydown.escape.window="sidebarOpen = false">
        <!-- Sidebar Backdrop for Mobile -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden"></div>

        <!-- Sidebar Navigation (Lebar 240px, latar putih) -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-[240px] bg-white border-r border-slate-200 text-slate-700 transition-transform duration-200 ease-in-out lg:static lg:translate-x-0 flex flex-col justify-between shadow-lg lg:shadow-none">
            <div>
                <!-- Brand Header -->
                <div class="h-16 flex items-center gap-3 px-5 border-b border-slate-200 bg-white">
                    @if (tenant_context()->hasTenant() && tenant_context()->getTenant()?->logo_url)
                        <img src="{{ tenant_context()->getTenant()->logo_url }}" alt="Logo {{ tenant_context()->getName() }}" class="w-8 h-8 rounded-xl object-contain border border-slate-200 shrink-0">
                    @else
                        <div class="w-8 h-8 rounded-xl bg-emerald-700 text-white flex items-center justify-center font-bold text-sm shadow-2xs shrink-0">
                            {{ strtoupper(substr(tenant_context()->getSchoolCode() ?? 'P', 0, 1)) }}
                        </div>
                    @endif
                    <div class="truncate">
                        <h2 class="text-sm font-extrabold text-slate-900 truncate">{{ tenant_context()->getName() }}</h2>
                        <span class="text-2xs text-emerald-700 font-semibold uppercase tracking-wider">Panel Panitia</span>
                    </div>
                </div>

                <!-- Nav Items (7 Menu) -->
                <nav class="p-3 space-y-1 text-xs font-medium">
                    <a href="{{ tenant_route('cms.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition min-h-[40px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 {{ request()->routeIs('*cms.dashboard') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 opacity-80 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ tenant_route('cms.waves.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition min-h-[40px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 {{ request()->routeIs('*cms.waves*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 opacity-80 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Gelombang</span>
                    </a>
                    <a href="{{ tenant_route('cms.tracks.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition min-h-[40px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 {{ request()->routeIs('*cms.tracks*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 opacity-80 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Jalur Masuk</span>
                    </a>
                    <a href="{{ tenant_route('cms.form-builder.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition min-h-[40px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 {{ request()->routeIs('*cms.form-builder*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 opacity-80 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span>Formulir Pendaftaran</span>
                    </a>
                    <a href="{{ tenant_route('cms.applicants.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition min-h-[40px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 {{ request()->routeIs('*cms.applicants*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 opacity-80 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Pendaftar</span>
                    </a>
                    <a href="{{ tenant_route('cms.walk-in.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition min-h-[40px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 {{ request()->routeIs('*cms.walk-in*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 opacity-80 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Daftar di Loket</span>
                    </a>
                    <a href="{{ tenant_route('cms.gdrive.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition min-h-[40px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 {{ request()->routeIs('*cms.gdrive*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 opacity-80 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                        <span>Google Drive</span>
                    </a>
                </nav>
            </div>

            <!-- User Footer in Sidebar -->
            <div class="p-3 border-t border-slate-200 bg-slate-50/50">
                <div class="px-2 py-1">
                    <p class="text-xs font-bold text-slate-800 truncate">{{ auth('ppdb')->user()?->name ?? 'Panitia' }}</p>
                    <p class="text-2xs text-slate-500 truncate">{{ auth('ppdb')->user()?->email }}</p>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            @if (session('superadmin_impersonating'))
                <div class="bg-indigo-600 text-white px-4 py-2 text-xs font-semibold flex items-center justify-between shrink-0 shadow-xs z-30">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        <span>Mode Impersonate Superadmin: Mengelola <strong>{{ session('impersonated_school_name', tenant_context()->getName()) }}</strong></span>
                    </div>
                    <a href="{{ route('superadmin.leave-impersonate') }}" class="min-h-[36px] px-3 py-1 bg-white text-indigo-700 hover:bg-indigo-50 rounded-lg text-2xs font-bold transition shadow-2xs flex items-center">
                        Kembali ke Superadmin &rarr;
                    </a>
                </div>
            @endif

            <!-- Topbar Bersih -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 gap-3 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <button @click="sidebarOpen = true" aria-label="Buka menu navigasi" class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-xl text-slate-600 hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 lg:hidden shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <span class="font-extrabold text-slate-900 text-sm truncate">{{ tenant_context()->getName() }}</span>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ tenant_route('tenant.home') }}" target="_blank" class="min-h-[40px] px-3.5 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold inline-flex items-center gap-1.5 shadow-2xs transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-400">
                        <span>Lihat Portal Publik</span>
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>

                    <form method="POST" action="{{ tenant_route('cms.logout') }}">
                        @csrf
                        <button type="submit" class="min-h-[40px] px-3 py-1.5 rounded-xl text-slate-600 hover:text-rose-700 hover:bg-rose-50 text-xs font-semibold inline-flex items-center gap-1.5 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600" title="Keluar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Body -->
            <main id="main-content" class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
                @if (session('success'))
                    <div class="mb-5 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold shadow-2xs">
                        <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-5 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold shadow-2xs">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>

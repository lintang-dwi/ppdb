<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Panitia PPDB - {{ $school->name ?? 'Sekolah' }}</title>
    @if ($school?->favicon_url)
        <link rel="icon" href="{{ $school->favicon_url }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
    <!-- Centered Login Card -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 sm:px-10 rounded-3xl border border-slate-200 shadow-xs space-y-6">
            <div class="text-center space-y-2">
                @if ($school?->logo_url)
                    <img src="{{ $school->logo_url }}" alt="Logo {{ $school->name }}" class="mx-auto w-12 h-12 rounded-2xl object-contain border border-slate-200 shadow-2xs">
                @else
                    <div class="mx-auto w-12 h-12 rounded-2xl bg-emerald-700 text-white flex items-center justify-center font-black text-xl shadow-2xs">
                        {{ strtoupper(substr($school->school_code ?? 'P', 0, 1)) }}
                    </div>
                @endif
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">
                        Masuk Panitia PPDB
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $school->name }}
                    </p>
                </div>
                <div>
                    <span class="pill bg-slate-100 text-slate-700 font-mono text-2xs">
                        Kode: {{ $school->school_code }}
                    </span>
                </div>
            </div>

            @if ($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ tenant_route('cms.login.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700">Alamat Email Panitia</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                           class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700">Kata Sandi</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 focus:outline-hidden">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded-sm border-slate-300 text-emerald-700 focus:ring-emerald-500">
                        <span class="ml-2 block text-xs text-slate-600">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="btn-primary w-full text-center text-xs">
                    Masuk ke Panel PPDB &rarr;
                </button>
            </form>

            <p class="text-center text-3xs text-slate-400 pt-2 border-t border-slate-100">
                Akses terbatas untuk panitia resmi terdaftar pada {{ $school->name }}.
            </p>
        </div>
    </div>
</body>
</html>


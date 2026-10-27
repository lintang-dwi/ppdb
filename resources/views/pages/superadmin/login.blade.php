@extends('layouts.ppdb_superadmin')

@section('title', 'Login Superadmin')

@section('content')
<div class="max-w-md mx-auto py-12 px-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl">
        <div class="text-center mb-6">
            <div class="w-11 h-11 rounded-xl bg-indigo-600 text-white mx-auto flex items-center justify-center mb-3 font-extrabold text-sm shadow-xs">
                SA
            </div>
            <h2 class="text-xl font-bold text-white tracking-tight">Login Superadmin</h2>
            <p class="text-xs text-slate-400 mt-1">Akses administrasi dan pemantauan sistem PPDB seluruh sekolah.</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 p-3 rounded-xl bg-rose-950/60 border border-rose-800 text-rose-300 text-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.login.submit') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300">Email Superadmin</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="superadmin@ppdb.cloud"
                    class="mt-1.5 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-hidden">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300">Kata Sandi</label>
                <input type="password" id="password" name="password" required
                    placeholder="••••••••••••"
                    class="mt-1.5 block w-full rounded-xl bg-slate-950 border border-slate-800 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-hidden">
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-2xs text-slate-400">Ingat sesi di perangkat ini</span>
                </label>
            </div>

            <button type="submit" class="w-full mt-2 py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-xs transition flex items-center justify-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                Masuk ke Portal Superadmin
            </button>
        </form>
    </div>
</div>
@endsection

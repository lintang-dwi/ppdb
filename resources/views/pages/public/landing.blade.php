@extends('layouts.ppdb_public')

@section('title', 'Penerimaan Peserta Didik Baru - '.$school->name)

@section('content')
<div class="space-y-10 sm:space-y-12 py-6 sm:py-10 max-w-5xl mx-auto px-4 sm:px-6">
    <!-- Hero Section: Clean White Card with 2-Column Desktop Grid -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 shadow-xs">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <!-- Left Column: Status Pill, Large H1, Paragraph, 2 CTA Buttons -->
            <div class="lg:col-span-7 space-y-4 text-left">
                <div>
                    @if ($isOpen)
                        <span class="pill pill-verified">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                            Pendaftaran Sedang Dibuka
                        </span>
                    @else
                        <span class="pill pill-cancelled">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                            Pendaftaran Belum Dibuka
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 leading-tight">
                    Portal PPDB Online {{ $school->name }}
                </h1>

                <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                    Penerimaan Peserta Didik Baru tahun ajaran resmi sekolah. Daftar secara mandiri dari ponsel Anda dengan cepat dan mudah.
                </p>

                @if ($isOpen)
                    <div class="pt-2 flex flex-col sm:flex-row gap-3">
                        <a href="{{ tenant_route('applicant.register.index') }}" class="btn-primary w-full sm:w-auto text-center">
                            <span>Daftar Sekarang</span>
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                        <a href="{{ tenant_route('applicant.status.lookup') }}" class="btn-secondary w-full sm:w-auto text-center">
                            Cek Status Saya
                        </a>
                    </div>
                @else
                    <div class="pt-2">
                        <a href="{{ tenant_route('applicant.status.lookup') }}" class="btn-secondary w-full sm:w-auto text-center">
                            Cek Status Pendaftaran Sebelumnya
                        </a>
                    </div>
                @endif
            </div>

            <!-- Right Column: Active Wave Card in bg-emerald-50 border-emerald-200 -->
            <div class="lg:col-span-5">
                @if ($activeWave)
                    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="pill bg-emerald-100 text-emerald-800">Gelombang Aktif</span>
                            <span class="text-xs font-semibold text-emerald-700">TA {{ $activeWave->academic_year }}</span>
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-slate-900 break-words">{{ $activeWave->name }}</h2>
                            <p class="text-xs text-slate-600 mt-1">
                                Batas Tutup: <strong class="text-slate-800">{{ \Carbon\Carbon::parse($activeWave->end_date)->translatedFormat('d F Y, H:i') }} WIB</strong>
                            </p>
                        </div>
                        <a href="{{ tenant_route('applicant.register.index') }}" class="btn-primary w-full text-xs text-center">
                            Mulai Mengisi Formulir &rarr;
                        </a>
                    </div>
                @else
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 text-center space-y-2">
                        <span class="pill bg-slate-200 text-slate-700">Informasi Jadwal</span>
                        <p class="text-xs text-slate-600">Saat ini belum ada gelombang pendaftaran yang sedang dibuka.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 3 Numbered Steps (01 / 02 / 03) -->
    <div class="space-y-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Alur Pendaftaran</h2>
            <p class="text-xs text-slate-500">Tiga langkah mudah menyelesaikan pendaftaran calon siswa baru.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card p-5 space-y-2 bg-white">
                <span class="text-2xl font-black text-emerald-700 font-mono tracking-tight">01</span>
                <h3 class="font-bold text-slate-900 text-sm">Pilih Jalur &amp; Isi Data</h3>
                <p class="text-xs text-slate-600 leading-relaxed">Tentukan jalur masuk yang sesuai lalu lengkapi data calon siswa serta kontak orang tua / wali.</p>
            </div>
            <div class="card p-5 space-y-2 bg-white">
                <span class="text-2xl font-black text-emerald-700 font-mono tracking-tight">02</span>
                <h3 class="font-bold text-slate-900 text-sm">Unggah Foto Berkas</h3>
                <p class="text-xs text-slate-600 leading-relaxed">Foto langsung kartu keluarga atau berkas persyaratan dari ponsel, berkas dikompresi otomatis.</p>
            </div>
            <div class="card p-5 space-y-2 bg-white">
                <span class="text-2xl font-black text-emerald-700 font-mono tracking-tight">03</span>
                <h3 class="font-bold text-slate-900 text-sm">Simpan Bukti Pendaftaran</h3>
                <p class="text-xs text-slate-600 leading-relaxed">Dapatkan nomor registrasi unik dan kartu bukti pendaftaran resmi dalam format PDF.</p>
            </div>
        </div>
    </div>

    <!-- Track Catalog Cards in bg-slate-50 rounded-2xl -->
    <div class="space-y-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Pilihan Jalur Pendaftaran</h2>
            <p class="text-xs text-slate-500">Pilih jalur yang sesuai dengan kriteria dan berkas yang Anda miliki.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($tracks as $track)
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-3 flex flex-col justify-between hover:border-emerald-300 transition">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="pill bg-white text-slate-700 border border-slate-200 text-2xs">
                                {{ $track->quota ? $track->quota.' Kuota' : 'Tanpa Batas Kuota' }}
                            </span>
                        </div>
                        <h3 class="font-bold text-slate-900 text-base">{{ $track->name }}</h3>
                        <p class="text-xs text-slate-600 leading-relaxed min-h-[36px]">
                            {{ $track->description ?: 'Pendaftaran melalui jalur resmi sekolah dengan seleksi berkas terpadu.' }}
                        </p>
                    </div>
                    @if ($isOpen)
                        <div class="pt-2 border-t border-slate-200/60">
                            <a href="{{ tenant_route('applicant.register.index') }}?track={{ $track->id }}" class="min-h-[44px] inline-flex items-center text-xs font-bold text-emerald-700 hover:text-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                                Pilih jalur ini &rarr;
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="col-span-full p-8 text-center bg-white rounded-2xl border border-slate-200 text-slate-500 text-sm">
                    Belum ada data jalur pendaftaran yang aktif.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection


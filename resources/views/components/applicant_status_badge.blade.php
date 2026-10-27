@props(['status'])

@php
    $configs = [
        'submitted' => ['class' => 'bg-blue-50 text-blue-700 border-blue-200', 'label' => 'Terdaftar'],
        'need_revision' => ['class' => 'bg-amber-50 text-amber-800 border-amber-200', 'label' => 'Perlu Perbaikan'],
        'verified' => ['class' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Berkas Lengkap'],
        'accepted' => ['class' => 'bg-purple-50 text-purple-700 border-purple-200', 'label' => 'Diterima'],
        'completed' => ['class' => 'bg-teal-50 text-teal-700 border-teal-200', 'label' => 'Selesai Daftar Ulang'],
        'cancelled' => ['class' => 'bg-rose-50 text-rose-700 border-rose-200', 'label' => 'Dibatalkan'],
    ];
    $config = $configs[$status] ?? ['class' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => $status];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-2xs font-semibold border ' . $config['class']]) }}>
    {{ $config['label'] }}
</span>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pendaftaran - {{ $applicant->registration_number }}</title>
    <style>
        @page { margin: 12mm 15mm; size: a4 portrait; }
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 10pt; color: #1e293b; line-height: 1.35; margin: 0; padding: 0; }
        .header { border-bottom: 2px solid #0f766e; padding-bottom: 8px; margin-bottom: 12px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .school-title { font-size: 13pt; font-weight: bold; color: #0f766e; text-transform: uppercase; margin: 0; }
        .school-sub { font-size: 8pt; color: #64748b; margin: 2px 0 0 0; }
        .slip-title { font-size: 10.5pt; font-weight: bold; text-align: center; text-transform: uppercase; margin: 8px 0 10px 0; color: #0f172a; letter-spacing: 0.5px; }
        .reg-box { background-color: #f8fafc; border: 1.5px dashed #0f766e; border-radius: 6px; padding: 8px 12px; margin-bottom: 12px; text-align: center; }
        .reg-label { font-size: 7.5pt; color: #0f766e; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; }
        .reg-number { font-size: 16pt; font-weight: bold; font-family: 'Courier New', Courier, monospace; color: #0f172a; margin: 2px 0; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .data-table td { padding: 6px 6px; font-size: 9pt; vertical-align: top; border-bottom: 1px solid #f1f5f9; }
        .label-col { width: 32%; color: #475569; font-weight: bold; }
        .colon-col { width: 3%; text-align: center; color: #64748b; }
        .val-col { width: 65%; color: #0f172a; }
        .notes-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; font-size: 7.5pt; color: #475569; }
        .notes-card strong { color: #0f172a; display: block; margin-bottom: 3px; font-size: 8pt; }
        .notes-card ol { margin: 0; padding-left: 14px; }
        .notes-card li { margin-bottom: 2px; }
        .footer-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .qr-cell { width: 100px; text-align: center; vertical-align: middle; }
        .qr-img { width: 90px; height: 90px; display: block; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="vertical-align: middle;">
                    <h1 class="school-title">{{ $school->name }}</h1>
                    <p class="school-sub">PANITIA PENERIMAAN PESERTA DIDIK BARU (PPDB) ONLINE</p>
                    <p class="school-sub">Tahun Ajaran {{ $wave?->academic_year ?? date('Y').'/'.(date('Y')+1) }}</p>
                </td>
                <td style="text-align: right; width: 60px; vertical-align: middle;">
                    @if ($school->logo_url)
                        <img src="{{ $school->logo_url }}" alt="Logo" style="width: 48px; height: 48px; object-fit: contain;">
                    @else
                        <div style="width: 48px; height: 48px; background-color: #0f766e; color: #ffffff; text-align: center; line-height: 48px; font-weight: bold; font-size: 18pt; border-radius: 6px;">
                            {{ strtoupper(substr($school->name, 0, 1)) }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="slip-title">Kartu Tanda Bukti Pendaftaran</div>

    <div class="reg-box">
        <div class="reg-label">Nomor Registrasi Resmi Calon Siswa</div>
        <div class="reg-number">{{ $applicant->registration_number }}</div>
        <div style="font-size: 7.5pt; color: #64748b;">Tercatat pada Sistem PPDB: {{ $printedAt }}</div>
    </div>

    <table class="data-table">
        <tr>
            <td class="label-col">Nama Lengkap Siswa</td>
            <td class="colon-col">:</td>
            <td class="val-col"><strong>{{ $applicant->full_name }}</strong></td>
        </tr>
        <tr>
            <td class="label-col">NIK (Nomor Kependudukan)</td>
            <td class="colon-col">:</td>
            <td class="val-col">{{ $applicant->nik }}</td>
        </tr>
        <tr>
            <td class="label-col">Tempat, Tanggal Lahir</td>
            <td class="colon-col">:</td>
            <td class="val-col">{{ $applicant->birth_place ?: '-' }}, {{ \Carbon\Carbon::parse($applicant->birth_date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label-col">Jenis Kelamin</td>
            <td class="colon-col">:</td>
            <td class="val-col">{{ $applicant->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
        </tr>
        <tr>
            <td class="label-col">Jalur Pendaftaran</td>
            <td class="colon-col">:</td>
            <td class="val-col"><strong>{{ $track?->name ?? 'Jalur Reguler' }}</strong></td>
        </tr>
        <tr>
            <td class="label-col">Gelombang</td>
            <td class="colon-col">:</td>
            <td class="val-col">{{ $wave?->name ?? 'Gelombang Reguler' }}</td>
        </tr>
        <tr>
            <td class="label-col">Nomor WhatsApp Kontak</td>
            <td class="colon-col">:</td>
            <td class="val-col">{{ $applicant->phone_whatsapp }}</td>
        </tr>
        @if (!empty($applicant->custom_fields))
            @foreach ($applicant->custom_fields as $fieldKey => $fieldVal)
                @if ($fieldVal !== null && $fieldVal !== '' && !is_array($fieldVal))
                <tr>
                    <td class="label-col">{{ $fieldLabels[$fieldKey] ?? ucwords(str_replace('_', ' ', $fieldKey)) }}</td>
                    <td class="colon-col">:</td>
                    <td class="val-col">{{ $fieldVal }}</td>
                </tr>
                @elseif (is_array($fieldVal) && count($fieldVal) > 0)
                <tr>
                    <td class="label-col">{{ $fieldLabels[$fieldKey] ?? ucwords(str_replace('_', ' ', $fieldKey)) }}</td>
                    <td class="colon-col">:</td>
                    <td class="val-col">{{ implode(', ', $fieldVal) }}</td>
                </tr>
                @endif
            @endforeach
        @endif
    </table>

    <table class="footer-table">
        <tr>
            <td class="qr-cell">
                <img src="{{ $qrBase64 }}" alt="QR Verifikasi" class="qr-img">
                <div style="font-size: 6.5pt; color: #64748b; margin-top: 3px;">Pindai untuk Cek Status</div>
            </td>
            <td style="padding-left: 14px; vertical-align: top;">
                <div class="notes-card">
                    <strong>Petunjuk & Langkah Selanjutnya:</strong>
                    <ol>
                        <li>Simpan bukti pendaftaran ini (cetak atau simpan PDF pada ponsel).</li>
                        <li>Pantau pengumuman hasil seleksi berkas melalui portal web PPDB sekolah.</li>
                        <li>Jika dinyatakan diterima, tunjukkan kartu ini beserta berkas fisik asli saat daftar ulang.</li>
                    </ol>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>

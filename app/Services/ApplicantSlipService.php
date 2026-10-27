<?php

namespace App\Services;

use App\Models\PpdbApplicant;
use App\Models\School;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfInstance;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ApplicantSlipService
{
    /**
     * Generate PDF card slip instance for an applicant.
     */
    public function generatePdf(PpdbApplicant $applicant, School $school): DomPdfInstance
    {
        // 1. Generate QR Code verification hash pointing to status lookup
        $verifyUrl = tenant_route('applicant.status.lookup', [
            'school_code' => $school->school_code,
            'reg' => $applicant->registration_number,
        ]);

        $qrSvg = QrCode::format('svg')
            ->size(110)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($verifyUrl);

        $qrBase64 = 'data:image/svg+xml;base64,'.base64_encode($qrSvg);

        $fieldLabels = \App\Models\PpdbFormField::where('field_type', '!=', 'file')
            ->pluck('field_label', 'field_key')
            ->all();

        // 2. Prepare print data
        $data = [
            'school' => $school,
            'applicant' => $applicant,
            'wave' => $applicant->wave,
            'track' => $applicant->track,
            'qrBase64' => $qrBase64,
            'fieldLabels' => $fieldLabels,
            'printedAt' => now()->translatedFormat('d F Y, H:i').' WIB',
        ];

        // 3. Render DomPDF instance
        return Pdf::loadView('pdf.applicant_card_slip', $data)
            ->setPaper('a5', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Helvetica',
            ]);
    }

    /**
     * Build standardized WhatsApp notification text (Layer 2 safety net).
     */
    public function buildWhatsAppText(PpdbApplicant $applicant, School $school): string
    {
        $statusUrl = tenant_route('applicant.status.lookup', [
            'school_code' => $school->school_code,
            'reg' => $applicant->registration_number,
        ]);

        $dobFormatted = $applicant->birth_date
            ? \Carbon\Carbon::parse($applicant->birth_date)->translatedFormat('d F Y')
            : '-';

        return "*BUKTI PENDAFTARAN PPDB ONLINE*\n"
            ."*{$school->name}*\n\n"
            ."Simpan pesan ini sebagai bukti pendaftaran resmi:\n"
            ."• No. Registrasi: *{$applicant->registration_number}*\n"
            ."• Nama Siswa: *{$applicant->full_name}*\n"
            ."• Tanggal Lahir: *{$dobFormatted}*\n"
            ."• Jalur Pendaftaran: *".($applicant->track?->name ?? 'Reguler')."*\n"
            ."• Gelombang: *".($applicant->wave?->name ?? '-')."*\n\n"
            ."Tautan Cek Status Mandiri & Unduh Slip:\n{$statusUrl}\n\n"
            ."(Gunakan No. Registrasi dan Tanggal Lahir untuk masuk portal).";
    }
}

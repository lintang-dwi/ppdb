<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\PpdbApplicant;
use App\Services\TenantContextService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicantExportController extends Controller
{
    public function __invoke(Request $request, TenantContextService $tenantContext): StreamedResponse
    {
        $schoolCode = $tenantContext->getSchoolCode();
        $fileName = "PPDB_Pendaftar_{$schoolCode}_".date('Ymd_His').'.csv';

        $query = PpdbApplicant::with(['wave', 'track'])->latest();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        if ($request->filled('track_id')) {
            $query->where('track_id', $request->input('track_id'));
        }
        if ($request->filled('wave_id')) {
            $query->where('wave_id', $request->input('wave_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('origin')) {
            $query->where('registration_origin', $request->input('origin'));
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header
            fputcsv($handle, [
                'No. Registrasi',
                'Tipe Registrasi',
                'Nama Lengkap',
                'NIK',
                'Tempat Lahir',
                'Tanggal Lahir',
                'Jenis Kelamin',
                'No. WhatsApp',
                'Jalur',
                'Gelombang',
                'Status Pendaftaran',
                'Kelengkapan Berkas',
                'Waktu Mendaftar',
            ]);

            $query->chunk(200, function ($applicants) use ($handle) {
                foreach ($applicants as $app) {
                    fputcsv($handle, [
                        $app->registration_number,
                        $app->registration_origin === 'offline_walk_in' ? 'Daftar di Loket' : 'Online',
                        $app->full_name,
                        "'".$app->nik, // apostrophe prevents Excel converting NIK to scientific notation
                        $app->birth_place ?? '-',
                        $app->birth_date?->format('d/m/Y') ?? '-',
                        $app->gender === 'L' ? 'Laki-laki' : 'Perempuan',
                        "'".$app->phone_whatsapp,
                        $app->track?->name ?? 'Reguler',
                        $app->wave?->name ?? '-',
                        $app->status_label,
                        $app->document_completeness === 'complete_physical' ? 'Fisik Lengkap' : ($app->document_completeness === 'complete_digital' ? 'Digital Lengkap' : 'Belum Lengkap'),
                        $app->created_at->format('d/m/Y H:i'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}

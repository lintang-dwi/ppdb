<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PpdbApplicant;
use App\Services\ApplicantSlipService;
use App\Services\TenantContextService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApplicantSlipController extends Controller
{
    public function __construct(
        protected TenantContextService $tenantContext,
        protected ApplicantSlipService $slipService
    ) {}

    public function __invoke(Request $request): Response
    {
        $regNumber = $request->input('reg');
        $birthDate = $request->input('dob');

        if (! $regNumber) {
            abort(400, 'Nomor registrasi wajib disertakan.');
        }

        $query = PpdbApplicant::where('registration_number', $regNumber);

        // Security / Zero-Password check: unless staff is logged in or valid signature, require matching birth_date
        $isStaff = auth('ppdb')->check();
        $isSigned = $request->hasValidSignature();

        if (! $isStaff && ! $isSigned) {
            if (! $birthDate) {
                abort(403, 'Akses ditolak: Tanggal lahir wajib disertakan untuk verifikasi keamanan.');
            }
            $query->whereDate('birth_date', $birthDate);
        }

        $applicant = $query->first();

        if (! $applicant) {
            abort(404, 'Data pendaftar dengan nomor registrasi dan tanggal lahir tersebut tidak ditemukan.');
        }

        $school = $this->tenantContext->getTenant();
        $pdf = $this->slipService->generatePdf($applicant, $school);
        $fileName = "BUKTI_PPDB_{$applicant->registration_number}.pdf";

        if ($request->boolean('preview')) {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
    }
}

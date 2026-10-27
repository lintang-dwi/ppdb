<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessApplicantDocUploadJob;
use App\Models\PpdbApplicant;
use App\Models\PpdbDocument;
use App\Services\TenantContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApplicantStatusController extends Controller
{
    public function __construct(
        protected TenantContextService $tenantContext
    ) {}

    public function lookup(): View
    {
        return view('pages.public.status_lookup', ['school' => $this->tenantContext->getTenant()]);
    }

    public function check(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'registration_number' => ['required', 'string'],
            'birth_date' => ['required', 'date'],
        ]);

        $applicant = PpdbApplicant::where('registration_number', $validated['registration_number'])
            ->whereDate('birth_date', $validated['birth_date'])->first();

        if (! $applicant) {
            return back()->withInput()->with('error', 'Nomor registrasi dan tanggal lahir tidak cocok atau belum terdaftar.');
        }

        session(['verified_applicant_id' => $applicant->id]);

        return redirect()->to(tenant_route('applicant.status.show', ['registration_number' => $applicant->registration_number]));
    }

    public function show(string $registrationNumber): View|RedirectResponse
    {
        $applicant = $this->resolveApplicant($registrationNumber);
        if (! $applicant) {
            return redirect()->to(tenant_route('applicant.status.lookup'))->with('error', 'Silakan verifikasi identitas terlebih dahulu.');
        }

        return view('pages.public.status_detail', [
            'school' => $this->tenantContext->getTenant(),
            'applicant' => $applicant->load(['wave', 'track', 'documents']),
        ]);
    }

    public function reuploadForm(string $registrationNumber): View|RedirectResponse
    {
        $applicant = $this->resolveApplicant($registrationNumber);
        if (! $applicant) return redirect()->to(tenant_route('applicant.status.lookup'));

        if ($applicant->status !== 'need_revision') {
            return redirect()->to(tenant_route('applicant.status.show', ['registration_number' => $applicant->registration_number]))
                ->with('error', 'Berkas Anda tidak dalam status perlu perbaikan.');
        }

        return view('pages.public.status_reupload', [
            'school' => $this->tenantContext->getTenant(),
            'applicant' => $applicant->load(['documents']),
        ]);
    }

    public function submitRevision(Request $request, string $registrationNumber): RedirectResponse
    {
        $applicant = $this->resolveApplicant($registrationNumber);
        if (! $applicant || $applicant->status !== 'need_revision') {
            return redirect()->to(tenant_route('applicant.status.lookup'));
        }

        $request->validate(['documents' => ['required', 'array'], 'documents.*' => ['nullable', 'file', 'max:5120']]);
        $schoolCode = $this->tenantContext->getSchoolCode();
        $jobs = [];

        DB::transaction(function () use ($applicant, $request, $schoolCode, &$jobs) {
            $tempDir = "temp_ppdb/{$schoolCode}/{$applicant->registration_number}";
            foreach ($request->file('documents', []) as $docKey => $file) {
                if ($file && $file->isValid()) {
                    $stored = $file->storeAs($tempDir, "{$docKey}_".time().".{$file->getClientOriginalExtension()}");
                    $doc = PpdbDocument::updateOrCreate(
                        ['applicant_id' => $applicant->id, 'document_type' => $docKey],
                        ['original_file_name' => $file->getClientOriginalName(), 'verification_status' => 'pending', 'rejection_reason' => null]
                    );
                    $jobs[] = new ProcessApplicantDocUploadJob($doc->id, $stored, $schoolCode, date('Y'), $applicant->registration_number, $applicant->full_name);
                }
            }
            $applicant->update(['status' => 'submitted', 'verification_notes' => null]);
        });

        foreach ($jobs as $job) dispatch($job);

        return redirect()->to(tenant_route('applicant.status.show', ['registration_number' => $applicant->registration_number]))
            ->with('success', 'Perbaikan berkas Anda berhasil dikirim! Menunggu verifikasi ulang panitia.');
    }

    protected function resolveApplicant(string $registrationNumber): ?PpdbApplicant
    {
        $applicant = PpdbApplicant::where('registration_number', $registrationNumber)->first();
        if (! $applicant) return null;
        if (auth('ppdb')->check() || session('verified_applicant_id') === $applicant->id) return $applicant;

        return null;
    }
}

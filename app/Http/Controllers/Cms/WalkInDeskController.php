<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicantWalkInRequest;
use App\Jobs\ProcessApplicantDocUploadJob;
use App\Models\PpdbApplicant;
use App\Models\PpdbDocument;
use App\Models\PpdbFormField;
use App\Models\PpdbTrack;
use App\Models\PpdbWave;
use App\Services\RegistrationNumberService;
use App\Services\TenantContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WalkInDeskController extends Controller
{
    public function __construct(
        protected TenantContextService $tenantContext,
        protected RegistrationNumberService $numberService
    ) {}

    public function index(): View
    {
        $school = $this->tenantContext->getTenant();
        $waves = PpdbWave::active()->get();
        if ($waves->isEmpty()) {
            $waves = PpdbWave::latest()->take(1)->get();
        }
        $tracks = PpdbTrack::active()->orderBy('order_index')->get();
        $customFields = PpdbFormField::active()->whereNotIn('step', ['berkas'])->orderBy('order_index')->get();

        return view('pages.cms.walk_in_form', compact('school', 'waves', 'tracks', 'customFields'));
    }

    public function store(StoreApplicantWalkInRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $schoolCode = $this->tenantContext->getSchoolCode();
        $jobs = [];

        $applicant = DB::transaction(function () use ($validated, $schoolCode, $request, &$jobs) {
            $regNumber = $this->numberService->generate($schoolCode);
            $isPhysical = (bool) $request->input('is_physical_submitted', false);

            $applicant = PpdbApplicant::create(array_merge($validated, [
                'registration_number' => $regNumber,
                'registration_origin' => 'offline_walk_in',
                'status' => $isPhysical ? 'verified' : 'submitted',
                'document_completeness' => $isPhysical ? 'complete_physical' : 'incomplete',
                'verified_at' => $isPhysical ? now() : null,
                'verified_by' => $isPhysical ? (auth('ppdb')->user()?->name ?? 'Petugas Loket') : null,
            ]));

            $tempDir = "temp_ppdb/{$schoolCode}/{$regNumber}";
            foreach ($request->file('documents', []) as $docKey => $file) {
                if ($file && $file->isValid()) {
                    $stored = $file->storeAs($tempDir, "{$docKey}_".time().".{$file->getClientOriginalExtension()}");
                    $doc = PpdbDocument::create([
                        'applicant_id' => $applicant->id,
                        'document_type' => $docKey,
                        'original_file_name' => $file->getClientOriginalName(),
                        'verification_status' => $isPhysical ? 'valid' : 'pending',
                        'is_physical_submitted' => $isPhysical,
                    ]);
                    $jobs[] = new ProcessApplicantDocUploadJob($doc->id, $stored, $schoolCode, date('Y'), $regNumber, $applicant->full_name);
                }
            }

            return $applicant;
        });

        foreach ($jobs as $job) {
            dispatch($job);
        }

        return redirect()->to(tenant_route('cms.walk-in.index'))
            ->with('success', "Pendaftaran calon siswa {$applicant->full_name} berhasil disimpan di loket!")
            ->with('registered_applicant', [
                'id' => $applicant->id,
                'name' => $applicant->full_name,
                'reg_number' => $applicant->registration_number,
                'slip_url' => tenant_route('applicant.slip.download', ['reg' => $applicant->registration_number]),
            ]);
    }
}

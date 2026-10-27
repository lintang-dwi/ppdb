<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicantPublicRequest;
use App\Jobs\ProcessApplicantDocUploadJob;
use App\Models\PpdbApplicant;
use App\Models\PpdbDocument;
use App\Models\PpdbFormField;
use App\Models\PpdbTrack;
use App\Models\PpdbWave;
use App\Services\RegistrationNumberService;
use App\Services\TenantContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationWizardController extends Controller
{
    public function __construct(
        protected TenantContextService $tenantContext,
        protected RegistrationNumberService $numberService
    ) {}

    public function index(): View
    {
        $activeWave = PpdbWave::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->firstOrFail();

        return view('pages.public.register_step_container', [
            'school' => $this->tenantContext->getTenant(),
            'wave' => $activeWave,
            'tracks' => PpdbTrack::where('is_active', true)->orderBy('order_index')->get(),
            'fields' => PpdbFormField::active()->ordered()->get()->groupBy('step'),
        ]);
    }

    public function store(StoreApplicantPublicRequest $request): RedirectResponse
    {
        $schoolCode = $this->tenantContext->getSchoolCode();
        $validated = $request->validated();
        $isPhysical = (bool) $request->boolean('is_physical_submitted');
        $jobs = [];

        $applicant = DB::transaction(function () use ($validated, $schoolCode, $request, $isPhysical, &$jobs) {
            $regNumber = $this->numberService->generate($schoolCode);
            $applicant = PpdbApplicant::create(array_merge($validated, [
                'registration_number' => $regNumber,
                'registration_origin' => 'online',
                'status' => 'submitted',
                'document_completeness' => $isPhysical ? 'complete_physical' : 'incomplete',
            ]));

            $tempDir = "temp_ppdb/{$schoolCode}/{$regNumber}";
            $uploaded = [];
            foreach ($request->file('documents', []) as $docKey => $file) {
                if ($file && $file->isValid()) {
                    $stored = $file->storeAs($tempDir, "{$docKey}_".time().".{$file->getClientOriginalExtension()}");
                    $doc = PpdbDocument::create([
                        'applicant_id' => $applicant->id,
                        'document_type' => $docKey,
                        'original_file_name' => $file->getClientOriginalName(),
                        'verification_status' => 'pending',
                        'is_physical_submitted' => false,
                    ]);
                    $jobs[] = new ProcessApplicantDocUploadJob($doc->id, $stored, $schoolCode, date('Y'), $regNumber, $applicant->full_name);
                    $uploaded[] = $docKey;
                }
            }

            if ($isPhysical) {
                $missing = PpdbFormField::active()->where('field_type', 'file')->whereNotIn('field_key', $uploaded)->pluck('field_key');
                foreach ($missing as $mKey) {
                    PpdbDocument::create(['applicant_id' => $applicant->id, 'document_type' => $mKey, 'verification_status' => 'pending', 'is_physical_submitted' => false]);
                }
            }

            return $applicant;
        });

        foreach ($jobs as $job) {
            dispatch($job);
        }

        return redirect()->to(tenant_route('applicant.register.success', ['reg' => $applicant->registration_number]))
            ->with('success', 'Pendaftaran Anda berhasil dikirim!');
    }

    public function success(Request $request): View|RedirectResponse
    {
        $regNumber = $request->query('reg') ?? session('registration_number');
        if (! $regNumber) {
            return redirect()->to(tenant_route('tenant.home'));
        }

        $applicant = PpdbApplicant::where('registration_number', $regNumber)->firstOrFail();
        $school = $this->tenantContext->getTenant();
        $waMsg = app(\App\Services\ApplicantSlipService::class)->buildWhatsAppText($applicant, $school);

        return view('pages.public.register_success', [
            'school' => $school,
            'applicant' => $applicant,
            'waUrl' => 'https://wa.me/?text='.urlencode($waMsg),
        ]);
    }
}

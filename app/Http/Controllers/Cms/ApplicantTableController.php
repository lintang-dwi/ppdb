<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\PpdbApplicant;
use App\Models\PpdbTrack;
use App\Models\PpdbWave;
use App\Services\ApplicantSlipService;
use App\Services\TenantContextService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicantTableController extends Controller
{
    public function __construct(
        protected TenantContextService $tenantContext,
        protected ApplicantSlipService $slipService
    ) {}

    public function index(Request $request): View
    {
        $school = $this->tenantContext->getTenant();
        $tracks = PpdbTrack::active()->orderBy('order_index')->get();
        $waves = PpdbWave::latest()->get();

        $query = PpdbApplicant::with(['wave', 'track', 'documents'])->latest();

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

        $applicants = $query->paginate(15)->withQueryString();

        $counts = [
            'all' => PpdbApplicant::count(),
            'submitted' => PpdbApplicant::where('status', 'submitted')->count(),
            'need_revision' => PpdbApplicant::where('status', 'need_revision')->count(),
            'verified' => PpdbApplicant::where('status', 'verified')->count(),
            'accepted' => PpdbApplicant::where('status', 'accepted')->count(),
            'completed' => PpdbApplicant::where('status', 'completed')->count(),
        ];

        return view('pages.cms.applicants_index', compact('applicants', 'tracks', 'waves', 'counts', 'school'));
    }

    public function show(PpdbApplicant $applicant): View
    {
        $school = $this->tenantContext->getTenant();
        $applicant->load(['wave', 'track', 'documents']);

        // Clean whatsapp phone number
        $cleanPhone = preg_replace('/[^0-9]/', '', $applicant->phone_whatsapp);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62'.substr($cleanPhone, 1);
        }

        $waMessage = urlencode("Halo Bapak/Ibu wali dari {$applicant->full_name} ({$applicant->registration_number}), kami dari Panitia PPDB {$school->name} ingin menginformasikan status pendaftaran Anda: ".strtoupper($applicant->status_label));
        $waUrl = "https://wa.me/{$cleanPhone}?text={$waMessage}";

        $fieldLabels = \App\Models\PpdbFormField::where('field_type', '!=', 'file')
            ->pluck('field_label', 'field_key')
            ->all();

        return view('pages.cms.applicant_detail', compact('applicant', 'school', 'waUrl', 'fieldLabels'));
    }
}

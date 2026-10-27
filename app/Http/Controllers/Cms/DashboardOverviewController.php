<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\PpdbApplicant;
use App\Models\PpdbTrack;
use App\Models\PpdbWave;
use App\Services\TenantContextService;
use Illuminate\View\View;

class DashboardOverviewController extends Controller
{
    public function __construct(
        protected TenantContextService $tenantContext
    ) {}

    public function __invoke(): View
    {
        $school = $this->tenantContext->getTenant();

        $activeWave = PpdbWave::active()->first();
        $tracksCount = PpdbTrack::active()->count();

        $counts = [
            'total' => PpdbApplicant::count(),
            'submitted' => PpdbApplicant::where('status', 'submitted')->count(),
            'need_revision' => PpdbApplicant::where('status', 'need_revision')->count(),
            'verified' => PpdbApplicant::where('status', 'verified')->count(),
            'accepted' => PpdbApplicant::where('status', 'accepted')->count(),
            'completed' => PpdbApplicant::where('status', 'completed')->count(),
            'cancelled' => PpdbApplicant::where('status', 'cancelled')->count(),
            'walk_in' => PpdbApplicant::where('registration_origin', 'offline_walk_in')->count(),
        ];

        return view('pages.cms.dashboard_overview', [
            'school' => $school,
            'activeWave' => $activeWave,
            'tracksCount' => $tracksCount,
            'counts' => $counts,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PpdbTrack;
use App\Models\PpdbWave;
use App\Services\TenantContextService;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __construct(
        protected TenantContextService $tenantContext
    ) {}

    public function __invoke(): View
    {
        $school = $this->tenantContext->getTenant();
        $now = now();

        $activeWave = PpdbWave::where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->first();

        $upcomingWave = null;
        if (! $activeWave) {
            $upcomingWave = PpdbWave::where('is_active', true)
                ->where('start_date', '>', $now)
                ->orderBy('start_date')
                ->first();
        }

        $tracks = PpdbTrack::where('is_active', true)
            ->orderBy('order_index')
            ->get();

        return view('pages.public.landing', [
            'school' => $school,
            'activeWave' => $activeWave,
            'upcomingWave' => $upcomingWave,
            'tracks' => $tracks,
            'isOpen' => (bool) $activeWave,
        ]);
    }
}

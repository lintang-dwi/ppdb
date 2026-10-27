<?php

namespace App\Http\Middleware;

use App\Models\PpdbWave;
use App\Services\TenantContextService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegistrationPeriodOpen
{
    public function __construct(
        protected TenantContextService $tenantContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->tenantContext->hasTenant()) {
            abort(Response::HTTP_NOT_FOUND, 'Sekolah tidak ditemukan.');
        }

        $now = now();
        $hasActiveWave = PpdbWave::where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->exists();

        if (! $hasActiveWave) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pendaftaran PPDB saat ini sedang ditutup atau belum dimulai.',
                ], Response::HTTP_FORBIDDEN);
            }

            return response()->view('pages.public.registration_closed', [
                'school' => $this->tenantContext->getTenant(),
                'upcomingWave' => PpdbWave::where('is_active', true)
                    ->where('start_date', '>', $now)
                    ->orderBy('start_date')
                    ->first(),
            ], Response::HTTP_OK);
        }

        return $next($request);
    }
}

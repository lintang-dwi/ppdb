<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePeriodSettingRequest;
use App\Http\Requests\UpdatePeriodSettingRequest;
use App\Models\PpdbWave;
use App\Services\TenantContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingPeriodController extends Controller
{
    public function __construct(
        protected TenantContextService $tenantContext
    ) {}

    public function index(): View
    {
        $waves = PpdbWave::latest('id')->paginate(15);

        return view('pages.cms.period_settings', [
            'school' => $this->tenantContext->getTenant(),
            'waves' => $waves,
        ]);
    }

    public function store(StorePeriodSettingRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        PpdbWave::create($validated);

        return redirect()->to(tenant_route('cms.waves.index'))
            ->with('success', 'Gelombang pendaftaran berhasil ditambahkan.');
    }

    public function update(UpdatePeriodSettingRequest $request, PpdbWave $wave): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $wave->update($validated);

        return redirect()->to(tenant_route('cms.waves.index'))
            ->with('success', 'Gelombang pendaftaran berhasil diperbarui.');
    }

    public function toggle(PpdbWave $wave): RedirectResponse
    {
        $wave->update(['is_active' => ! $wave->is_active]);

        $statusMsg = $wave->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->to(tenant_route('cms.waves.index'))
            ->with('success', "Status gelombang berhasil {$statusMsg}.");
    }
}

<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFormFieldRequest;
use App\Http\Requests\UpdateFormFieldRequest;
use App\Models\PpdbFormField;
use App\Models\PpdbWave;
use App\Services\FormPresetSeederService;
use App\Services\TenantContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormBuilderController extends Controller
{
    public function __construct(
        protected TenantContextService $tenantContext
    ) {}

    public function index(Request $request, FormPresetSeederService $presetService): View
    {
        $waveId = $request->input('wave_id');

        $query = PpdbFormField::ordered();
        if ($waveId) {
            $query->where(fn ($q) => $q->where('wave_id', $waveId)->orWhereNull('wave_id'));
        }

        $schoolCode = $this->tenantContext->getSchoolCode();
        $currentTier = $schoolCode ? $presetService->detectCurrentTier($schoolCode, $waveId ? (int) $waveId : null) : null;

        return view('pages.cms.form_builder', [
            'school' => $this->tenantContext->getTenant(),
            'fields' => $query->get()->groupBy('step'),
            'waves' => PpdbWave::active()->get(),
            'steps' => PpdbFormField::STEPS,
            'currentWaveId' => $waveId,
            'currentTier' => $currentTier,
            'currentTierLabel' => $presetService->getTierLabel($currentTier),
        ]);
    }

    public function store(StoreFormFieldRequest $request): RedirectResponse
    {
        PpdbFormField::create($this->prepareFieldData($request));

        return redirect()->to(tenant_route('cms.form-builder.index'))
            ->with('success', 'Field formulir berhasil ditambahkan.');
    }

    public function update(UpdateFormFieldRequest $request, PpdbFormField $field): RedirectResponse
    {
        $field->update($this->prepareFieldData($request));

        return redirect()->to(tenant_route('cms.form-builder.index'))
            ->with('success', 'Field formulir berhasil diperbarui.');
    }

    public function destroy(PpdbFormField $field): RedirectResponse
    {
        $field->delete();

        return redirect()->to(tenant_route('cms.form-builder.index'))
            ->with('success', 'Field formulir berhasil dihapus.');
    }

    public function toggle(PpdbFormField $field): RedirectResponse
    {
        $field->update(['is_active' => ! $field->is_active]);
        $statusMsg = $field->is_active ? 'diaktifkan' : 'disembunyikan';

        return redirect()->to(tenant_route('cms.form-builder.index'))
            ->with('success', "Field formulir berhasil {$statusMsg}.");
    }

    public function applyPreset(Request $request, FormPresetSeederService $presetService): RedirectResponse
    {
        $data = $request->validate([
            'tier' => ['required', 'string', 'in:paud_tk,sd_mi,smp_mts,sma_smk,smk,pesantren'],
            'wave_id' => ['nullable', 'integer', 'exists:ppdb_waves,id'],
            'replace_existing' => ['nullable', 'boolean'],
        ]);

        $count = $presetService->seedPreset(
            $this->tenantContext->getSchoolCode(),
            $data['tier'],
            $data['wave_id'] ?? null,
            $request->boolean('replace_existing', true)
        );

        $label = $presetService->getTierLabel($data['tier']);

        return redirect()->to(tenant_route('cms.form-builder.index'))
            ->with('success', "Template formulir {$label} berhasil diterapkan ({$count} field aktif).");
    }

    protected function prepareFieldData(StoreFormFieldRequest|UpdateFormFieldRequest $request): array
    {
        $data = $request->validated();
        $data['is_required'] = $request->boolean('is_required');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['options'] = $this->parseOptions($data['options'] ?? null);

        return $data;
    }

    protected function parseOptions(mixed $options): ?array
    {
        if (is_array($options)) {
            return array_values(array_filter(array_map('trim', $options)));
        }
        if (is_string($options) && trim($options) !== '') {
            return array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $options))));
        }
        return null;
    }
}

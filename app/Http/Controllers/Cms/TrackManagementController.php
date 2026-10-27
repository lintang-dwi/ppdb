<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrackRequest;
use App\Http\Requests\UpdateTrackRequest;
use App\Models\PpdbTrack;
use App\Services\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TrackManagementController extends Controller
{
    public function __construct(
        protected TenantContextService $tenantContext
    ) {}

    public function index(): View
    {
        $tracks = PpdbTrack::ordered()->get();

        return view('pages.cms.track_settings', [
            'school' => $this->tenantContext->getTenant(),
            'tracks' => $tracks,
        ]);
    }

    public function store(StoreTrackRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['slug'] = $this->generateUniqueSlug($validated['name']);

        PpdbTrack::create($validated);

        return redirect()->to(tenant_route('cms.tracks.index'))
            ->with('success', 'Jalur pendaftaran berhasil ditambahkan.');
    }

    public function update(UpdateTrackRequest $request, PpdbTrack $track): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($track->name !== $validated['name']) {
            $validated['slug'] = $this->generateUniqueSlug($validated['name'], $track->id);
        }

        $track->update($validated);

        return redirect()->to(tenant_route('cms.tracks.index'))
            ->with('success', 'Jalur pendaftaran berhasil diperbarui.');
    }

    public function toggle(PpdbTrack $track): RedirectResponse
    {
        $track->update(['is_active' => ! $track->is_active]);

        $statusMsg = $track->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->to(tenant_route('cms.tracks.index'))
            ->with('success', "Status jalur berhasil {$statusMsg}.");
    }

    public function reorder(Request $request): JsonResponse
    {
        $items = $request->validate([
            'order' => ['required', 'array'],
            'order.*.id' => ['required', 'integer'],
            'order.*.order_index' => ['required', 'integer'],
        ]);

        foreach ($items['order'] as $item) {
            PpdbTrack::where('id', $item['id'])->update(['order_index' => $item['order_index']]);
        }

        return response()->json(['success' => true]);
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (PpdbTrack::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}

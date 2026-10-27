<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\PpdbApplicant;
use App\Models\PpdbDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicantActionToggleController extends Controller
{
    public function updateStatus(Request $request, PpdbApplicant $applicant): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:submitted,need_revision,verified,accepted,completed,cancelled'],
            'verification_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($applicant, $validated) {
            $data = [
                'status' => $validated['status'],
                'verification_notes' => $validated['verification_notes'] ?? null,
            ];

            if ($validated['status'] === 'verified') {
                $data['verified_at'] = now();
                $data['verified_by'] = auth('ppdb')->user()?->name ?? 'Panitia';
            }

            $applicant->update($data);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $applicant->status,
                'status_label' => $applicant->status_label,
            ]);
        }

        return back()->with('success', "Status pendaftar berhasil diubah menjadi: {$applicant->status_label}");
    }

    public function verifyDocument(Request $request, PpdbDocument $document): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'verification_status' => ['required', 'in:pending,valid,invalid'],
            'rejection_reason' => ['nullable', 'string', 'max:255'],
            'flag_revision' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($document, $validated) {
            $document->update([
                'verification_status' => $validated['verification_status'],
                'rejection_reason' => $validated['verification_status'] === 'invalid' ? ($validated['rejection_reason'] ?? 'Berkas tidak sesuai/buram') : null,
            ]);

            if ($validated['verification_status'] === 'invalid' && ! empty($validated['flag_revision'])) {
                $document->applicant->update([
                    'status' => 'need_revision',
                    'verification_notes' => $validated['rejection_reason'] ?? 'Terdapat berkas yang perlu perbaikan.',
                ]);
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'verification_status' => $document->verification_status,
                'rejection_reason' => $document->rejection_reason,
            ]);
        }

        return back()->with('success', 'Status verifikasi berkas berhasil diperbarui.');
    }

    public function togglePhysicalDoc(PpdbDocument $document): RedirectResponse|JsonResponse
    {
        DB::transaction(function () use ($document) {
            $document->update([
                'is_physical_submitted' => ! $document->is_physical_submitted,
            ]);

            $applicant = $document->applicant;
            $allPhysical = $applicant->documents()->where('is_physical_submitted', false)->doesntExist();
            $applicant->update([
                'document_completeness' => $allPhysical ? 'complete_physical' : 'incomplete',
            ]);
        });

        return back()->with('success', 'Status penerimaan fisik berkas berhasil diubah.');
    }
}

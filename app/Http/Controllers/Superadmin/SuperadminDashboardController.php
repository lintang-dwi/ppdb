<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\PpdbApplicant;
use App\Models\PpdbUser;
use App\Models\PpdbWave;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SuperadminDashboardController extends Controller
{
    public function index(): View
    {
        $schools = School::orderBy('name')->get();

        $stats = [
            'total_schools' => $schools->count(),
            'total_waves' => PpdbWave::withoutGlobalScopes()->where('is_active', true)->count(),
            'total_applicants' => PpdbApplicant::withoutGlobalScopes()->count(),
            'total_verified' => PpdbApplicant::withoutGlobalScopes()->whereIn('status', ['verified', 'accepted', 'completed'])->count(),
            'total_gdrive_connected' => $schools->filter(fn ($s) => $s->hasGoogleDriveConnected())->count(),
        ];

        // Enrich schools with stats
        $schoolsData = $schools->map(function ($s) {
            return [
                'school' => $s,
                'applicants_count' => PpdbApplicant::withoutGlobalScopes()->where('school_id', $s->school_code)->count(),
                'active_wave' => PpdbWave::withoutGlobalScopes()->where('school_id', $s->school_code)->where('is_active', true)->first(),
                'gdrive_connected' => $s->hasGoogleDriveConnected(),
            ];
        });

        return view('pages.superadmin.dashboard', [
            'stats' => $stats,
            'schoolsData' => $schoolsData,
            'superadmin' => Auth::guard('superadmin')->user(),
        ]);
    }

    public function impersonate(School $school): RedirectResponse
    {
        $panitia = PpdbUser::where('school_id', $school->school_code)->first();

        if (! $panitia) {
            $panitia = PpdbUser::create([
                'school_id' => $school->school_code,
                'name' => 'Panitia PPDB ' . $school->name,
                'email' => 'admin@' . ($school->subdomain ?: $school->school_code) . '.sch.id',
                'password' => bcrypt(str()->random(24)),
            ]);
        }

        Auth::guard('ppdb')->login($panitia);

        session([
            'superadmin_impersonating' => true,
            'impersonated_school' => $school->school_code,
            'impersonated_school_name' => $school->name,
        ]);

        return redirect()->to(url("/{$school->school_code}/cms/dashboard"))
            ->with('success', "Mode Impersonate aktif: Mengakses panel CMS {$school->name}.");
    }

    public function leaveImpersonate(): RedirectResponse
    {
        Auth::guard('ppdb')->logout();

        session()->forget([
            'superadmin_impersonating',
            'impersonated_school',
            'impersonated_school_name',
        ]);

        return redirect()->route('superadmin.dashboard')
            ->with('success', 'Telah kembali ke dashboard Superadmin.');
    }

    public function updateSchool(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:500'],
            'favicon' => ['nullable', 'string', 'max:500'],
            'custom_domain' => ['nullable', 'string', 'max:255', 'unique:schools,custom_domain,' . $school->school_code . ',school_code'],
            'custom_domain_verified' => ['nullable', 'boolean'],
        ]);

        $school->update([
            'name' => trim($validated['name']),
            'logo' => !empty($validated['logo']) ? trim($validated['logo']) : null,
            'favicon' => !empty($validated['favicon']) ? trim($validated['favicon']) : null,
            'custom_domain' => !empty($validated['custom_domain']) ? strtolower(trim($validated['custom_domain'])) : null,
            'custom_domain_verified' => $request->boolean('custom_domain_verified'),
        ]);

        return redirect()->route('superadmin.dashboard')
            ->with('success', "Pengaturan white label untuk {$school->name} berhasil diperbarui.");
    }
}

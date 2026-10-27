<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Services\TenantContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected TenantContextService $tenantContext
    ) {}

    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::guard('ppdb')->check()) {
            return redirect()->to(tenant_route('cms.dashboard'));
        }

        return view('pages.cms.auth.login', [
            'school' => $this->tenantContext->getTenant(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['school_id'] = $this->tenantContext->getSchoolCode();
        $remember = $request->boolean('remember');

        if (Auth::guard('ppdb')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(tenant_route('cms.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('ppdb')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to(tenant_route('cms.login'));
    }
}

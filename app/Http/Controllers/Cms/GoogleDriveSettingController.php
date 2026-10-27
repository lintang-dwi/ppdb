<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\TenantContextService;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Oauth2;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class GoogleDriveSettingController extends Controller
{
    public function __construct(
        protected TenantContextService $tenantContext
    ) {}

    public function index(): View
    {
        $school = $this->tenantContext->getTenant();

        return view('pages.cms.google_drive_settings', compact('school'));
    }

    public function connect(): RedirectResponse
    {
        $client = $this->buildGoogleClient();
        $client->setState($this->tenantContext->getSchoolCode());

        return redirect()->away($client->createAuthUrl());
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->to(tenant_route('cms.gdrive.index'))
                ->with('error', 'Otentikasi Google Drive dibatalkan: '.$request->input('error'));
        }

        $code = $request->input('code');
        if (! $code) {
            return redirect()->to(tenant_route('cms.gdrive.index'))
                ->with('error', 'Kode otorisasi Google tidak ditemukan.');
        }

        $client = $this->buildGoogleClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            return redirect()->to(tenant_route('cms.gdrive.index'))
                ->with('error', 'Gagal mendapatkan token: '.($token['error_description'] ?? $token['error']));
        }

        $school = School::where('school_code', $this->tenantContext->getSchoolCode())->firstOrFail();

        $refreshToken = $token['refresh_token'] ?? null;
        if (! $refreshToken && ! $school->hasGoogleDriveConnected()) {
            return redirect()->to(tenant_route('cms.gdrive.index'))
                ->with('error', 'Google tidak mengembalikan refresh token. Mohon coba putus akun dari Google dan hubungkan ulang.');
        }

        $email = null;
        try {
            $oauth2 = new Oauth2($client);
            $email = $oauth2->userinfo->get()->email;
        } catch (\Throwable) {
            $email = 'Akun Google Terhubung';
        }

        $data = ['google_drive_email' => $email];
        if ($refreshToken) {
            $data['google_drive_refresh_token'] = $refreshToken;
        }

        $school->update($data);

        return redirect()->to(tenant_route('cms.gdrive.index'))
            ->with('success', "Google Drive sekolah ({$email}) berhasil dihubungkan!");
    }

    public function disconnect(): RedirectResponse
    {
        $school = School::where('school_code', $this->tenantContext->getSchoolCode())->firstOrFail();
        $school->update([
            'google_drive_refresh_token' => null,
            'google_drive_email' => null,
            'google_drive_root_folder_id' => null,
        ]);

        return redirect()->to(tenant_route('cms.gdrive.index'))
            ->with('success', 'Koneksi Google Drive sekolah berhasil diputus.');
    }

    protected function buildGoogleClient(): Client
    {
        $clientId = config('services.google_drive.client_id');
        $clientSecret = config('services.google_drive.client_secret');

        if (! $clientId || ! $clientSecret) {
            throw new RuntimeException('GOOGLE_DRIVE_CLIENT_ID atau GOOGLE_DRIVE_CLIENT_SECRET belum dikonfigurasi di server.');
        }

        $client = new Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri(tenant_route('cms.gdrive.callback'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->addScope([Drive::DRIVE, 'https://www.googleapis.com/auth/userinfo.email']);

        return $client;
    }
}

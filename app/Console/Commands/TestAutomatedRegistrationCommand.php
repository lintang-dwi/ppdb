<?php

namespace App\Console\Commands;

use App\Http\Controllers\Public\RegistrationWizardController;
use App\Http\Requests\StoreApplicantPublicRequest;
use App\Models\PpdbApplicant;
use App\Models\PpdbTrack;
use App\Models\PpdbWave;
use App\Models\School;
use App\Services\ApplicantSlipService;
use App\Services\FormTestAutomationService;
use App\Services\TenantContextService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TestAutomatedRegistrationCommand extends Command
{
    protected $signature = 'ppdb:test-registration {--school= : Kode sekolah tenant} {--count=1 : Jumlah pendaftaran}';
    protected $description = 'Automasi pengisian formulir PPDB untuk testing (berkas cukup dari notifikasi tanpa upload)';

    public function handle(
        TenantContextService $tenantService,
        FormTestAutomationService $testService,
        ApplicantSlipService $slipService
    ): int {
        $schoolCode = $this->option('school');
        $school = $schoolCode ? School::where('school_code', $schoolCode)->first() : School::first();

        if (! $school) {
            $this->error('Sekolah tidak ditemukan. Pastikan seeder telah dijalankan.');

            return self::FAILURE;
        }

        $tenantService->setTenant($school);
        $count = max(1, (int) $this->option('count'));

        $this->info("=== Memulai Testing Automasi Pendaftaran PPDB: {$school->name} ({$school->school_code}) ===");
        $this->comment("Catatan Berkas: {$testService->getDocumentTestNotice()}");

        for ($i = 1; $i <= $count; $i++) {
            $this->line("\n<fg=cyan>[Pengujian #{$i}/{$count}] Menyiapkan data form otomatis...</>");

            $wave = PpdbWave::where('school_id', $school->school_code)->where('is_active', true)->first();
            $track = PpdbTrack::where('school_id', $school->school_code)->where('is_active', true)->first();

            if (! $wave || ! $track) {
                $this->error('Wave aktif atau Track aktif tidak ditemukan untuk sekolah ini.');

                return self::FAILURE;
            }

            $payload = $testService->generatePayload($school, $wave, $track);

            $this->table(['Field Identitas', 'Nilai Terisi Otomatis'], [
                ['Nama Lengkap', $payload['full_name']],
                ['NIK', $payload['nik']],
                ['Jenis Kelamin', $payload['gender'] === 'L' ? 'Laki-laki' : 'Perempuan'],
                ['Tanggal Lahir', $payload['birth_date']],
                ['Nomor WhatsApp', $payload['phone_whatsapp']],
                ['Jalur Masuk', $track->name],
                ['Berkas Persyaratan', '<fg=yellow>Dilewati (Notifikasi Saja / Tanpa Unggah Berkas)</>'],
            ]);

            if (! empty($payload['custom_fields'])) {
                $customRows = [];
                foreach ($payload['custom_fields'] as $key => $val) {
                    $customRows[] = [$key, is_array($val) ? implode(', ', $val) : (string) $val];
                }
                $this->table(['Field Tambahan', 'Nilai Terisi'], $customRows);
            }

            $baseUrl = rtrim(config('app.url', 'http://localhost:8000'), '/');
            $subRequest = StoreApplicantPublicRequest::create(
                "{$baseUrl}/{$school->school_code}/daftar",
                'POST',
                $payload,
                [],
                [],
                ['HTTP_X_PPDB_TEST_MODE' => 'automated-testing']
            );
            $subRequest->headers->set('X-PPDB-Test-Mode', 'automated-testing');
            $subRequest->setContainer(app())->setRedirector(app('redirect'));
            app()->instance('request', $subRequest);

            try {
                $subRequest->validateResolved();
                $response = app(RegistrationWizardController::class)->store($subRequest);
            } catch (ValidationException $e) {
                $this->error('Validasi formulir gagal:');
                foreach ($e->errors() as $field => $messages) {
                    $this->line("  - <fg=red>{$field}:</> ".implode(', ', $messages));
                }
                return self::FAILURE;
            }

            if ($response->isRedirection()) {
                $applicant = PpdbApplicant::where('school_id', $school->school_code)->latest('id')->first();
                $dob = $applicant->birth_date instanceof \DateTimeInterface ? $applicant->birth_date->format('Y-m-d') : (string) $applicant->birth_date;
                $slipUrl = tenant_route('applicant.slip.download', ['reg' => $applicant->registration_number, 'dob' => $dob, 'preview' => 1]);
                $successUrl = tenant_route('applicant.register.success', ['reg' => $applicant->registration_number]);
                $waText = $slipService->buildWhatsAppText($applicant, $school);

                $this->info("✔ Berhasil! Notifikasi Pendaftaran Diterima.\n  - <fg=green>Nomor Registrasi :</> {$applicant->registration_number}\n  - <fg=green>Status Sistem    :</> {$applicant->status} ({$applicant->document_completeness})\n  - <fg=green>Portal Selesai   :</> {$successUrl}\n  - <fg=green>Link Bukti Slip  :</> {$slipUrl}\n  - <fg=green>Pesan WhatsApp   :</> ".Str::limit($waText, 80));
            } else {
                $this->error('Gagal mengirim form.');

                return self::FAILURE;
            }
        }

        $this->info("\nSemua skenario testing automasi pengisian form berhasil diselesaikan.");

        return self::SUCCESS;
    }
}

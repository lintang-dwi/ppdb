<?php

namespace Database\Seeders;

use App\Models\PpdbApplicant;
use App\Models\PpdbDocument;
use App\Models\PpdbTrack;
use App\Models\PpdbUser;
use App\Models\PpdbWave;
use App\Models\School;
use App\Services\FormPresetSeederService;
use App\Services\TenantContextService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PpdbDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Ensure Platform Superadmin exists
        \App\Models\PpdbSuperadmin::firstOrCreate(
            ['email' => 'superadmin@ppdb.cloud'],
            [
                'name' => 'Superadmin Platform PPDB',
                'password' => Hash::make('password123'),
            ]
        );

        // 1. Ensure Demo School exists in master registry
        $school = School::firstOrCreate(
            ['school_code' => 'sman1jakarta'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'SMAN 1 Jakarta',
                'subdomain' => 'sman1jkt',
                'custom_domain' => 'ppdb.sman1jkt.sch.id',
                'custom_domain_verified' => true,
                'theme' => 'default',
                'status' => 'active',
                'logo' => null,
            ]
        );

        // Bind tenant context
        $tenantService = app(TenantContextService::class);
        $tenantService->setTenant($school);

        // 2. Admission Wave
        $wave = PpdbWave::firstOrCreate(
            ['school_id' => $school->school_code, 'name' => 'Gelombang 1 - Reguler & Prestasi'],
            [
                'academic_year' => '2026/2027',
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(30),
                'announcement_date' => now()->addDays(35),
                'is_active' => true,
            ]
        );

        // 3. Admission Tracks
        $trackReguler = PpdbTrack::firstOrCreate(
            ['school_id' => $school->school_code, 'slug' => 'reguler'],
            [
                'name' => 'Reguler',
                'quota' => 120,
                'description' => 'Jalur umum seleksi administrasi dan berkas lengkap.',
                'is_active' => true,
                'order_index' => 1,
            ]
        );

        $trackPrestasi = PpdbTrack::firstOrCreate(
            ['school_id' => $school->school_code, 'slug' => 'prestasi'],
            [
                'name' => 'Prestasi',
                'quota' => 40,
                'description' => 'Jalur prestasi akademik dan non-akademik.',
                'is_active' => true,
                'order_index' => 2,
            ]
        );

        // 4. Committee Staff User
        PpdbUser::firstOrCreate(
            ['school_id' => $school->school_code, 'email' => 'panitia@sman1jkt.sch.id'],
            [
                'name' => 'Panitia PPDB SMAN 1',
                'password' => Hash::make('password123'),
            ]
        );

        // 5. Form Preset (SMA/SMK)
        $presetService = app(FormPresetSeederService::class);
        $presetService->seedPreset($school->school_code, FormPresetSeederService::TIER_SMA, $wave->id);

        // 6. Demo Applicant & Document
        $applicant = PpdbApplicant::firstOrCreate(
            ['registration_number' => 'REG-SMAN1-2026-0001'],
            [
                'school_id' => $school->school_code,
                'wave_id' => $wave->id,
                'track_id' => $trackReguler->id,
                'registration_origin' => 'online',
                'full_name' => 'Ahmad Fathoni',
                'nik' => '3171051508080001',
                'birth_date' => '2010-08-15',
                'birth_place' => 'Jakarta',
                'gender' => 'L',
                'phone_whatsapp' => '081234567890',
                'custom_fields' => [
                    'nisn' => '0081234567',
                    'previous_school_name' => 'SMP Negeri 1 Jakarta',
                    'major_choice_1' => 'IPA',
                    'major_choice_2' => 'IPS',
                    'is_colorblind' => 'Tidak Buta Warna',
                ],
                'document_completeness' => 'complete_digital',
                'status' => 'submitted',
            ]
        );

        PpdbDocument::firstOrCreate(
            ['applicant_id' => $applicant->id, 'document_type' => 'kk'],
            [
                'school_id' => $school->school_code,
                'original_file_name' => 'kartu_keluarga_ahmad.pdf',
                'drive_file_id' => 'mock_drive_file_id_01',
                'drive_view_url' => 'https://drive.google.com/file/d/mock_drive_file_id_01/view',
                'is_physical_submitted' => false,
                'verification_status' => 'pending',
            ]
        );
    }
}

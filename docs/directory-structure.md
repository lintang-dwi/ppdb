# Directory Structure

Code is grouped by audience (`Public` vs `Cms`) and by role (controller, service, job, component).

## `app/`

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Public/
│   │   │   ├── LandingController.php
│   │   │   ├── RegistrationWizardController.php
│   │   │   └── ApplicantStatusController.php
│   │   └── Cms/
│   │       ├── SettingPeriodController.php
│   │       ├── TrackManagementController.php
│   │       ├── FormBuilderController.php
│   │       ├── GoogleDriveSettingController.php
│   │       ├── ApplicantTableController.php
│   │       ├── ApplicantActionToggleController.php
│   │       └── WalkInDeskController.php
│   ├── Middleware/
│   │   ├── IdentifyTenantPpdb.php
│   │   └── EnsureRegistrationPeriodOpen.php
│   └── Requests/
│       ├── StoreApplicantPublicRequest.php
│       ├── StoreApplicantWalkInRequest.php
│       └── UpdatePeriodSettingRequest.php
├── Models/
│   ├── School.php
│   ├── PpdbWave.php
│   ├── PpdbTrack.php
│   ├── PpdbFormField.php
│   ├── PpdbApplicant.php
│   ├── PpdbDocument.php
│   └── PpdbUser.php
├── Scopes/
│   └── TenantScope.php
├── Services/
│   ├── TenantContextService.php
│   ├── RegistrationNumberService.php
│   ├── FormSchemaValidatorService.php
│   ├── FormPresetSeederService.php
│   ├── DocumentCompressionService.php
│   └── GoogleDriveClientService.php
├── Jobs/
│   ├── ProcessApplicantDocUploadJob.php
│   └── GenerateApplicantPdfCardJob.php
└── View/
    └── Components/
        ├── Public/
        │   ├── WizardStepper.php
        │   ├── DynamicInputRenderer.php
        │   └── SafetyProofModal.php
        └── Cms/
            ├── StatusToggleSwitch.php
            ├── TrackFilterTabs.php
            └── QuickActionMenu.php
```

## `resources/views/`

```text
resources/views/
├── layouts/
│   ├── ppdb_public.blade.php
│   └── ppdb_cms.blade.php
├── components/
│   ├── public/
│   │   ├── wizard-stepper.blade.php
│   │   ├── dynamic-input.blade.php
│   │   └── safety-modal.blade.php
│   └── cms/
│       ├── toggle-switch.blade.php
│       ├── track-tabs.blade.php
│       └── quick-action.blade.php
└── pages/
    ├── public/
    │   ├── landing.blade.php
    │   ├── register_step_container.blade.php
    │   └── status_tracker.blade.php
    ├── cms/
    │   ├── dashboard_overview.blade.php
    │   ├── period_settings.blade.php
    │   ├── track_settings.blade.php
    │   ├── form_builder.blade.php
    │   ├── google_drive_settings.blade.php
    │   ├── applicant_index.blade.php
    │   ├── applicant_detail.blade.php
    │   └── walk_in_form.blade.php
    └── pdf/
        └── applicant_card_slip.blade.php
```

## Responsibilities

### Controllers

| Class | Area | Responsibility |
| :--- | :--- | :--- |
| `LandingController` | Public | School PPDB landing page, open waves and tracks. |
| `RegistrationWizardController` | Public | Multi-step online registration. |
| `ApplicantStatusController` | Public | Status lookup, slip download, re-upload of rejected documents. |
| `SettingPeriodController` | CMS | CRUD for waves (`ppdb_waves`). |
| `TrackManagementController` | CMS | CRUD for tracks and quotas (`ppdb_tracks`). |
| `FormBuilderController` | CMS | Manage form fields (`ppdb_form_fields`). |
| `GoogleDriveSettingController` | CMS | Alur OAuth connect & disconnect mandiri akun Google Drive per sekolah (`/cms/settings/google-drive`). |
| `ApplicantTableController` | CMS | Applicant list and detail. |
| `ApplicantActionToggleController` | CMS | Inline status toggles (async PATCH). |
| `WalkInDeskController` | CMS | Walk-in desk intake (Mode Loket). |

### Middleware

| Class | Responsibility |
| :--- | :--- |
| `IdentifyTenantPpdb` | Detect the tenant from the CNAME host (`ppdb.sekolah.sch.id`) or the path prefix (`ppdb.bitserver.cloud/{school_code}`), then bind the `School` model to the singleton tenant manager. |
| `EnsureRegistrationPeriodOpen` | Allow public registration routes only when an active wave has `is_active = true` and the current time is between `start_date` and `end_date`. Otherwise redirect to the closed/info page. |

### Services

| Class | Responsibility |
| :--- | :--- |
| `TenantContextService` | Singleton holding the active tenant in memory. Getters for `school_code`, name, logo, and config, plus whether the request runs on a custom domain or the default path. |
| `RegistrationNumberService` | Generate `REG-{SCHOOL}-{YEAR}-{INDEX}` inside a transaction with `lockForUpdate()`. |
| `FormSchemaValidatorService` | Read the tenant's active `ppdb_form_fields` and build a Laravel `$rules` array to validate the `custom_fields` payload. |
| `FormPresetSeederService` | Hold the standard field blueprints (PAUD/TK, SD, SMP, SMA/SMK) and copy one into `ppdb_form_fields` when a school enables PPDB or picks an instant template. |
| `DocumentCompressionService` | Layer 2 compression (server). Shrinks images that are still large after browser compression or came from the walk-in desk, before Drive upload. |
| `GoogleDriveClientService` | Resolver dinamis kredensial Google Drive per-sekolah (`refresh_token` terenkripsi). Mengelola hirarki folder di Google Drive masing-masing sekolah (`PPDB_{YEAR}/{REG_NO}_{NAME}`), dan menangani unggah file multipart Drive API v3. |

### Jobs

| Class | Responsibility |
| :--- | :--- |
| `ProcessApplicantDocUploadJob` | Take the staged file from `storage/app/temp_ppdb/`, run server compression if it is not compressed yet, upload through `GoogleDriveClientService`, save `drive_file_id` and `drive_view_url` to `ppdb_documents`, then delete the local temp file. |
| `GenerateApplicantPdfCardJob` | Optional. Render the slip view to a PDF file on disk for async download or email attachment. |

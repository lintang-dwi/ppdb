# TECHNICAL SPECIFICATION & SYSTEM DOCUMENTATION

## 1. Complete Modular Directory Tree

The monolith architecture strictly organizes functional units into domain-specific controllers, services, components, and blade views:

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
├── pages/
│   ├── public/
│   │   ├── landing.blade.php
│   │   ├── register_step_container.blade.php
│   │   └── status_tracker.blade.php
│   ├── cms/
│   │   ├── dashboard_overview.blade.php
│   │   ├── period_settings.blade.php
│   │   ├── track_settings.blade.php
│   │   ├── form_builder.blade.php
│   │   ├── applicant_index.blade.php
│   │   ├── applicant_detail.blade.php
│   │   └── walk_in_form.blade.php
│   └── pdf/
│       └── applicant_card_slip.blade.php
```

---

## 2. Comprehensive Database Schema Specifications

### Table: `ppdb_waves` (Admission Periods & Waves)
* **Purpose:** Governs academic years, operational time windows, and emergency master switches.
* **Fields & Attributes:**
  * `id`: Primary Key, Big Integer, Unsigned, Auto Increment.
  * `school_id`: Foreign Key referencing `schools.school_code`, Varchar(50), Indexed.
  * `name`: Varchar(150) — Label for the wave (e.g., "Gelombang 1 - Jalur Reguler").
  * `academic_year`: Varchar(20) — Target admission year (e.g., "2026/2027").
  * `start_date`: DateTime — Opening timestamp for public registration.
  * `end_date`: DateTime — Scheduled closure timestamp.
  * `announcement_date`: DateTime, Nullable — Timestamp for results release.
  * `is_active`: Boolean, Default True — Emergency operational toggle switch.
  * `timestamps`: Created At & Updated At timestamps.
* **Indexes:** Composite index on `[school_id, is_active]`.

### Table: `ppdb_tracks` (Admission Tracks & Quotas)
* **Purpose:** Manages admission pathways within schools.
* **Fields & Attributes:**
  * `id`: Primary Key, Big Integer, Unsigned, Auto Increment.
  * `school_id`: Foreign Key referencing `schools.school_code`, Varchar(50), Indexed.
  * `name`: Varchar(100) — Name of track (e.g., "Zonasi", "Prestasi", "Reguler", "Tahfidz").
  * `slug`: Varchar(100) — URL/key identifier scoped to tenant.
  * `quota`: Integer, Nullable — Optional capacity limit.
  * `description`: Text, Nullable — Clear track conditions and physical document requirements.
  * `is_active`: Boolean, Default True — Quick toggle to close individual track.
  * `order_index`: Integer, Default 0 — Display order on frontend.
  * `timestamps`: Created At & Updated At timestamps.
* **Indexes:** Composite index on `[school_id, is_active]`.

### Table: `ppdb_form_fields` (Dynamic Form Configuration)
* **Purpose:** Dynamic configuration of form steps and fields per school.
* **Fields & Attributes:**
  * `id`: Primary Key, Big Integer, Unsigned, Auto Increment.
  * `school_id`: Foreign Key referencing `schools.school_code`, Varchar(50), Indexed.
  * `wave_id`: Foreign Key referencing `ppdb_waves.id`, Nullable, Big Integer.
  * `step`: Varchar(50) — Step grouping (`biodata`, `orang_tua`, `asal_sekolah`, `berkas`, `tambahan`).
  * `field_key`: Varchar(100) — Key identifier inside applicant JSON payloads.
  * `field_label`: Varchar(150) — Friendly Indonesian label displayed on UI.
  * `field_type`: Enum (`text`, `number`, `select`, `radio`, `checkbox`, `textarea`, `date`, `file`).
  * `options`: JSON, Nullable — Array of selectable values for select/radio types.
  * `is_required`: Boolean, Default False — Flags required vs. optional fields.
  * `is_active`: Boolean, Default True — Field visibility toggle.
  * `order_index`: Integer, Default 0 — Sort sequence within the step.
  * `timestamps`: Created At & Updated At timestamps.
* **Indexes:** Composite index on `[school_id, step, is_active]`.

### Table: `ppdb_applicants` (Student Applicant Master)
* **Purpose:** Stores core identity attributes, track choices, and dynamic custom field answers.
* **Fields & Attributes:**
  * `id`: Primary Key, Big Integer, Unsigned, Auto Increment.
  * `school_id`: Foreign Key referencing `schools.school_code`, Varchar(50), Indexed.
  * `wave_id`: Foreign Key referencing `ppdb_waves.id`, Big Integer, Indexed.
  * `track_id`: Foreign Key referencing `ppdb_tracks.id`, Nullable, Big Integer, Indexed.
  * `registration_number`: Varchar(50), Unique — Standardized ID (`REG-{SCHOOL}-{YEAR}-{INDEX}`).
  * `registration_origin`: Enum (`online`, `walk_in_offline`), Default `online`.
  * `full_name`: Varchar(255) — Full student legal name.
  * `nik`: Varchar(20) — National Identity Number, Indexed.
  * `birth_date`: Date — Date of birth (primary public status lookup credential).
  * `birth_place`: Varchar(100), Nullable — City/regency of birth.
  * `gender`: Enum (`L`, `P`), Nullable.
  * `phone_whatsapp`: Varchar(25) — Primary WhatsApp notification number.
  * `custom_fields`: JSON, Nullable — Dynamic field values parsed from form builder.
  * `document_completeness`: Enum (`complete_digital`, `complete_physical`, `incomplete`), Default `incomplete`.
  * `status`: Enum (`submitted`, `need_revision`, `verified`, `accepted`, `completed`, `cancelled`), Default `submitted`.
  * `verification_notes`: Text, Nullable — Direct committee feedback for applicant revisions.
  * `verified_at`: Timestamp, Nullable.
  * `verified_by`: Varchar(100), Nullable — Name or identifier of verifying staff member.
  * `timestamps`: Created At & Updated At timestamps.
* **Indexes:** Composite index on `[school_id, status]`, composite index on `[registration_number, birth_date]`.

### Table: `ppdb_documents` (File Upload Registry)
* **Purpose:** Tracks document files, physical submissions, and Google Drive links.
* **Fields & Attributes:**
  * `id`: Primary Key, Big Integer, Unsigned, Auto Increment.
  * `school_id`: Foreign Key referencing `schools.school_code`, Varchar(50), Indexed.
  * `applicant_id`: Foreign Key referencing `ppdb_applicants.id`, Big Integer, Cascade On Delete.
  * `document_type`: Varchar(50) — Key identifier (e.g., `kk`, `akta_kelahiran`, `ijazah`, `pas_foto`, `sertifikat`).
  * `original_file_name`: Varchar(255), Nullable.
  * `drive_file_id`: Varchar(255), Nullable — Google Drive File ID.
  * `drive_view_url`: Text, Nullable — Direct viewable preview URL from Google Drive.
  * `is_physical_submitted`: Boolean, Default False — Flags hardcopy delivered to school office.
  * `verification_status`: Enum (`pending`, `valid`, `invalid`), Default `pending`.
  * `rejection_reason`: Varchar(255), Nullable.
  * `timestamps`: Created At & Updated At timestamps.
* **Indexes:** Composite index on `[applicant_id, document_type]`.

### Table: `ppdb_users` (Committee Single-Tier Administrative Staff)
* **Purpose:** Single-tier authentication table for admissions staff.
* **Fields & Attributes:**
  * `id`: Primary Key, Big Integer, Unsigned, Auto Increment.
  * `school_id`: Foreign Key referencing `schools.school_code`, Varchar(50), Indexed.
  * `name`: Varchar(255) — Staff full name.
  * `email`: Varchar(255), Unique per school context.
  * `password`: Varchar(255) — Bcrypt password hash.
  * `remember_token`: Varchar(100), Nullable.
  * `timestamps`: Created At & Updated At timestamps.
* **Indexes:** Composite index on `[school_id, email]`.

---

## 3. Modular Feature Specifications

### Module A: Hybrid Dual-Entry Subsystem
1. **Public Self-Service Registration:**
   * Step 1: Select Track and review specific track conditions.
   * Step 2: Fill Core Student Identity & Parent/Guardian Details.
   * Step 3: Fill Dynamic Custom Fields (rendered via Blade component according to school setup).
   * Step 4: Document Attachments (marked as required or allow-deferred delivery).
   * Client-side image compression before upload staging.
2. **Walk-In Admission Desk (Mode Loket):**
   * Accessible exclusively inside the admin CMS (`/cms/ppdb/walk-in`).
   * Minimalist form with relaxed validation requirements for quick intake.
   * Direct checkbox for physical submission (`Berkas fisik diterima langsung`).
   * Instant trigger to print physical intake receipt slip for parents.

### Module B: Safety-Net Proof Generation
1. **Automated Document Slip Rendering:**
   * Built on a streamlined A4/A5 PDF layout containing school branding, applicant details, track, barcode/QR code verification hash, and status instructions.
2. **Instant Download Trigger:**
   * Client-side JavaScript injects an invisible anchor tag to trigger local PDF download upon successful form response without page reloads.
3. **Zero-Cost WhatsApp Share Trigger:**
   * Assembles a structured plain-text message containing applicant name, registration number, birth date, and direct status verification URL.
   * Launches `https://wa.me/?text={encoded_message}` in a separate tab or directly opens native mobile WhatsApp.

### Module C: Interactive Administrative Table & Toggles
1. **Inline Reactive Toggles:**
   * Panitia changes applicant status directly from table rows via Alpine.js switches.
   * Single-click toggles for: Document Validity (`Lengkap / Belum`), Admission Decision (`Diterima / Proses`), and Re-Registration (`Selesai / Belum`).
   * Background asynchronous patch request updates status without table reload.
2. **Revision Feedback Workflow:**
   * Rejection of specific document files opens a minimal modal dialog.
   * Panitia inputs clear, friendly instructions (e.g., "Foto Akta terpotong di bagian nomor registrasi, mohon unggah ulang").
   * Applicant entering the status portal is prompted to upload replacements exclusively for rejected items.

---

## 4. Preset Form Template Definitions

The `FormPresetSeederService` automatically seeds ready-to-use form configurations for four primary school tiers:

* **Tier 1: PAUD / TK (Early Childhood & Kindergarten):**
  * *Preset Fields:* Child Nickname, Sibling Order, Weight/Height, Special Medical Notes, Uniform Size (`S, M, L, XL`), Mother's WhatsApp.
  * *Document Rules:* All document uploads default to optional/deferred.
* **Tier 2: SD / Madrasah Ibtidaiyah (Elementary Level):**
  * *Preset Fields:* Full Legal Name, NIK, Family Card Number (No KK), Distance to School, Transportation Mode, Father & Mother Details.
  * *Document Rules:* KK and Akta Kelahiran marked mandatory; certificates optional.
* **Tier 3: SMP / Madrasah Tsanawiyah (Junior Secondary):**
  * *Preset Fields:* NISN, Previous School NPSN, Previous School Name, Average Report Score (Semesters 1–5), Uniform Size.
  * *Document Rules:* KK, Akta, and SKL/Surat Keterangan Lulus.
* **Tier 4: SMA / SMK (Senior Secondary & Vocational):**
  * *Preset Fields:* Major / Department Preference (1st & 2nd Choice), Cumulative Academic Score, Height & Colorblind Status (for technical majors).
  * *Document Rules:* KK, Akta, Graduation Certificate, Academic Report Card scans.
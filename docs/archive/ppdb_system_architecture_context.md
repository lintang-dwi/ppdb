# CONTEXT: PPDB MULTI-TENANT ENGINE (MONOLITH)

## 1. Project Background & Core Philosophy
* **Project Nature:** Standalone Multi-Tenant PPDB Engine built as a single Laravel 12 Monolith.
* **Core Design Philosophy:** **Adaptation Bridge / Transition Tool**. The platform does not enforce a rigid "Single Source of Truth." Instead, it bridges manual, physical admissions processes with modern digital self-service.
* **Dual-Entry Paradigm:**
  * **Online Self-Service:** Prospective students/parents fill out responsive multi-step wizards from home.
  * **Walk-In Desk (Mode Loket):** School staff use a streamlined administrative screen to input physical documents brought directly to the school office.
* **Relationship to Existing Infrastructure:** Operates as a distinct application codebase, sharing the primary multi-tenant database context (`saas_core`) and linking directly to the existing `schools` tenant registry.

---

## 2. Architectural Guardrails & Invariants
* **Application Paradigm:** Single-repo Laravel Monolith (Blade, Alpine.js, Tailwind CSS). No decoupled SPAs or external client-side frameworks.
* **Multi-Tenancy Isolation:**
  * Tenant Key: Primary string identifier (`school_code`, e.g., `sman1jakarta`).
  * Automatic Global Query Scope (`TenantScope`) bound to all domain models.
  * Tenant Resolution: Handled via specialized middleware supporting both platform default routing and external custom domain mapping.
* **Access Control & User Friction Elimination:**
  * **Applicants / Public:** Strict **zero-password friction**. Access to status, slips, and document revision uses only `registration_number` and `birth_date` (or NIK).
  * **Admissions Committee (Panitia):** Single-tier administrative level (`ppdb_users` scoped to `school_id`). No complex multi-tier role-permission matrices.
* **Document Management & Cloud Storage:**
  * Centralized Superadmin OAuth 2.0 Google Drive API v3 credentials stored globally in server environment variables.
  * Three-tiered automatic directory structure: `Root` -> `[school_code]` -> `[PPDB_YEAR]` -> `[REG_NO]_[APPLICANT_NAME]`.
  * Asynchronous document transmission via background queue jobs using Redis and Laravel Horizon. Zero synchronous external uploads during web requests.
* **Anti-Lost Slip Invariant (3-Layer Safety Net):**
  1. *Layer 1 (Direct Device Trigger):* Automatic silent client-side download trigger of the registration slip PDF upon successful submission.
  2. *Layer 2 (Zero-Cost WhatsApp Share):* Instant dynamic click-to-chat `wa.me` intent URL to self-send registration credentials and direct status lookup links.
  3. *Layer 3 (Desk Backup):* Committee quick-action tools on the administrative dashboard for one-click reprinting and instant WhatsApp resend.

---

## 3. Operational Model & Status Transitions
* **Hybrid Dynamic Form Builder:** Core identity columns stored directly in database rows; custom school-specific fields stored in structured dynamic JSON attributes.
* **Terminology Principle:** User interfaces must never expose raw database or programming terms. Use contextual language (e.g., "Wajib Diisi" vs. "Boleh Dikosongkan / Menyusul").
* **Admissions Flow Stages:**
  * `submitted`: Initial automated state assigned upon submission.
  * `need_revision`: Committee requests re-upload or clarification of blurred/missing files.
  * `verified`: Documents and physical files confirmed valid.
  * `accepted`: Student formally admitted to the quota.
  * `completed`: In-person re-registration, measurements, or fee settlements finished.
  * `cancelled`: Applicant withdrew or missed re-registration deadlines.
* **Table Action Protocol:** Status transitions are managed via interactive inline toggle switches on the administrative table, eliminating complicated Excel import cycles.
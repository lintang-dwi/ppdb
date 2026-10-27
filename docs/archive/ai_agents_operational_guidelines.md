# AGENTS INSTRUCTIONS & OPERATIONAL GUIDELINES

This document governs the operational rules, constraints, and coding standards for all AI Agents, automated assistants, and engineers contributing to this repository.

---

## 1. Absolute Constraints (Zero Tolerance Violations)

1. **NO Independent Frontend Frameworks:**
   * Never propose, initialize, or scaffold separate frontend applications (e.g., Vue SPA, React, Next.js, Nuxt).
   * All user interfaces must reside strictly within Laravel Blade templates enhanced by Alpine.js directives and Tailwind CSS utility classes.

2. **NO Traditional Password Registration for Applicants:**
   * Never create user tables, hashed password fields, or session auth guards for student applicants.
   * Applicant retrieval must strictly authenticate against `registration_number` combined with `birth_date` or `nik`.

3. **NO Synchronous Cloud Storage Uploads in HTTP Request Cycles:**
   * Uploaded files must be validated, staged to local temporary storage (`storage/app/temp_ppdb/`), and dispatched as background queue jobs.
   * HTTP endpoints must respond immediately without awaiting Google Drive API completion.

4. **NO Raw Unscoped Queries on Tenant Models:**
   * Never execute raw queries that bypass `TenantScope` on tenant-scoped tables (`ppdb_waves`, `ppdb_tracks`, `ppdb_form_fields`, `ppdb_applicants`, `ppdb_documents`).
   * Unscoped queries (`withoutGlobalScopes()`) are strictly restricted to middleware tenant resolution and uniqueness collision verifiers.

5. **NO Raw Code Dumps in Strategy or Architecture Mode:**
   * When explaining architecture or workflows, focus on design clarity, schema attributes, and structural boundaries.

---

## 2. File Length Thresholds & Decomposition Protocol

Every file in the codebase must strictly adhere to physical line limits to prevent monolithic god-objects and ensure rapid testability:

| Component Type | Maximum Line Limit | Decomposition Strategy |
| :--- | :--- | :--- |
| **Controllers** | 120 Lines | Delegate business logic to Services, move validation to FormRequests, offload workflows to Jobs. |
| **Service Classes** | 150 Lines | Restrict services to single responsibilities (e.g., Numbering, Schema Parsing, Drive Interaction). |
| **Eloquent Models** | 100 Lines | Keep models limited to cast definitions, relationships, and scopes. Move business queries to Services. |
| **Queue Jobs** | 120 Lines | Single task execution with explicit retry and failure handling. |
| **Blade Page Views** | 150 Lines | Extract form inputs, navigation steps, and modal dialogs into reusable sub-components. |
| **Blade UI Components** | 80 Lines | Focus on single, isolated UI primitives (e.g., input wrapper, toggle switch, badge). |
| **Alpine.js Components** | 80 Lines | Extract state objects and HTTP dispatch handlers into focused modular functions. |

---

## 3. Implementation Workflow

When generating or refactoring code:
1. **Check Tenant Scope:** Verify that `school_id` assignment occurs automatically behind the scenes.
2. **Preserve Dual URL Compatibility:** Generate internal links using the centralized tenant URL helper to preserve custom CNAME subdomains and default path prefixes.
3. **Verify 3-Layer Slip Safety:** Ensure every applicant creation route produces necessary parameters for automated downloads and WhatsApp share intents.
4. **Enforce Friendly Admin Vocabulary:** Ensure all administrative builder interfaces present layperson school terminology instead of software engineering terms.
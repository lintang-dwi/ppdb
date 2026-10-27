# PPDB Engine Agent Guide

Rules, limits, and coding standards for AI agents and engineers working in this repository.

## Read First
1. [`context.md`](context.md): what the system is, its architectural rules, and the status flow.
2. [`docs/database-schema.md`](docs/database-schema.md): tables, columns, indexes.
3. [`docs/directory-structure.md`](docs/directory-structure.md): where each class and view belongs.
4. [`docs/features.md`](docs/features.md): module behavior (dual entry, slip, admin table).
5. [`docs/form-presets.md`](docs/form-presets.md): preset forms per school level.

## Scope
- One Laravel 12 monolith: Blade + Alpine.js + Tailwind CSS.
- Shares the `saas_core` database and the `schools` tenant registry with the existing platform.
- PPDB domain only (admission waves, tracks, form builder, applicants, documents, committee users).

---

## 1. Hard Constraints (Zero Tolerance)

| # | Rule | Do | Don't |
| :- | :--- | :--- | :--- |
| 1 | **No separate frontend framework** | Build every screen in Blade, use Alpine.js for interactivity and Tailwind for styling. | Scaffold Vue, React, Next.js, Nuxt, or any SPA. |
| 2 | **No passwords for applicants** | Authenticate lookups with `registration_number` + `birth_date` (or `nik`). | Create applicant user tables, password hashes, or session guards. |
| 3 | **No synchronous cloud uploads** | Validate, stage to `storage/app/temp_ppdb/`, dispatch a queue job, respond immediately. | Wait for the Google Drive API inside an HTTP request. |
| 4 | **No unscoped queries on tenant models** | Rely on `TenantScope` for `ppdb_waves`, `ppdb_tracks`, `ppdb_form_fields`, `ppdb_applicants`, `ppdb_documents`. | Call `withoutGlobalScopes()` or raw SQL outside the allowed places below. |
| 5 | **No code dumps in architecture discussions** | Explain design, schema attributes, and boundaries. | Paste large code blocks when the question is about strategy. |
| 6 | **No numeric tenant key** | `school_id` is `VARCHAR(50)` referencing `schools.school_code` on every `ppdb_*` table. | Use `unsignedBigInteger('school_id')` or relate to `schools.id`. |
| 7 | **No strict status state machine in the CMS** | Let the committee flip toggles back and forth to fix misclicks. See [`context.md` §8](context.md#8-applicant-status-flow). | Reject a status change just because it is "out of order". |

**Allowed unscoped queries (rule 4):**
- Tenant resolution middleware (`IdentifyTenantPpdb`).
- Uniqueness and collision checks (for example registration number generation).

---

## 2. File Length Limits

Split a file before it crosses its limit.

| Component | Max Lines | How to Split |
| :--- | :---: | :--- |
| Controller | 120 | Business logic → Service, validation → FormRequest, long workflows → Job. |
| Service | 150 | One responsibility per service (numbering, schema parsing, Drive access). |
| Eloquent Model | 100 | Casts, relationships, and scopes only. Business queries → Service. |
| Queue Job | 120 | One task per job, with explicit `tries`, `backoff`, and `failed()` handling. |
| Blade Page | 150 | Extract form inputs, wizard steps, and modals into components. |
| Blade Component | 80 | One UI primitive (input wrapper, toggle, badge). |
| Alpine.js Component | 80 | Move state objects and HTTP handlers into small functions. |

---

## 3. Implementation Checklist

Run through this list every time you write or refactor code:

- [ ] **Tenant scope:** `school_id` is filled automatically (model event / scope), never passed by hand from the request.
- [ ] **Dual URL support:** internal links go through the central tenant URL helper so both custom domains (CNAME) and the default path prefix keep working.
- [ ] **3-layer slip safety:** every route that creates an applicant returns what the auto-download and the WhatsApp share link need. See [`context.md` §4](context.md#4-anti-lost-slip-invariant-3-layer-safety-net).
- [ ] **Plain admin wording:** admin screens use school vocabulary, not technical terms. See [`context.md` §6](context.md#6-ui-terminology).
- [ ] **Transactions:** see §4 below.
- [ ] **Compression:** images are compressed in the browser and again by `DocumentCompressionService` if still large. See [`context.md` §5](context.md#5-documents-and-storage).
- [ ] **File limits:** no file crosses the limits in §2.

---

## 4. Required DB Transactions

Wrap these in `DB::transaction`:

| Process | Requirement |
| :--- | :--- |
| Registration number generation | Read the last sequence with `lockForUpdate()` to prevent race conditions. |
| Saving a registration | `ppdb_applicants` insert, `ppdb_documents` insert, and the release of temporary local files happen in one transaction. |
| Multi-table status changes | Any status change that also touches documents or other tables. |

---

## 5. Where Things Go

| Need | Location |
| :--- | :--- |
| Public pages (landing, wizard, status) | `app/Http/Controllers/Public/` + `resources/views/pages/public/` |
| Committee CMS screens | `app/Http/Controllers/Cms/` + `resources/views/pages/cms/` |
| Request validation | `app/Http/Requests/` |
| Business logic | `app/Services/` |
| Slow or external work (Drive upload, PDF) | `app/Jobs/` |
| Tenant filtering | `app/Scopes/TenantScope.php` |
| Reusable UI | `app/View/Components/` + `resources/views/components/` |

Full tree: [`docs/directory-structure.md`](docs/directory-structure.md).

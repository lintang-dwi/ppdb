# PPDB Multi-Tenant Engine

Online and walk-in student admission (PPDB) for many schools in one Laravel 12 app.

Parents register from home through a step-by-step form. Staff can enter applicants who come to the school office in person. Both end up in the same table, and the committee manages them from one dashboard.

## Main Features
- **Two ways to register:** online wizard for parents, walk-in desk (Mode Loket) for staff.
- **No applicant passwords:** status lookup uses registration number + birth date.
- **Slip that doesn't get lost:** auto-download PDF, WhatsApp self-share, reprint from the dashboard.
- **Per-school form builder:** presets for PAUD/TK, SD/MI, SMP/MTs, SMA/SMK, editable per school.
- **Inline status toggles:** the committee updates document, admission, and re-registration status straight from the table.
- **Google Drive mandiri per-sekolah (BYOD):** setiap sekolah menghubungkan akun Google Drive sendiri via OAuth; berkas diunggah di latar belakang dengan token terenkripsi.

## Tech Stack

| Layer | Technology |
| :--- | :--- |
| Backend | Laravel 12 (monolith) |
| UI | Blade, Alpine.js, Tailwind CSS |
| Database | MySQL (shared `saas_core`) |
| Queue | Redis + Laravel Horizon |
| Storage | Google Drive API v3 |
| PDF | Blade view → PDF (`pages/pdf/applicant_card_slip.blade.php`) |

## Documentation

| File | Contents |
| :--- | :--- |
| [`AGENTS.md`](AGENTS.md) | Rules and limits for AI agents and contributors |
| [`context.md`](context.md) | System purpose, architecture rules, status flow |
| [`docs/directory-structure.md`](docs/directory-structure.md) | Folder tree for `app/` and `resources/views/` |
| [`docs/database-schema.md`](docs/database-schema.md) | All `ppdb_*` tables, columns, indexes |
| [`docs/features.md`](docs/features.md) | Module specs: dual entry, slip, admin table |
| [`docs/form-presets.md`](docs/form-presets.md) | Preset form fields per school level |
| [`docs/implementation-plan.md`](docs/implementation-plan.md) | Build phases P0–P12, decisions, risks |
| [`docs/archive/`](docs/archive/) | Original drafts, kept for history only. Not the source of truth. |

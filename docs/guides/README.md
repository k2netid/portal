> **Source of Truth:** `ja-core_engine/docs/guides/` — mirrored copy. Do not edit here.
> Re-sync: `bash /home/jejakawan/dev/ja-core_engine/scripts/sync-docs-guides-reference-downstream.sh`

# Guides (How-to)

Prosedur singkat untuk tugas umum di **ja-core_engine**.  
Bukan penjelasan arsitektur — untuk “kenapa”, lihat `docs/architecture/` dan ADR.

| Guide | Kapan dipakai |
| :--- | :--- |
| [Start a task](start-a-task.md) | Audit → rencana → implement → verify → catat |
| [Run quality gates](run-quality-gates.md) | Sebelum merge / tutup task agen |
| [Sync RBAC & capabilities](sync-rbac-and-capabilities.md) | Setelah ubah permission / manifest capability |
| [Seed theme demo data](seed-theme-demo-data.md) | Isi data demo generik per tema |
| [Apply install profile](apply-install-profile.md) | Pilih bentuk produk core / cms / cms_site |
| [Add optional module](add-optional-module.md) | Scaffold + aktifkan pack first-party |
| [Add theme customizer setting](add-theme-customizer-setting.md) | Tambah setting di host atau paket tema |
| [Add i18n keys](add-i18n-keys.md) | Paritas `id` / `en` / `su` + `i18n:check` |
| [Security checks](security-checks.md) | Audit deps, gate route, member/API smoke |
| [Run Playwright E2E](run-e2e-playwright.md) | Smoke Docker/full-stack, matrix npm, captcha bypass |
| [Update changelogs](update-changelog.md) | Dual-layer: modul/tema + root |
| [Release & versioning](release-and-versioning.md) | SemVer produk, API `v1`, OpenAPI, cut release |
| [Sync docs downstream](sync-docs-downstream.md) | Mirror guides/reference → k2net / smkn6 / ja-cms |

Kebijakan: [`DOCUMENTATION.md`](../DOCUMENTATION.md) · Tutorial onboarding: [`AGENT_START_HERE.md`](../AGENT_START_HERE.md) · Contributing: [`CONTRIBUTING.md`](../../CONTRIBUTING.md).

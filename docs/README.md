# Dokumentasi Resmi Jejakawan Core Engine (`ja-core_engine`)

Pusat navigasi arsitektur, standar pemrograman, dan panduan teknis.

> **Kebijakan SoT / SoC / Diátaxis:** [`DOCUMENTATION.md`](DOCUMENTATION.md) — baca sebelum menambah dokumen.  
> **Catatan fork:** Repo ini master kernel untuk aplikasi downstream. Mulai dari [AGENT_START_HERE.md](AGENT_START_HERE.md).

> **Guides / Reference:** salinan mirror dari upstream — [guides/](guides/README.md) · [reference/](reference/README.md). SoT: `ja-core_engine`. Sync: `npm run docs:sync-downstream` di core.

---

## Mulai di sini

| Dokumen | Deskripsi |
| :--- | :--- |
| [**DOCUMENTATION**](DOCUMENTATION.md) | Lapisan SoT, Diátaxis, aturan ADR, SoC core vs tema vs tenant |
| [**COMPLETENESS**](COMPLETENESS.md) | Checklist fondasi docs (apa yang sudah/ belum) |
| [**WORKFLOW**](WORKFLOW.md) | Alur kerja agen: audit → diskusi → rencana → implement → verify → catat |
| [**Work log**](work/README.md) | Brief tugas aktif + status |
| [**AGENT_START_HERE**](AGENT_START_HERE.md) | Cerita fork, branch strategy, aturan agent |
| [**Guides (how-to)**](guides/README.md) | Prosedur: verify, RBAC, theme seed, modul, customizer |
| [**Reference**](reference/README.md) | Katalog modul, CLI, indeks kontrak L0 |
| [**Branching**](branching.md) | `main` + `integrate/*` + `feat`/`fix` |
| [**Architectural status**](architectural-status.md) | Kernel + CMS packs / `/site` on `main` |
| [**Downstream apps & licensing**](product/downstream-apps-and-licensing.md) | Hub JA-CP, OAuth IdP, pola extend kernel |
| [**Bootstrap downstream app**](product/bootstrap-downstream-app.md) | Scaffold modul produk dari kernel |

---

## Aspek pemrograman (pintu masuk)

| Aspek | Explanation | How-to / Reference |
| :--- | :--- | :--- |
| Domain & modul | [01 Overview](architecture/01-overview-and-tier-design.md) | [modules catalog](reference/modules.md) |
| Backend | [02 Backend standards](architecture/02-backend-standards.md) | `backend/Modules/*/README.md` |
| Frontend | [03 Frontend standards](architecture/03-frontend-standards.md) | `frontend/src/modules/*/README.md` |
| Theme & customizer | [04 Theme system](architecture/04-theme-system.md) | [add setting](guides/add-theme-customizer-setting.md) · [naming](../frontend/src/modules/Layout/customizer/naming-conventions.md) · [themes](themes/README.md) |
| i18n | [05 i18n](architecture/05-i18n-guidelines.md) | [add i18n keys](guides/add-i18n-keys.md) |
| Security | [06 Security](architecture/06-security-and-governance.md) | [security checks](guides/security-checks.md) |
| Extensions & packs | [Module contract](extensions/module-contract.md) · [Lifecycle](extensions/lifecycle.md) | [add module](guides/add-optional-module.md) · [schema](extensions/module-manifest.schema.json) |
| RBAC / capabilities | [RBAC seeders](extensions/rbac-and-lifecycle-seeders.md) · ADR-020/021 | [sync RBAC](guides/sync-rbac-and-capabilities.md) |
| Identity (3-tier) | ADR-014…016 | — |
| Install profile | [install-profiles](extensions/install-profiles.md) | [apply profile](guides/apply-install-profile.md) |
| Upstream / seeders | [ADR-022](adr/ADR-022-upstream-core-curation-and-generic-theme-seeder-architecture.md) | [theme:seed](guides/seed-theme-demo-data.md) |
| Quality gates | `AGENTS.md` | [run gates](guides/run-quality-gates.md) · [CLI ref](reference/cli-commands.md) |

---

## Arsitektur & keputusan

| Dokumen | Deskripsi |
| :--- | :--- |
| [**01. Overview & Architecture**](architecture/01-overview-and-tier-design.md) | Modular monolith, tier System / Infra / Security |
| [**02. Backend Standards**](architecture/02-backend-standards.md) | Laravel patterns, FormRequest, services, tests |
| [**03. Frontend Standards**](architecture/03-frontend-standards.md) | Vue 3 SPA, Pinia, engine layout |
| [**04. Theme System**](architecture/04-theme-system.md) | Host vs theme package, SoC/SoT customizer |
| [**05. i18n Guidelines**](architecture/05-i18n-guidelines.md) | Paritas `id` / `en` / `su` |
| [**06. Security & Governance**](architecture/06-security-and-governance.md) | CSP, ABAC, passkeys, SIEM |
| [**Data Studio vs CCK**](architecture/data-studio-vs-cck.md) | Batas Data Studio vs editorial CMS |
| [**ADRs**](adr/README.md) | Keputusan arsitektur (core + pointer tema/downstream) |
| [**Themes index**](themes/README.md) | Indeks tema + ADR Layung |
| [**HTTP / OpenAPI**](reference/http-api.md) | Scramble + dynamic OpenAPI |
| [**Guides**](guides/README.md) | How-to prosedur |
| [**Reference**](reference/README.md) | Katalog modul, CLI, kontrak |
| [**ja-CE audit (2026-08-27)**](audit/ja-ce-comprehensive-audit-2026-08-27.md) | Honesty map W1–W5 |
| [**Upstream sync audit (2026-09-07)**](audit/AUDIT-2026-09-07-codebase-curation-and-upstream-sync.md) | Kurasi multi-repo |

---

## Extensions

| Dokumen | Deskripsi |
| :--- | :--- |
| [**Module contract**](extensions/module-contract.md) | Kontrak first-party optional (Mail golden sample) + JSON Schema |
| [**Extension lifecycle**](extensions/lifecycle.md) | Dual boot, deactivate vs uninstall, public identity |
| [**Install profiles**](extensions/install-profiles.md) | `core` / `cms` / `cms_site` |
| [**Member Area RFC**](extensions/member-area.md) | Portal pembaca adaptif terhadap pack aktif |
| [**RBAC & seeders RFC**](extensions/rbac-and-lifecycle-seeders.md) | Enable/disable, audit seeder, CMS roles gap |
| [**External module packaging**](extensions/external-module-packaging.md) | Path/VCS/in-tree packs; CMS extract order |
| [**ja-mail**](extensions/ja-mail.md) | Modul Mail |

---

## Arsip / tenant docs

Dokumen usang atau tenant-specific **tidak disimpan di core**. Lihat:

- K2NET: `k2net-portal/docs/adr/ADR-009-...`, `k2net-portal/docs/audit/`
- Sarangenge / school history: `smkn6-portal/docs/feat_sarangenge/` (dan `smkn1cijulang-portal`) — bukan SoT `ja-cms` / `k2net-portal`

---

## Lainnya

- [**CONTRIBUTING**](../CONTRIBUTING.md) — cara berkontribusi + DoD
- [**Root README**](../README.md) — instalasi dev & quality gates
- [**CHANGELOG**](../CHANGELOG.md) — riwayat rilis (root; detail di modul/tema)
- [**AGENTS.md**](../AGENTS.md) — pedoman singkat agent AI

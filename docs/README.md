# Dokumentasi Resmi Jejakawan Core Engine (`ja-core_engine`)

Selamat datang di pusat dokumentasi arsitektur, standar pemrograman, dan panduan teknis **Jejakawan Core Engine**.

> **Catatan fork:** Repo ini berasal dari `ja-cms`, direfaktor menjadi **master kernel** untuk aplikasi downstream. Baca [AGENT_START_HERE.md](AGENT_START_HERE.md) dan [architectural-status.md](architectural-status.md) sebelum kontribusi.

---

## Mulai di sini

| Dokumen | Deskripsi |
| :--- | :--- |
| [**AGENT_START_HERE**](AGENT_START_HERE.md) | Cerita fork, branch strategy, aturan agent |
| [**Branching**](branching.md) | `main` + `integrate/*` + `feat`/`fix` |
| [**Architectural status**](architectural-status.md) | Kernel `main` + CMS packs / `/site` di `integrate/cms` |
| [**Downstream apps & licensing**](product/downstream-apps-and-licensing.md) | Hub JA-CP, OAuth IdP, pola extend kernel |
| [**Bootstrap downstream app**](product/bootstrap-downstream-app.md) | Scaffold modul produk dari kernel |
| [**Module contract**](extensions/module-contract.md) | Kontrak first-party optional (Mail golden sample) + JSON Schema |
| [**External module packaging**](extensions/external-module-packaging.md) | P2: path/VCS/in-tree packs tanpa fork kernel; CMS extract order |

---

## Arsitektur

| Dokumen | Deskripsi |
| :--- | :--- |
| [**01. Overview & Architecture**](architecture/01-overview-and-tier-design.md) | Modular monolith, tier System / Infra / Security |
| [**ja-CE comprehensive audit (2026-08-27)**](architecture/ja-ce-comprehensive-audit-2026-08-27.md) | Docs↔kode, bugs, refine — bahan diskusi pre-P5 |
| [**02. Backend Standards**](architecture/02-backend-standards.md) | Laravel patterns, FormRequest, services, tests |
| [**03. Frontend Standards**](architecture/03-frontend-standards.md) | Vue 3 SPA, Pinia, engine layout |
| [**05. i18n Guidelines**](architecture/05-i18n-guidelines.md) | Paritas `id` / `en` / `su` |
| [**06. Security & Governance**](architecture/06-security-and-governance.md) | CSP, ABAC, passkeys, SIEM |

---

## Lainnya

- [**Root README**](../README.md) — instalasi dev & quality gates
- [**CHANGELOG**](../CHANGELOG.md) — riwayat rilis
- [**AGENTS.md**](../AGENTS.md) — pedoman singkat agent AI

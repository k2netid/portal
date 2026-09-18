---
id: 2026-09-18-docs-system-restructure
title: Restrukturisasi sistem dokumentasi (SoT/SoC/Diátaxis + guides/reference)
status: done
scale: L
repo: ja-core_engine
owner: agent
created: 2026-09-18
updated: 2026-09-18
blocks: []
related_adr: []
---

# Task: Restrukturisasi sistem dokumentasi

## 1. Tujuan

Menjadikan `docs/` menggambarkan proyek dari berbagai aspek pemrograman dengan SoT berlapis, SoC core/tema/tenant, Diátaxis (tutorial/how-to/reference/explanation), dan alignment ke codebase — plus alur kerja agen yang dapat diaudit.

## 2. Audit (current state)

- ADR campur core/tema/tenant; `feat_sarangenge/` usang; audit K2NET di core.
- Dominan explanation/ADR; how-to & reference terindeks hampir tidak ada.
- Theme system L2 hilang (`04`); naming conventions L0 ada di kode tanpa pintu docs yang jelas.

## 3. Diskusi / keputusan

- Pertahankan nomor ADR; theme ADR → `docs/themes/`; ADR-009 SoT di `k2net-portal`.
- ADR-018 tetap core (plugin platform).
- `_archive` dihapus setelah konfirmasi duplikat di downstream.
- Generator OpenAPI: Scramble + `dynamic:openapi` (sudah ada) — didokumentasikan, bukan diganti.

## 4. Plan

### In scope

- [x] `DOCUMENTATION.md`, restruktur folder, stubs ADR
- [x] `guides/` + `reference/`
- [x] i18n/security how-to, HTTP API ref, link checker CI, sync downstream
- [x] `WORKFLOW.md` + templates + `docs/work/`

### Out of scope

- Full rewrite semua module README
- OpenAPI generator baru dari nol

## 5. Log proses

| Waktu | Agen/sesi | Catatan |
| :--- | :--- | :--- |
| 2026-09-18 | Cursor agent | Audit + diskusi urutan SoT/Diátaxis |
| 2026-09-18 | Cursor agent | Implement restruktur + guides/reference |
| 2026-09-18 | Cursor agent | i18n/security/OpenAPI docs + link CI + sync k2net/smkn6 |
| 2026-09-18 | Cursor agent | WORKFLOW + templates + brief ini |

## 6. Hasil

- Perilaku / docs: sistem dokumentasi berlapis + how-to/reference + workflow agen + dual-layer changelog (modul/tema/root) + CONTRIBUTING
- File kunci: `docs/DOCUMENTATION.md`, `docs/WORKFLOW.md`, `docs/guides/`, `docs/reference/`, `docs/guides/update-changelog.md`, `CONTRIBUTING.md`, theme `CHANGELOG.md`, `scripts/check-docs-links.mjs`
- Gate: `npm run docs:links` OK
- Follow-up: commit ketiga repo bila user minta

## 7. Status akhir

`done` — fondasi docs + workflow + changelog policy siap dipakai task berikutnya.

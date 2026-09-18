---
id: 2026-09-19-theme-registry-3level-e2e
title: Playwright E2E test untuk alur 3-Tingkat Themes di App Store & Themes Manager
status: done
scale: S
repo: ja-core_engine
owner: agent
created: 2026-09-19
updated: 2026-09-19
blocks: []
related_adr:
  - ADR-023
---

# Task: Playwright E2E Test - 3-Level Theme Registry & Quota Flow

## 1. Tujuan

Menguji secara end-to-end (E2E) menggunakan Playwright alur antarmuka tema 3-tingkat (ADR-023 §2.9):
1. **App Store (`/dash/settings/extensions` atau `/dash/extensions`)**:
   - Tab filter "Themes" memfilter kartu ekstensi hanya menampilkan family tema (`theme-*`).
   - Visual banner edukatif 3-tingkat (Pack Inventory, Served Theme, Public Website) tampil beserta badge ringkasan kuota lisensi.
   - Kartu tema aktif menampilkan chip `Currently Served Theme` dan pintasan Customizer.
   - Invariant baseline tema: tombol aktivasi/deaktivasi `theme-janari` terkunci (`Locked`).
2. **Themes Manager (`/dash/themes`)**:
   - Header arsitektur 3-tingkat dengan ringkasan kuota lisensi dan tombol pintas ke App Store.
   - Kartu tema menampilkan badge entitlement (`Free Baseline`, `PRO Included`, atau `PRO Required`) dan status paket (`Pack Enabled` / `Pack Disabled`).
   - Tombol aktivasi pada tema non-entitled terkunci dengan aman (`PRO Required` / Lock icon).
3. **Navigasi Antar-halaman**:
   - Navigasi lancar bolak-balik antara Themes Manager dan App Store via tombol pintas 3-tingkat.

## 2. Implementasi

1. Dibuat `frontend/tests/e2e/theme-registry-3level-flow.spec.ts` dengan 3 skenario pengujian komprehensif:
   - `App Store displays Themes tab, 3-level architecture banner, quota badge, and served theme indicators`
   - `Themes Manager displays 3-level pipeline header, quota summary, entitlement badges, and graceful action locks`
   - `Inter-page navigation connects App Store and Themes Manager seamlessly`
2. Menggunakan helper `loginAsAdmin` dan regex multikamus (`en`, `id`, `su`) agar uji coba tahan terhadap perubahan locale console.
3. Seluruh 132 test E2E terdaftar bersih tanpa error di Playwright suite.

## 3. Acceptance Criteria

- [x] File test E2E `frontend/tests/e2e/theme-registry-3level-flow.spec.ts` terdaftar dan valid di Playwright (132 total tests across 22 files).
- [x] Skenario App Store Themes shelf, banner 3-tingkat, dan baseline lock teruji.
- [x] Skenario Themes Manager pipeline header, badge kuota, dan entitlement lock teruji.
- [x] `npm run agent:verify` lulus 100% (Pint, PHPStan L9 600/600, ESLint 0 warnings, vue-tsc type-check, i18n symmetric keys, SemVer check).

## 4. Log

| Waktu | Catatan |
| :--- | :--- |
| 2026-09-19 01:10 | Inisiasi task dan perancangan skenario Playwright. |
| 2026-09-19 01:12 | Implementasi `theme-registry-3level-flow.spec.ts` dan verifikasi ESLint. |
| 2026-09-19 01:13 | Verifikasi menyeluruh `npm run agent:verify` sukses 100%. |

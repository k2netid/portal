---
id: 2026-09-19-theme-registry-console-ux-3level
title: Console UI/UX Themes shelf di App Store + 3 tingkat visual & badge kuota lisensi
status: done
scale: M
repo: ja-core_engine
owner: agent
created: 2026-09-19
updated: 2026-09-19
blocks: []
related_adr:
  - ADR-023
---

# Task: Console UI/UX Themes shelf & 3-Level Visual Contract

## 1. Tujuan

Menyempurnakan antarmuka pengguna Console (App Store / Extensions & Themes Manager) sesuai **ADR-023 §2.9**:
1. Menampilkan shelf dan tab **Themes** terpisah di App Store (`/dash/settings/extensions`).
2. Menampilkan visual edukatif **3 Tingkat Status Tema** (Pack Inventory → Served Theme → Public Site).
3. Menampilkan **Badge & Indikator Kuota Lisensi** tema per tier (Community: Janari only; Pro: maks 1 premium; Enterprise: all).
4. Menyediakan indikasi **Currently Served** pada kartu tema di App Store dengan tombol pintas ke Customizer/Themes.
5. Memperjelas status entitlement dan pencegahan aktivasi pada Themes Manager (`/dash/themes`) sebelum request dikirim ke backend.

## 2. Audit (Kondisi Awal)

- Di backend, 4 theme pack (`theme-janari`, `theme-layung`, `theme-sarangenge`, `theme-sareupna`) sudah terdaftar dengan `type: 'theme'` dan `family: 'theme'` (ADR-023 Fase 1).
- Di App Store FE (`frontend/src/modules/Core/System/views/settings/extensions/Index.vue`), `filterTabs` dan `FAMILY_ORDER` belum memasukkan family `theme`. Tema akan jatuh ke fallback grouping atau tercampur.
- Belum ada representasi visual 3-tingkat (Pack Registry → Served Theme → Public Site).
- Di Themes manager FE (`frontend/src/modules/Layout/views/themes/Index.vue`), kartu tema hanya menampilkan `Active` vs `Inactive` tanpa status pack inventory atau peringatan license quota.

## 3. Rencana Perubahan

1. **Backend**:
   - `ThemeController::index()`: lampirkan metadata kuota lisensi (`quota: { tier, max_premium_active, remaining_slots }`) dan per-theme flag (`is_pack_enabled`, `is_entitled`, `is_premium`).
   - `ExtensionController::index()`: tandai atribut `is_served` pada theme pack extension.
2. **Frontend App Store (`Index.vue` & `ExtensionCard.vue`)**:
   - Tambahkan tab `Themes` (`value: 'theme'`).
   - Tambahkan shelf `Themes` dengan badge kuota lisensi.
   - Komponen callout / banner edukatif 3-tingkat status tema.
   - Pada kartu tema: icon `Palette`, badge `Currently Served` + quick action ke Customizer.
3. **Frontend Themes Manager (`views/themes/Index.vue`)**:
   - Header banner arsitektur 3-tingkat & ringkasan kuota lisensi.
   - Indikator entitlement (`Free`, `PRO Required`, `Pack Inactive`).
4. **i18n**: Tambahkan kamus bahasa (`en`, `id`, `su`).
5. **Verifikasi**: `npm run agent:verify` + visual validation di browser.

## 4. Acceptance Criteria

- [x] Tab dan shelf "Themes" muncul di App Store (`/dash/settings/extensions`).
- [x] Visual banner 3-tingkat tampil jelas menjelaskan: (1) Pack Inventory, (2) Served Theme, (3) Public Site.
- [x] Badge kuota lisensi menampilkan kuota yang tepat sesuai tier aktif.
- [x] Kartu tema di App Store menandai tema yang sedang `Currently Served`.
- [x] Di Themes manager, tema yang tidak entitled (e.g. premium pada tier Community) menampilkan badge `PRO Required` dan tombol disable yang informatif.
- [x] `npm run agent:verify` (frontend lint, type-check, unit tests, e2e list, backend pint, phpstan level 9) hijau 100%.

## 5. Log

| Waktu | Catatan |
| :--- | :--- |
| 2026-09-19 | Audit dan penyusunan brief implementasi UI/UX Themes 3-tingkat & kuota lisensi |
| 2026-09-19 | Implementasi backend ThemeController & ExtensionController + unit tests di ThemePackRegistryTest |
| 2026-09-19 | Implementasi frontend App Store (tab, shelf, banner 3-tingkat, kuota badge, ExtensionCard theme support) & Themes Manager (pipeline header, card entitlement badges, lock guards) + i18n 3 bahasa |
| 2026-09-19 | Seluruh verifikasi quality gates (`npm run agent:verify`: Pint, PHPStan L9 600/600, ESLint 0 warning, vue-tsc type-check, 315 unit tests, 129 e2e tests listed, docs links, SemVer checks) berhasil 100% hijau |


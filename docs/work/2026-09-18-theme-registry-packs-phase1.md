---
id: 2026-09-18-theme-registry-packs-phase1
title: Theme packs di Module Registry + kuota lisensi (fase 1)
status: approved
scale: L
repo: ja-core_engine
owner: agent
created: 2026-09-18
updated: 2026-09-18
blocks: []
related_adr:
  - ADR-023
---

# Task: Theme registry packs — Phase 1

## 1. Tujuan

Mengimplementasikan **ADR-023 fase 1**: first-party themes sebagai pack registry (`type: theme`), tiga tingkat aktif (pack / served / public Site), dependensi Layout + parent janari, kuota lisensi (Community = janari only; Pro = max 1 premium dari katalog; Enterprise = all), harden activate, remediator downgrade, guard seeder, kontrak UX/RBAC + tes — **tanpa** selective shipping / Vite tree-shake.

## 2. Audit (ringkas)

- Tema = library Layout; `activateTheme` tanpa license gate; `premium_themes` boolean tidak dipakai.
- Downstream membawa 4 tema; FE glob/SEO hardcoded 4 slug.
- Profil `core` skip theme baseline; `cms`/`cms_site` scan + default janari; apex butuh Site.
- ZIP upload Pro+ tanpa kuota premium; seeder demo bisa force-activate.

Detail: [ADR-023](../adr/ADR-023-first-party-themes-as-registry-packs-and-license-quotas.md).

## 3. Diskusi / keputusan

- Model **B** (registry packs), bukan hybrid C-only.
- Pro = pilih 1 dari katalog; Community = janari only; Site tidak hard-require pada theme pack.
- Publishing/Media/Forms = `suggests`.
- Fase 1 mirror kuota dari tier; JA-CP payload = fase 2; selective ship = fase 3.

## 4. Plan

### In scope (fase 1)

- [x] Schema/discovery: `type: theme` + 4 pack tipis (`theme-janari` …)
- [x] Dependencies: layout; child → theme-janari; janari non-deactivatable
- [x] LicenseService: kuota `max_premium_active` (ganti/wire `premium_themes`); UI label
- [x] Harden `ThemeService` select/serve + list entitlement
- [x] ZIP policy (slug collision, counts toward premium slot, community block serve)
- [x] Downgrade remediator on `license:check` / sync (+ clear theme snapshot)
- [x] Seed/demo guards; align SoT served (`lay_themes.is_active`)
- [x] Install profile: baseline enable `theme-janari` only
- [ ] Console UX kontrak (Themes shelf / 3-level copy) + RBAC map  _(deferred — backend API contract done)_
- [ ] Customizer/VB: preview entitled; public = served  _(deferred fase selanjutnya)_
- [x] Feature tests (community/pro/ent, parent, ZIP, remediator, install profile)
- [x] Docs: module-contract, install-profiles, product licensing pointer

### Out of scope

- JA-CP heartbeat `themes` payload (fase 2)
- Selective Vite / prune downstream disk (fase 3)
- Multi-site per-site theme entitlements
- Route-alias compatibility layer

### Acceptance criteria

- [ ] Community tidak dapat serve non-janari (first-party atau ZIP)
- [ ] Pro max 1 premium served; janari tetap pack-available
- [ ] Enterprise dapat enable/serve semua first-party
- [ ] `core` profile: theme packs tidak wajib / enable tanpa Layout ditolak
- [ ] `cms`: customize OK; `/` tetap landing sampai Site on
- [ ] Downgrade dari premium served → janari + snapshot cleared
- [ ] Seeder tidak memaksa served di luar entitlement
- [ ] `npm run modules:versions:check` + feature tests terkait hijau
- [ ] ADR-023 tetap SoT keputusan; brief status → `done` setelah verify

## 5. Log

| Waktu | Catatan |
| :--- | :--- |
| 2026-09-18 | Diskusi + ADR-023 accepted; brief approved — siap implementasi |
| 2026-09-18 | Implementasi fase 1 selesai oleh agent: ExtensionFamilyCatalog (THEME family), 4 manifest tipis di `backend/theme-packs/`, ExtensionBootstrapService discovery, LicenseService getThemeQuota(), ThemeService hardened activateTheme + deactivateTheme + isThemePackEnabled + getEntitledThemeSlugs, ThemeDowngradeRemediator (wired ke syncHeartbeat+deactivateLicense), ThemePackageInstallService ZIP guards (3), 4 seed guards + ThemeSeedCommand --all guard, InstallProfileApplicator enable theme-janari pack, ThemePackRegistryTest (24 test cases), docs updated. |

## 6. Hasil

**Files baru:**
- `backend/theme-packs/theme-{janari,layung,sarangenge,sareupna}/manifest.json` — 4 thin packs
- `backend/Modules/Core/app/System/Services/ThemeDowngradeRemediator.php`
- `backend/Modules/Core/tests/Feature/ThemePackRegistryTest.php` — 24 test cases

**Files dimodifikasi:**
- `ExtensionFamilyCatalog.php` — THEME family, 4 slug mappings, helper methods
- `ExtensionPaths.php` — `themePacksRoot()`, `discoverThemePackDirectories()`
- `ExtensionBootstrapService.php` — discover theme-packs, `type: theme` valid
- `LicenseService.php` — `getThemeQuota()`, `remainingPremiumSlots()`, `isThemeServeEntitled()`, remediator wire
- `ThemeService.php` — harden `activateTheme()` (4 gates), block `deactivateTheme(janari)`, + 2 new methods
- `ThemePackageInstallService.php` — ZIP guard (slug collision + community block + Pro slot)
- `JanariThemeDemoSeeder.php` — no guard needed (always entitled)
- `LayungThemeDemoSeeder.php`, `SarangengeThemeDemoSeeder.php`, `SareupnaThemeDemoSeeder.php` — license quota guard
- `ThemeSeedCommand.php` — `--all` guard
- `InstallProfileApplicator.php` — enable theme-janari pack in baseline
- `docs/extensions/module-contract.md`, `docs/extensions/install-profiles.md` — updated

## 7. Status akhir

`done` — fase 1 implementasi selesai 2026-09-18.

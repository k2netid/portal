---
status: accepted
scope: core
date: 2026-09-18
related:
  - ADR-011
  - ADR-012
  - ADR-016
  - ADR-017
  - ADR-022
---

# ADR-023: First-party themes as Module Registry packs + license quotas

**Status:** Accepted — Phase 1 ✅ implemented (2026-09-18) · Phase 2 (JA-CP payload) + Phase 3 (selective shipping) pending  
**Tanggal:** 2026-09-18  
**Author:** Jejakawan Core Engineering  
**Scope:** Module Registry (`sys_extensions`), Layout theme host, `LicenseService` / JA-CP, install profiles, console App Store / Themes UI  
**Related:** ADR-011 / ADR-012 (extension license gating), ADR-016 (module-aware identity), ADR-017 (console license UI), ADR-022 (theme seeders)

---

## 1. Konteks

Hari ini:

- First-party themes (`janari`, `layung`, `sarangenge`, `sareupna`) adalah **library file** di bawah Layout (`views/themes/<slug>/`), di-scan ke `lay_themes`, dan hampir selalu **ikut di setiap downstream / Vite bundle**.
- Module Registry mengatur **module/plugin** saja (`type: module | plugin`). Tema tidak punya baris registry.
- `LicenseService` punya fitur boolean `premium_themes` (dan UI yang menyesatkan), tetapi **`ThemeService::activateTheme` tidak mengecek lisensi**.
- Engine modular: profil `core` / `cms` / `cms_site` — tema baseline hanya relevan bila Layout (CMS family) aktif; apex publik butuh Site.

Kebutuhan produk:

- Community → **janari only**
- Pro → **janari selalu tersedia** + **pilih 1 tema premium dari katalog**
- Enterprise / white_label → semua first-party
- Downstream jangka panjang tidak wajib membawa semua tema (fase shipping terpisah)

---

## 2. Keputusan

### 2.1 Model: opsi B — theme packs di Module Registry

Setiap first-party theme adalah extension tipis:

| Slug | Peran |
| :--- | :--- |
| `theme-janari` | Baseline / parent; always-on inventory |
| `theme-layung` | Premium catalog |
| `theme-sarangenge` | Premium catalog |
| `theme-sareupna` | Premium catalog |

- Manifest `type` diperluas: `module` \| `plugin` \| **`theme`** (atau ekuivalen yang diindeks registry dengan diskriminator jelas).
- Pack **tipis**: manifest + pointer ke tree `views/themes/<slug>/` — **bukan** full nwidart module boilerplate ala Mail.
- SoT SemVer tema tetap `theme.json` (+ theme `CHANGELOG.md`); baris registry **mirror** versi. `npm run modules:versions:check` tetap memakai path theme.

Layout (`layout`) tetap host: scan, customizer, `activateTheme` (served), public theme API.

### 2.2 Tiga tingkat “aktif” (wajib dipisah)

| Tingkat | Arti | Syarat |
| :--- | :--- | :--- |
| **Pack enabled** | Inventory registry (`sys_extensions.status = active`) | Dependensi pack terpenuhi + kuota lisensi |
| **Frontend selected / served** | Satu baris `lay_themes.is_active` (tema publik) | Pack tema itu enabled + Layout on (+ parent pack enabled) |
| **Public served at `/`** | Pengunjung melihat tema | Pack **Site** active (`cms_site`) |

SoT untuk “tema mana yang dilayani publik”: **`lay_themes.is_active`** (bukan dual `theme_active` setting — migrasi/align ke satu SoT di implementasi).

### 2.3 Dependensi pack

```
library → publishing → layout → site
                         ├── theme-janari     requires: [layout]
                         │                     suggests: [publishing, media, forms]
                         └── theme-*premium*  requires: [layout, theme-janari]
                                               suggests: [publishing, media, forms]
```

| Aturan | Alasan |
| :--- | :--- |
| Theme packs **tidak** `requires: site` | Profil `cms` boleh customize tanpa apex publik |
| Theme packs **tidak** hard-require `publishing` | Shell theme boleh; konten degrade lembut |
| Child **requires `theme-janari`** | `parent_theme: janari` fallback views/settings/i18n |
| `theme-janari` tidak boleh di-deactivate | Parent + Community baseline |
| Profil `core` | Theme packs tidak di-baseline; enable ditolak tanpa Layout |

Install profile:

- `core` — skip theme packs
- `cms` / `cms_site` — enable **`theme-janari`** saja + `ensureDefaultFrontendTheme`; child tidak auto-enable

### 2.4 Kuota lisensi

| Tier | `always_on` | `max_premium_active` |
| :--- | :--- | ---: |
| community / starter* | `janari` | `0` |
| pro | `janari` | `1` |
| enterprise / white_label | `janari` | unlimited (`null`) |

\*Starter mengikuti matrix fitur existing; bila belum dapat premium themes, perlakukan seperti community untuk kuota tema sampai JA-CP membedakan.

- **Premium** = first-party non-janari **atau** uploaded ZIP yang di-serve (lihat §2.5).
- Janari **tidak** mengisi slot Pro.
- Pro “pilih 1 dari katalog”: enable/serve maksimal satu premium; ganti = deactivate/unserve yang lama lalu enable yang baru (swap atomic di API disarankan).

**Fase 1:** mirror kuota dari `license_type` di `LicenseService` (ganti boolean `premium_themes` yang menyesatkan).  
**Fase 2:** JA-CP heartbeat/activate payload:

```json
"themes": {
  "always_on": ["janari"],
  "max_premium_active": 1,
  "catalog": ["janari", "layung", "sarangenge", "sareupna"]
}
```

Missing fields → fallback mirror tier (aman: community = janari only).

### 2.5 Dual channel (ZIP upload)

- Kuota berlaku pada tema **yang di-serve**, semua channel.
- ZIP **tidak** boleh memakai slug first-party.
- Community: tidak boleh serve ZIP.
- Pro: ZIP yang di-serve menghitung slot premium (bersaing dengan first-party premium).
- Upload tetap butuh `theme_upload` (Pro+) seperti hari ini.

### 2.6 Harden runtime (hari ini soft)

- `ThemeService::activateTheme` / select served: **hard** require Layout product-active + theme pack enabled + parent pack enabled (bila child).
- Dependensi tema yang hanya di-log → jadi blocker.
- `license:check` / sync lisensi menjalankan **serve remediator** (lihat §2.7).

### 2.7 Downgrade / remediator

Jika served slug di luar entitlement (mis. site pakai `layung` lalu tier jadi community):

1. Set served → `janari` (atau grace period singkat + banner console — default: force janari).
2. Clear `frontend_theme_snapshot_v1` (+ broadcast aktivasi yang sudah ada).
3. Notifikasi console (license / themes).

Berlaku untuk first-party dan ZIP.

### 2.8 Seed / demo

- `theme:seed` / `*ThemeDemoSeeder` **tidak** boleh memaksa served di luar entitlement.
- `--all` ditolak atau di-no-op untuk slug non-entitled pada Community.
- Seed content hanya jika pack **available** (enabled atau at least present + entitled to enable).

### 2.9 Console UX & RBAC

- Shelf / section **Themes** terpisah dari Modules/Plugins; copy menjelaskan 3 tingkat (pack → select/serve → Site on).
- Permission: activate `type=theme` memakai gate yang sama dengan `manage themes` (+ license), bukan “any sanctum admin”.
- Customizer / Visual Builder: boleh preview pack yang entitled; iframe publik hanya **served**. Ganti pilihan Pro = settings per-tema tetap; **no auto-merge**.

### 2.10 Member / host plugins / forms

- Served theme harus menyediakan member chrome **atau** hard-fallback ke janari tokens/components.
- Host plugins (`cinematic-nav`, dll.) membaca settings **served** theme saja.
- `contact_form_slug`: resolve slug tema served atau fallback `contact`.

---

## 3. Fase implementasi

| Fase | Isi | Bukan |
| :--- | :--- | :--- |
| **1 — Registry + gate** | Theme packs tipis, discovery, deps, kuota mirror tier, harden activate, remediator, seed guards, RBAC/UX kontrak, tests | Tree-shake Vite / prune downstream |
| **2 — JA-CP** | Payload `themes.*` di heartbeat/activate | — |
| **3 — Selective shipping** | Bootstrap / sync allowlist; Vite dynamic loaders | Wajib di fase 1 |

**Constraint fase 1:** first-party boleh tetap ada di disk + bundle (`BUNDLED_FRONTEND_THEME_SLUGS`, SEO imports, glob). Entitlement mengatur **serve/enable**, bukan keberadaan file.

---

## 4. Eksplisit di luar scope (defer)

- Per-site theme entitlement (multi_site / white_label)
- Selective disk sync / slim community tarball
- Route-alias compatibility saat ganti tema (`/solusi`, `/guru`, …)
- Pack-scoped sitemap/robots
- Menjadikan tiap theme full nwidart Laravel module dengan providers

---

## 5. Konsekuensi

**Positif:** SKU tema selaras lisensi; App Store/registry satu mental model; profil modular (`core` tanpa tema) konsisten; jalan menuju shipping selektif.

**Negatif / biaya:** refactor discovery + UI; dual state pack vs served harus dididik ke operator; fase 1 belum mengurangi ukuran fork; butuh tes downgrade/ZIP/kuota.

**Invariants:**

1. `theme-janari` selalu inventory-on bila Layout on.  
2. Hanya satu tema **served** per instance.  
3. Apex publik butuh Site; inventory tema tidak.  
4. Parent pack ≠ served theme.

---

## 6. Referensi kode (current state)

- `LicenseService::getFeaturesMatrix` / heartbeat — `backend/Modules/Core/app/System/Services/LicenseService.php`
- `ThemeService::activateTheme` / `scanThemes` — `backend/Modules/Layout/app/Services/ThemeService.php`
- `ThemePackageInstallService` — upload ZIP
- `InstallProfileApplicator` — theme baseline
- `FrontendLayout.vue`, `themeViewResolver.ts` (`BUNDLED_FRONTEND_THEME_SLUGS`)
- `scripts/sync-frontend-assets-to-backend.sh`
- `docs/extensions/install-profiles.md`, `docs/product/downstream-apps-and-licensing.md`

Work brief fase 1: [`docs/work/2026-09-18-theme-registry-packs-phase1.md`](../work/2026-09-18-theme-registry-packs-phase1.md)

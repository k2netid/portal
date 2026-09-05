# ADR-005: Generalisasi Menyeluruh Referensi Brand Tenant & Platform

**Status:** Accepted / Implemented  
**Tanggal:** 2026-09-05  
**Author:** Jejakawan Engineering  
**Scope:** Seluruh codebase `smkn6-portal` — frontend, backend, locales, tests, dan sample data  
**Supersedes / Extends:** [ADR-004](./ADR-004-multi-theme-soc-cross-theme-isolation-and-identity-generalization.md), [ADR-001](./ADR-001-dynamic-school-identity-and-vhost-port.md), [ADR-002](./ADR-002-app-name-vs-site-name-identity-separation.md)

---

## 1. Konteks & Permasalahan

Setelah implementasi Multi-Theme SoC (ADR-004) dan Pemisahan Identitas (ADR-002), audit komprehensif menunjukkan bahwa platform masih mengandung **residu hardcode** dari dua sumber:

### A. Referensi Tenant — SMKN 6 Bandung (Sarangenge Theme)
1. **P0 — Hardcoded Email**: `info@smkn6bandung.sch.id` sebagai fallback aktif di `useSarangengeIdentity.ts:66` dan `bundle.json`.
2. **P1 — Sidebar Navigation Leak**: Label `"Pricing Page"` bocor dari skema Layung ke customizer Sarangenge.
3. **P1 — Plugin Locale Examples**: String contoh `smkn6bandung` di 3 file `plugin.json` (id/en/su).
4. **P2 — Code Comment**: Komentar `SMKN 6 Bandung` di `Hero.vue`.

### B. Referensi Platform Owner — K2NET (Layung Theme)
1. **~70 file** masih mengandung hardcode `K2NET`, `k2net.id`, `PT Kirana Karina Network`:
   - Identity composable (`useLayungIdentity.ts`)
   - Email defaults (`resolveLayungLocalizedCopy.ts`)  
   - Store/marketplace URLs (`layungStoreUrls.ts`)
   - Locale files (3 bahasa × ~44 referensi per file)
   - Schema defaults (`schema.settings.json`, `theme.json`)
   - 24 Vue components (13 sections + 11 pages)
   - Backend blade/test/composer files
   - Core System locales
   - 8 test fixture files

### C. Defensive Gap
- `findThemeViewKey()` di `themeViewResolver.ts` menjatuhkan fallback ke **semua** slug tema bawaan (`BUNDLED_FRONTEND_THEME_SLUGS`) saat array kandidat kosong, melanggar prinsip isolasi tema.

---

## 2. Keputusan Arsitektur

### A. Generalisasi Sarangenge (Tema Pendidikan)

| Komponen | Sebelum | Sesudah |
|----------|---------|---------|
| `useSarangengeIdentity.ts` L66 | `return 'info@smkn6bandung.sch.id'` | Resolve dari `systemStore.siteSettings.contact_email` → fallback `'info@portal.sch.id'` |
| `bundle.json` (sample data) | Data spesifik SMKN 6 (nama, alamat, telepon, WA, email, body pages) | Data generik "Portal Sekolah Vokasi" — alamat/telepon/WA dikosongkan |
| `sidebar.navigation.json` | `"Pricing Page"` | `"Admissions Page"` |
| `plugin.json` (id/en/su) | Contoh: `smkn6bandung` | Contoh: `instansi_anda` / `your_organization` / `instansi_anjeun` |
| `Hero.vue` comment | `// SMKN 6 Bandung` | `// Sarangenge Theme (Education Archetype)` |

### B. Generalisasi Layung (Tema Korporat ISP/MSP)

| Komponen | Sebelum | Sesudah |
|----------|---------|---------|
| **Identity defaults** | `PT Kirana Karina Network`, `IDNIC-K2NET-ID`, `/logofull_k2net.png` | `PT Penyedia Layanan Internet`, `IDNIC-ISP-ID`, `/logo.png` |
| **Email defaults** | `info@k2net.id`, `cs@k2net.id`, `sales@k2net.id`, `billing@k2net.id` | `info@portal.net`, `cs@portal.net`, `sales@portal.net`, `billing@portal.net` |
| **Store URLs** | Tokopedia/Shopee K2NET links | Empty strings (configurable via customizer) |
| **Locale files** (id/en/su) | `K2NET` brand name di ~44 strings per file | `Kami` (generic first-person) |
| **Schema & theme.json** | K2NET defaults di semua email/brand fields | Generic portal defaults |
| **WhatsApp prefill** | `'Halo K2NET, saya ingin...'` | `'Halo, saya ingin...'` |
| **Vue components** (24 files) | K2NET fallback strings di `t()` calls | Generic ISP/provider terms |
| **Backend files** | `k2net.id` URLs, K2NET brand | `jejakawan.com` / `Portal` |
| **Test fixtures** (8 files) | K2NET assertions | Generic Portal assertions |

### C. Perbaikan Defensive Gap

```diff
- const slugs = themeSlugs.length > 0 ? themeSlugs : [...BUNDLED_FRONTEND_THEME_SLUGS]
+ // Defensive: never leak sibling themes
+ const slugs = themeSlugs.length > 0 ? themeSlugs : ['janari']
```

Fallback sekarang hanya ke `janari` (universal parent), memastikan tidak ada kebocoran view lintas tema sibling.

---

## 3. Strategi Penggantian

- **Composables & identity files**: Edit manual presisi untuk memastikan logic resolution cascade tetap benar.
- **Locale & schema JSON**: Bulk `sed` replacement dengan pola:
  - `K2NET` → `Kami` (locale context)
  - `k2net.id` → `portal.net`
  - `PT Kirana Karina Network` → `Penyedia Layanan Internet`
- **Vue components**: Bulk `sed` pada fallback strings di `t()` calls.
- **Backend mirror**: Sync setelah semua frontend changes.
- **Test fixtures**: Update assertions agar match dengan generalized strings.

---

## 4. Verifikasi

| Check | Hasil |
|-------|-------|
| `vue-tsc --noEmit` | ✅ 0 errors |
| `vitest run` | ✅ 45/45 files, 286/286 tests passed |
| `grep -rnI 'K2NET\|k2net'` (source) | ✅ **0 matches** |
| `grep -rnI 'smkn6\|SMKN'` (source) | ✅ **0 matches** |

---

## 5. Konsekuensi & Implikasi

### Positif
1. **Platform Vendor-Agnostic**: Codebase tidak lagi membawa identitas brand/tenant manapun — siap di-deploy untuk organisasi apapun hanya melalui customizer settings.
2. **Isolasi Tema Ketat**: `findThemeViewKey` tidak lagi bisa bocor ke sibling themes.
3. **Sample Data Reusable**: `bundle.json` kedua tema berisi data generik yang masuk akal sebagai starting point untuk deployment manapun.
4. **Test Stability**: Assertions tidak lagi bergantung pada string brand spesifik.

### Rencana Lanjutan
- **Branding K2NET**: Akan diimplementasikan ulang sebagai konfigurasi deployment di repo `/home/jejakawan/dev/k2net-portal`, bukan sebagai hardcode di platform core.
- **Branding SMKN 6**: Akan diimplementasikan via Theme Customizer settings dan `SITE_NAME` env var saat deployment ke domain `smkn6bandung.sch.id`.

### Catatan Penting
- ADR historis (`docs/adr/ADR-004` s/d `ADR-009` dan `docs/feat_sarangenge/ADR-001` s/d `ADR-003`) tetap menyebut `K2NET` dan `SMKN 6` sebagai konteks keputusan arsitektur pada masanya. Ini bersifat dokumentasi historis dan **tidak perlu di-sanitize**.
- Yang digeneralisasi adalah **kode sumber dan konfigurasi runtime**, bukan catatan arsitektur.

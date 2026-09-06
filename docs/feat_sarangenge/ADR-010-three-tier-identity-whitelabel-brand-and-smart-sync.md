# ADR-010: Arsitektur 3-Tier Identitas, White Label Enterprise, dan Smart Brand Sync

**Status:** Accepted / Implemented  
**Tanggal:** 2026-09-07  
**Author:** Jejakawan Engineering  
**Scope:** `backend/Modules/Core/app/System/Http/Controllers/SettingController.php`, `frontend/src/modules/Core/System/views/settings/general/GeneralTab.vue`, `frontend/src/modules/Core/System/views/settings/general/PlatformIdentityTab.vue`, `frontend/src/modules/Core/System/views/settings/appearance/AppearanceSettings.vue`, `frontend/src/shared/layouts/partials/TheSidebar.vue`, `frontend/src/shared/composables/useBrandIdentity.ts`, `frontend/src/shared/composables/useSiteIdentity.ts`  
**Supersedes / Extends:** ADR-002 (Pemisahan APP_NAME vs SITE_NAME), Core Engine Generalization & Identity Architecture  

---

## 1. Konteks & Permasalahan

Dalam sistem arsitektur Jejakawan Core Engine (`ja-core_engine`), identitas sistem terbagi menjadi beberapa domain yang sebelumnya saling bercampur atau tumpang tindih:

1. **Pencampuran Identitas Platform dan Identitas Situs Publik**:
   - Di masa lalu, konfigurasi `app_name`, `brand_logo`, dan `brand_favicon` sering disalahartikan sebagai identitas situs publik, padahal entitas tersebut merupakan identitas sistem konsol manajemen (white-label reseller/institusi).
   - Pengaturan `site_name`, `site_logo`, dan `site_favicon` yang diperuntukkan bagi pengunjung situs publik tema (`site` module) tidak terisolasi secara tegas dari konsol manajemen.
2. **Redundansi Upload Logo di Tampilan Konsol**:
   - Pada halaman Console Appearance (`AppearanceSettings.vue`), terdapat formulir upload logo yang menduplikasi pengaturan logo di `GeneralTab.vue`. Ini membingungkan administrator sistem mengenai logo mana yang sebenarnya menjadi acuan resmi konsol.
3. **Ketiadaan Sinkronisasi Pintar (Smart Sync)**:
   - Bagi institusi atau sekolah yang ingin menyamakan seluruh identitas situs publik dengan identitas brand resminya, mereka harus melakukan input data berulang di tab General dan tab Site Identity.
4. **Resiko Ketiadaan Aset Logo (Broken Assets)**:
   - Ketika administrator menghapus logo brand kustom, antarmuka konsol berpotensi menampilkan gambar rusak jika sistem tidak memiliki mekanisme *graceful auto-fallback* ke aset kanonikal vendor Jejakawan.

---

## 2. Keputusan Arsitektur

### A. Pembagian 3-Tier Identitas (Three-Tier Identity Model)

Sistem menetapkan hierarki 3 lapis identitas yang terpisah secara ketat:

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Core Engine Identity (Platform Canonical Baseline)       │
│    Name: "Jejakawan" | Logo: /logo.png | Favicon: /favicon.ico│
│    Sifat: Hardcoded Filesystem Baseline, Fallback Mutlak     │
└──────────────────────────────┬──────────────────────────────┘
                               │ (fallback saat tier 2 kosong)
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. White Label / Brand Identity (Tenant Console Brand)      │
│    app_name, brand_logo, brand_favicon, branding_display    │
│    Scope: Dashboard Konsol (/dash), Login Shell, Sidebar     │
│    Gating: Lisensi Enterprise / White-Label Tier             │
└──────────────────────────────┬──────────────────────────────┘
                               │ (opsional sinkronisasi via smart sync)
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. Site Identity (Public Portal / Active Theme)             │
│    site_name, site_logo, site_favicon, site_description, ...│
│    Scope: Situs Web Publik (/), Janari/Sarangenge/Layung    │
│    Lifecycle: Bergantung pada modul `site` & `layout`       │
└─────────────────────────────────────────────────────────────┘
```

1. **Tier 1 — Core Engine Identity**:
   - Baseline vendor tidak terhapus: nama `"Jejakawan"`, logo kanonikal `/logo.png`, dan favicon kanonikal `/favicon.ico`.
   - Menjadi fallback otomatis apabila Tier 2 tidak memiliki aset atau dihapus oleh administrator.
2. **Tier 2 — White Label / Brand Identity**:
   - Konfigurasi: `app_name`, `brand_logo`, `brand_favicon`, `branding_display`.
   - Lokasi Pengaturan: Konsol $\to$ Pengaturan $\to$ Tab General (`GeneralTab.vue`).
   - Proteksi Lisensi: Diberi badge dan perlindungan lisensi Enterprise (`licenseService.hasFeature('white_label')`).
   - Tampilan Sidebar Konsol Reaktif: Selector mode `branding_display` mendukung:
     - `both`: Menampilkan logo dan teks nama aplikasi.
     - `logo_only`: Hanya menampilkan logo brand.
     - `name_only`: Hanya menampilkan teks nama aplikasi.
     - `collapsed_icon_only`: Hanya menampilkan ikon saat sidebar diciutkan (*collapsed*).
3. **Tier 3 — Site Identity**:
   - Konfigurasi: `site_name`, `site_logo`, `site_favicon`, `site_description`, `site_url`.
   - Lokasi Pengaturan: Konsol $\to$ Pengaturan $\to$ Tab Site Identity (`PlatformIdentityTab.vue`).
   - Ruang Lingkup: Seluruh halaman depan publik (tema Sarangenge, Janari, Layung) yang di-host oleh modul `site`.

---

### B. Smart Brand Sync (`brand_sync_site_identity`)

Untuk menyederhanakan alur kerja institusi yang ingin identitas situs publiknya selalu serasi dengan identitas brand konsol:

1. Disediakan toggle pengaturan `brand_sync_site_identity` di `GeneralTab.vue`.
2. Saat toggle ini diaktifkan dan formulir disimpan:
   - Backend `SettingController::updateGeneral` atau `updatePlatformIdentity` secara atomik menyalin nilai:
     - `app_name` $\to$ `site_name`
     - `brand_logo` $\to$ `site_logo`
     - `brand_favicon` $\to$ `site_favicon`
   - Sinkronisasi juga diteruskan ke konfigurasi tema aktif di `lay_theme_settings` melalui `syncSiteIdentityToActiveTheme()` (apabila modul `layout` aktif).
3. Bila toggle dimatikan, identitas situs publik dapat diatur secara mandiri dan independen tanpa memengaruhi nama brand konsol.

---

### C. Eliminasi Redundansi Logo di Console Appearance

1. Seluruh kontrol upload logo di halaman Console Appearance (`AppearanceSettings.vue`) dihapus.
2. Console Appearance murni difokuskan pada manipulasi tema tampilan (*Easy Mode vs Advanced Mode*, token CSS, preset warna, dan radius).
3. Pengaturan logo brand dipusatkan secara eksklusif pada `GeneralTab.vue` di bawah kendali lisensi Enterprise.

---

### D. Auto-Fallback Saat Logo Dihapus

Ketika administrator menghapus file logo kustom (`brand_logo = null`):
1. `useBrandIdentity.ts` secara cerdas mengevaluasi nilai reaktif `brandLogoUrl`.
2. Jika string kosong atau `null`, URL otomatis kembali ke `/logo.png` (aset resmi Jejakawan).
3. Sidebar konsol (`TheSidebar.vue`) dan halaman login (`Login.vue`) tetap tampil sempurna tanpa *broken image icon*.

---

## 3. Konsekuensi & Verifikasi

### Keuntungan Arsitektur:
- **Zero Confusion**: Peran masing-masing identitas (Core vs White Label vs Site) sangat jelas dan terdokumentasi.
- **Enterprise Grade**: Memenuhi syarat white-label penuh tanpa risiko kebocoran brand (*brand leakage*).
- **Graceful Resilience**: Sistem tidak akan pernah mengalami visual rusak ketika logo atau favicon kustom dihapus.
- **Symmetric i18n**: Semua label, deskripsi bantuan, dan badge lisensi diterjemahkan secara simetris dalam bahasa Indonesia (`id`), Inggris (`en`), dan Sunda (`su`).

### Verifikasi:
- Lulus pemeriksaan simetri i18n: `npm run i18n:check` (9.214 kunci simetris per bahasa).
- Lulus type-checking TypeScript: `vue-tsc -b` (0 errors).
- Uji fungsional: Simpan identitas brand dengan toggle sinkronisasi aktif $\to$ nilai `site_name`, `site_logo`, dan tema aktif tersinkronisasi sempurna. Hapus logo $\to$ fallback ke `/logo.png` berfungsi seketika.

---
status: accepted
scope: layout
date: 2026-09-19
related:
  - ADR-002
  - ADR-015
  - ADR-022
  - ADR-023
  - ADR-024
---

# ADR-025: Theme Customizer Default Mode, Site Locale, and Runtime Prepaint Resilience

**Status:** Accepted ✅ implemented (2026-09-19)  
**Tanggal:** 2026-09-19  
**Author:** Jejakawan Core Engineering  
**Scope:** `frontend/src/modules/Layout/customizer/`, `frontend/src/shared/composables/useDarkMode.ts`, `frontend/src/engine/i18n.ts`, `frontend/public/theme-prepaint.js`, `backend/public/theme-prepaint.js`, `backend/Modules/Layout/`  
**Related:** ADR-002 (Customizer page isolation & branding), ADR-015 (Favicon & prepaint guards), ADR-023 (Theme registry packs), ADR-024 (Header menu alignment)

---

## 1. Konteks & Latar Belakang

Sebelumnya, tema publik Jejakawan belum memiliki kontrol terpusat di **Theme Customizer** untuk menentukan mode tampilan warna awal (*default theme mode*) dan bahasa utama situs (*default site locale*) bagi pengunjung baru:
1. **White Flash & Mode Theme**:
   - Jika pengunjung baru pertama kali mengunjungi situs, sistem otomatis mengandalkan preferensi browser (*system*) atau default hardcoded tanpa mempedulikan tema situs yang mungkin sengaja dirancang gelap (*dark mode*) seperti pada tema Layung atau tema berkarakter khusus.
2. **Bahasa Situs**:
   - Belum ada pengaturan kanonikal di Customizer untuk menetapkan bahasa utama situs (`id`, `en`, `su`, atau `auto` deteksi browser).
3. **Subresource Integrity (SRI) Digest Mismatch**:
   - Static script `/theme-prepaint.js` sebelumnya diberi SRI digest oleh bundler Vite. Saat aset di-cache oleh Cloudflare/CDN sementara HTML diperbarui, terjadi kegagalan eksekusi prepaint akibat integritas SRI digest mismatch.
4. **Dynamic Chunk Failure pada Rute Publik**:
   - Saat deployment baru bergulir di server, chunk lama yang diakses oleh tab browser yang masih terbuka berisiko melempar uncaught dynamic import error tanpa auto-recovery.

Fitur ini sebelumnya telah diuji coba pada downstream `k2net-portal` dan kini distandarisasi ke dalam arsitektur upstream kanonikal `ja-core_engine`.

---

## 2. Keputusan Arsitektur

### 2.1 Standardisasi Skema Customizer (`global.settings.schema.json` & `ThemeService.php`)
1. **`default_theme_mode`** (Kategori: `Appearance / Gaya`):
   - Opsi: `dark` (Default), `light`, `system`.
   - Mengontrol mode warna tampilan awal bagi pengunjung publik sebelum mereka memilih switch tema secara manual.
2. **`default_site_locale`** (Kategori: `General / Umum`):
   - Opsi: `id` (Default), `en`, `su`, `auto` (Deteksi Browser).
   - Menentukan bahasa tampilan utama yang dimuat saat pengunjung baru pertama kali membuka situs.

### 2.2 Prepaint Instan Zero-Flicker (`theme-prepaint.js`)
- `frontend/public/theme-prepaint.js` dan `backend/public/theme-prepaint.js` membaca `ja_theme_default_mode` dan `ja_theme_default_locale` langsung dari `localStorage` sebelum browser melakukan *first paint*.
- Menjamin halaman tidak mengalami kedipan putih (*white flash*) atau pergantian bahasa mendadak (*locale flick*) saat boot.

### 2.3 Reaktivitas Runtime & Resolusi Cerdas
1. **`useDarkMode.ts`**:
   - Menyediakan `getThemeDefaultMode()` dan `applyFrontendThemeDefault(defaultMode)`.
   - Menyimpan preferensi default ke `ja_theme_default_mode` dan mengaktifkan kelas `dark` pada elemen `<html>` secara otomatis jika pengguna belum pernah memilih tema manual (`frontend-dark-mode` kosong).
2. **`useTheme.ts`**:
   - Mengambil `default_theme_mode` dan `default_site_locale` dari data tema aktif dan menerapkannya saat inisialisasi maupun saat preview pesan postMessage dikirim oleh Theme Customizer.
3. **`i18n.ts`**:
   - Mengekspor `getThemeDefaultLocale()`.
   - `detectLocale()` mengimplementasikan hirarki prioritas resolusi:
     1. Pilihan manual pengguna (`localStorage.getItem('locale')`)
     2. Pengaturan bahasa default tema (`getThemeDefaultLocale()`)
     3. Bahasa browser perangkat (jika mode `auto` dipilih)
     4. Fallback kanonikal (`'id'`)

### 2.4 Resiliensi Deployment & SRI
1. **`strip-prepaint-sri` di `vite.config.ts`**:
   - Menghapus atribut `integrity` dan `crossorigin` khusus untuk tag script `/theme-prepaint.js` pada file HTML hasil build (`index.html`, `landing.html`, `public.html`) agar cache Cloudflare/CDN tidak memblokir eksekusi prepaint.
2. **Dynamic Chunk Auto-Recovery di `public.ts` router**:
   - Memasang `router.onError` menggunakan `attemptChunkRecoveryReload` dan `isChunkLoadError` dari `@/shared/utils/chunkRecovery` untuk melakukan pemulihan sesi otomatis ketika chunk lama kadaluarsa pasca-deployment.

### 2.5 Resiliensi CSP & Library Tag Management
1. Menambahkan domain tantangan Cloudflare (`challenges.cloudflare.com`, `cloudflare.com`, `*.cloudflare.com`) ke script-src CSP di `SecurityHeaders.php`.
2. Mengharmonisasi query type `content` (termasuk tipe `general` dan `null`) pada `TagController.php` dan memperbaiki watcher filter pada antarmuka `Index.vue` modul Library.

---

## 3. Konsekuensi & Status Paritas

- **Upstream Engine (`ja-core_engine`)**: Telah memiliki fungsionalitas penuh untuk pengaturan default tema dan bahasa pada seluruh tema first-party.
- **Downstream Synchronization**: Perubahan ini dipropagasikan secara merata ke `ja-cms`, `smkn6-portal`, `smkn1cijulang-portal`, dan menyelaraskan `k2net-portal`.

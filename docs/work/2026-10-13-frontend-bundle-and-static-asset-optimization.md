---
id: 2026-10-13-frontend-bundle-and-static-asset-optimization
title: "Optimasi bundle awal, pemuatan locale modular, dan placeholder WebP"
status: completed
scale: L
repo: k2net-portal
owner: agent
created: 2026-10-13
updated: 2026-10-13
blocks: []
related_adr: [ADR-082]
---

# Task: Optimasi bundle frontend dan aset statis K2NET Portal

## 1. Tujuan

Menurunkan beban transfer awal console (`index.html`) dan publik (`public.html`), menjaga landing (`landing.html`) tetap sangat ringan, serta mengurangi ukuran aset placeholder gambar tanpa merusak terjemahan bahasa (Indonesian, English, Sundanese), navigasi rute, maupun kompatibilitas URL aset lama yang mungkin sudah tersimpan.

## 2. Audit (current state vs baseline)

- **Baseline Awal**:
  - `check-perf-budget.mjs` lama hanya memeriksa ukuran file `index-*.js` tunggal (<650 KB) dan total JS (<26.000 KB), tanpa mengukur total static import graph manifest yang sebenarnya diunduh per halaman.
  - Eager import katalog modul di `frontend/src/engine/i18n/moduleLocales.ts` menarik seluruh modul (System, Infra, Mail, Media, Library, Publishing, Layout, Forms, Newsletter, Search, AI, Member) dan 4 tema sekaligus ke bundle awal.
  - `manualChunks` di `vite.config.ts` memecah ke `vendor-misc` catch-all yang menarik pustaka berat (PDF.js, Chart.js, FullCalendar, Tiptap) ke dalam jalur inisialisasi awal.
  - Aset placeholder PNG (`avatar-placeholder.png`, `hero-placeholder.png`, `news-placeholder.png`) total berukuran **~2,2 MB**.

## 3. Keputusan & Desain Solusi

1. **Modular & On-demand Locale Loading**:
   - Memisahkan pesan inti bahasa (`common`, `editor`, `plugin`, `console`) dari katalog modul spesifik.
   - Menggunakan `import.meta.glob('/src/modules/**/locales/**/*.json')` dinamis untuk memuat modul sesuai rute/shell yang aktif (`ensureModuleLocaleMessages`).
   - Shell console hanya memuat `system`, `infra`, dan ekstensi aktif.
   - Shell publik hanya memuat `layout` (dan `member` bila ekstensi aktif).
   - Katalog `security` ditunda (deferred) dan dimuat dinamis saat rute `/security` atau `/profile` diakses.
   - Mendukung pergantian bahasa dinamis (`id`, `en`, `su`) yang memuat ulang pack modul yang sudah aktif.
2. **Rollup Natural Dynamic Chunking**:
   - Menghapus konfigurasi `manualChunks` catch-all di `vite.config.ts` dan mengaktifkan `manifest: true`.
   - Membiarkan batas import dinamis (`import()`) Rollup memisahkan chunk per rute dan per dependensi opsional secara alami.
3. **Optimasi Placeholder Statis**:
   - Menambahkan format WebP (`avatar-placeholder.webp` ~6 KB, `hero-placeholder.webp` ~50 KB, `news-placeholder.webp` ~55 KB — total ~112 KB, reduksi 95%).
   - Mengarahkan fallback di `BlockRenderer.vue` ke aset WebP.
   - Mempertahankan file `.png` lama di direktori public demi kompatibilitas URL.
4. **Enforcement Perf Budget Checker**:
   - Memperbarui `frontend/scripts/check-perf-budget.mjs` untuk mengukur static import graph dari `.vite/manifest.json`.
   - Menetapkan budget: Initial JS Gzip <= 200 KB (landing <= 69 KB), Initial CSS Gzip <= 100 KB.

## 4. Hasil & Metrik Terverifikasi

### Bundle Static Import Graph (Gzip)

| Shell Entry | Hasil JS (Raw / Gzip) | JS Gzip Budget | Hasil CSS (Raw / Gzip) | CSS Gzip Budget | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Console (`index.html`)** | 400 KB / **139 KB** | 200 KB | 376 KB / **45 KB** | 100 KB | **PASS** |
| **Public (`public.html`)** | 397 KB / **138 KB** | 200 KB | 376 KB / **45 KB** | 100 KB | **PASS** |
| **Landing (`landing.html`)** | 188 KB / **68 KB** | 69 KB | 382 KB / **46 KB** | 100 KB | **PASS** |

### Aset Placeholder Aktif

- `avatar-placeholder.webp`: **6.184 B** (~6,1 KB)
- `hero-placeholder.webp`: **50.882 B** (~50,8 KB)
- `news-placeholder.webp`: **55.892 B** (~55,8 KB)
- **Total**: ~112 KB (turun ~95% dari baseline ~2,2 MB).
- File kompatibilitas `.png` tetap tersedia utuh.

### Verifikasi Quality Gates

- `npm run i18n:check` & `npm run i18n:check:full`: **PASS** (4.455 gate keys, 9.699 definitions simetris `en/id/su`).
- `npm run type-check`: **PASS** (0 TypeScript errors).
- `npm run test:unit`: **PASS** (52 file tes, 324 tes unit lulus).
- `npm run quality:perf`: **PASS** (semua entry di bawah target budget).
- `npm run quality:frontend`: **PASS** (linter, i18n, type-check, vitest, dan playwright test list).

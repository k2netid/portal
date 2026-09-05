# ADR-006: Arsitektur Official Plugin Floating Social Dock & Hotline serta Kategorisasi Ekstensi

**Status:** Accepted / Implemented  
**Tanggal:** 2026-09-05  
**Author:** Jejakawan Engineering  
**Scope:** `smkn6-portal` & `k2net-portal` (Backend, Frontend, Plugin Registry, Customizer, E2E Testing)  
**Supersedes / Extends:** [ADR-003](./ADR-003-generic-fail-safe-instagram-feed-plugin-and-theme-slots.md), [ADR-004](./ADR-004-multi-theme-soc-cross-theme-isolation-and-identity-generalization.md), [ADR-005](./ADR-005-complete-brand-generalization-smkn6-and-k2net.md)

---

## 1. Konteks & Permasalahan

Sebelumnya, komponen *floating social dock* (tombol melayang media sosial & hotline) diimplementasikan secara terpisah dan terisolasi di dalam masing-masing template tema (`JanariFloatingSocialDock.vue`, `LayungFloatingSocialDock.vue`, `SarangengeFloatingSocialDock.vue`). Pendekatan ini menimbulkan beberapa kendala:
1. **Redundansi Kode**: Duplikasi markup, logika posisi, serta styling lintas tema.
2. **Keterbatasan Fleksibilitas**: Pengguna tidak dapat mengaktifkan atau menonaktifkan fitur secara independen melalui panel ekstensi (*Module & Plugin Registry*).
3. **Pemisahan UI yang Belum Jelas**: Di halaman manajemen ekstensi (`/dash/extensions`), item baru seperti Floating Social Dock hanya muncul di tab filter *"Semua"* alih-alih dikelompokkan rapi ke tab *"Plugin"*.
4. **Resiko Rate-Limiter pada Pengujian Otomatis**: Pengujian login otomatis dengan script headless memicu mekanisme progressive security lockout di backend (`SecurityService`), memblokir akun dan IP workstation dev.

---

## 2. Keputusan Arsitektur

### A. Transformasi Menjadi Official Plugin (`floating-social-dock`)
Komponen dock didecoupling dari direktori tema dan dijadikan **Official First-Party Plugin**:
* **Database Extension Entry**: Didaftarkan via migration `2026_09_05_000002_register_floating_social_dock_extension.php` dengan `slug = 'floating-social-dock'`, `type = 'plugin'`, dan `family = 'plugin'`.
* **Global Plugin Slot**: Didaftarkan slot layout baru `'floating_overlay'` di `backend/config/layout.php` dengan default binding:
  ```php
  'floating_overlay' => [
      'allowed_types' => ['plugin', 'widget'],
      'max_items' => 3,
      'default_blocks' => ['floating-social-dock'],
  ]
  ```
* **Frontend Canonical Block**: Dibuat komponen kanonikal `FloatingSocialDockBlock.vue` di `frontend/src/engine/plugins/blocks/` dengan kemampuan:
  - Animasi GSAP interaktif (dock bounce, staggered icons, hover expanding pill).
  - 7 Posisi responsif (`bottom-right`, `bottom-left`, `top-right`, `top-left`, `center-right`, `center-left`, `bottom-center`).
  - 3 Orientasi tata letak (`horizontal`, `vertical`, `auto`).
  - 3 Gaya kapsul (`glassmorphism`, `solid`, `minimal`).
  - Dukungan aksesibilitas penuh (ARIA labels, keyboard navigation) dan i18n.
* **Stacking Context Safe Container**: `PluginSlot.vue` dikonfigurasi khusus saat `name === 'floating_overlay'` dengan class `fixed inset-0 z-[9990] pointer-events-none overflow-visible` (anak tombol memakai `pointer-events-auto`), memastikan interaksi tidak pernah terhalang oleh container SVG hero section tema.

### B. Integrasi Dua Arah & Conditional Logic dengan Theme Customizer
* Pada `frontend/src/modules/Layout/composables/useCustomizerNavigation.ts`, diimplementasikan conditional logic di dalam `getVisibleSettings`. Jika toggle `enable_floating_social` bernilai `false`, maka seluruh 5 opsi subordinate dock disembunyikan secara otomatis dari panel customizer.

### C. Standardisasi Kategorisasi Tab Ekstensi
* Diperbaiki skema PostgreSQL `sys_extensions` dan migration agar secara eksplisit mengisi `'family' => 'plugin'`.
* Pada `frontend/src/modules/Core/System/views/settings/extensions/Index.vue`, diperbarui fungsi `resolveFamily`:
  ```ts
  const resolveFamily = (ext: ExtensionItem): string => {
      if (ext.type === 'plugin' || ext.family === 'plugin') {
          return 'plugin';
      }
      if (ext.family && ext.family !== 'module') {
          return ext.family;
      }
      if (ext.is_core || ext.slug === 'core') {
          return 'platform';
      }
      return ext.family || 'cms';
  };
  ```
  Dengan demikian, baik `floating-social-dock` maupun `instagram-feed` secara konsisten tampil di bawah tab **Plugin**.

### D. Keamanan & Staging E2E Testing Bypass
* Ditambahkan lingkungan `'staging'` ke dalam `CaptchaService::isE2eBypassed()`:
  ```php
  if (! app()->environment(['local', 'testing', 'staging'])) {
      return false;
  }
  ```
  Memungkinkan Playwright menggunakan header `X-E2E-Captcha-Bypass` saat pengujian E2E di staging tanpa mematikan proteksi captcha untuk pengguna nyata.
* Workstation dev (`192.168.88.4`) dan localhost (`127.0.0.1`) ditambahkan ke tabel `sys_ip_lists` dengan status `whitelist` permanen.

---

## 3. Verifikasi & Dampak

1. **E2E Test Parity**:
   - `floating-social-dock.spec.ts`: Passed di SMKN6 (`49280`) dan K2NET (`8083`).
   - `extensions-integration.spec.ts`: Passed di SMKN6 (`49280`) dan K2NET (`8083`).
2. **Quality Gates**:
   - Paritas translasi i18n 100% simetris (27 gate keys, 9098 definisi per bahasa: `id`, `en`, `su`).
   - Type-check TypeScript 0 errors.
   - Sinkronisasi aset `npm run build` dan `sync-frontend-assets-to-backend.sh` sinkron 100% di kedua repositori.

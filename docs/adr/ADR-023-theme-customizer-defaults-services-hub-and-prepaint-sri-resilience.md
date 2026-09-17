# ADR-023: Theme Customizer Global Defaults, Unified Services Hub, and SRI Prepaint Resilience

**Status:** Accepted  
**Tanggal:** 2026-09-17  
**Author:** Jejakawan Engineering  
**Scope:** `frontend/`, `backend/Modules/Layout/`, `backend/Modules/Library/`, `backend/Modules/Core/`, `themes/layung`

---

## Konteks & Latar Belakang

Pada sesi pengembangan portal K2NET (`k2net-portal`), terdapat beberapa tantangan teknis dan kebutuhan bisnis yang saling berkaitan:

1. **Reaktivitas Manajemen Tag (Library Module)**:
   - Terjadi inkonsistensi sinkronisasi data dan *reactivity loss* pada UI manajemen tag (`Index.vue`) saat pengguna beralih antara tab tag umum (*general*) dan tag editorial.

2. **Fragmentasi Portofolio Layanan (`Solusi.vue` vs `Services.vue`)**:
   - Menu navigasi publik mengarahkan pengguna ke halaman terpisah yang membingungkan: halaman solusi (`/solusi`) dan halaman layanan (`/services`), dengan format penamaan file bahasa Indonesia (`Solusi.vue`, `Tim.vue`) yang tidak konsisten dengan konvensi bahasa Inggris modul frontend.
   - Section SLA (Service Level Agreement) internet hilang dari tampilan ISP publik.

3. **Restrukturisasi Navigasi Header & Halaman Tim**:
   - Halaman profil tim belum terdaftar di menu utama dan file masih bernama `Tim.vue`.
   - Diperlukan integrasi penuh dengan database *Menu Builder* (`lay_menus` dan `lay_menu_items`) serta seeders deployment klien.

4. **Dynamic Chunk Loading Failure (`Strict MIME type checking`)**:
   - Saat deployment parsial dilakukan, browser pengunjung yang memiliki cache HTML lama mencoba memuat modul JavaScript lama yang hash chunk-nya telah digantikan di server (`Bf0V3fxL.js`), memicu 404 HTML fallback dan TypeError pada dynamic import.

5. **Kebutuhan Pengaturan Default Visual & Bahasa di Theme Customizer**:
   - Belum ada kontrol di Theme Customizer untuk menetapkan tampilan bawaan situs publik ke **Dark Mode** dan bahasa bawaan ke **Bahasa Indonesia (ID)** secara terpusat tanpa mengabaikan preferensi manual pengunjung.

6. **Subresource Integrity (SRI) Digest Mismatch pada `theme-prepaint.js`**:
   - Plugin `vite-plugin-sri` secara otomatis menghitung digest sha384 untuk seluruh script di HTML. Karena `/theme-prepaint.js` adalah aset statis tanpa hash file dan di-cache oleh Cloudflare/browser dengan TTL 4 jam, pembaruan script prepaint menyebabkan browser menolak eksekusi script akibat *integrity mismatch*.

---

## Keputusan Arsitektur

### 1. Perbaikan Reaktivitas Tag Management (`TagController.php` & `Index.vue`)
- Mengoreksi penanganan parameter filter dan refresh state pada `TagController.php` dan Vue component `frontend/src/modules/Library/views/tags/Index.vue`.
- Memastikan transisi antar-tab me-reload data secara reaktif dan membersihkan form state tanpa sisa query lama.

### 2. Konsolidasi Portofolio Layanan ke `Services.vue`
- Menyatukan fungsionalitas `Solusi.vue` ke dalam [Services.vue](file:///home/jejakawan/dev/k2net-portal/frontend/src/modules/Layout/views/themes/layung/pages/Services.vue) sebagai satu-satunya kanonikal hub layanan K2NET.
- Menerapkan *interactive tab switcher* dengan sinkronisasi URL hash (`#isp`, `#msp`) dan query string (`?tab=isp`, `?tab=msp`).
- Menghidupkan kembali komponen `<SlaGuaranteeSection />` di tab ISP lengkap dengan jaminan SLA berbasis kontrak dan panel ASN AS153992 / IDNIC.
- Menghapus file `Solusi.vue` dan membersihkan rute `/solusi` dari routing table.

### 3. Standarisasi Halaman Tim (`Team.vue`) dan Navigasi Header
- Mengubah nama file `Tim.vue` menjadi [Team.vue](file:///home/jejakawan/dev/k2net-portal/frontend/src/modules/Layout/views/themes/layung/pages/Team.vue).
- Memetakan rute `/team` dengan alias `tim` untuk backwards-compatibility.
- Mendaftarkan menu "Tim" ke dalam *Header Navigation Menu* di urutan ke-5.
- Memperbarui `K2netDeploymentSeeder.php`, `bundle.json`, dan menyinkronkan data `lay_menus` & `lay_menu_items` di database staging (`ja-dev`) dan live (`ja-srv`).

### 4. Dynamic Chunk Recovery Handler
- Menambahkan fungsi `attemptChunkRecoveryReload` di `frontend/src/shared/utils/chunkRecovery.ts` dengan perlindungan `sessionStorage` terhadap *reload loop*.
- Memasang `router.onError` di `frontend/src/engine/router/public.ts` untuk mendeteksi error `Failed to fetch dynamically imported module` dan memicu refresh otomatis saat hash chunk berubah pasca-deployment.

### 5. Pengaturan Default Dark Mode & Bahasa di Theme Customizer
- Menambahkan schema field pada `frontend/src/modules/Layout/customizer/platform/schema/global.settings.schema.json` dan `theme.json`:
  - `default_theme_mode`: `dark` (default), `light`, `system` (Panel: Appearance / Gaya).
  - `default_site_locale`: `id` (default), `en`, `su`, `auto` (Panel: General / Umum).
- Mengintegrasikan pipeline prepaint:
  - `theme-prepaint.js`: membaca `ja_theme_default_mode` dan `ja_theme_default_locale` sebelum *first paint* DOM.
  - `useDarkMode.ts`: menggunakan default tema aktif sebagai fallback jika pengunjung belum memiliki preferensi tersimpan di `localStorage['frontend-dark-mode']`.
  - `i18n.ts`: menginisialisasi locale bawaan dari `ja_theme_default_locale`.
  - `useTheme.ts`: mengekstrak konfigurasi tema dan memancarkan event sinkronisasi real-time ke live preview.

### 6. Isolasi Subresource Integrity (SRI) pada `theme-prepaint.js`
- Menambahkan plugin khusus `strip-prepaint-sri` di `frontend/vite.config.ts` untuk menghapus atribut `integrity` dan `crossorigin` hanya pada `<script src="/theme-prepaint.js">`.
- File bundle ter-hash pada direktori `/assets/*.js` dan `/assets/*.css` tetap terlindungi oleh atribut SRI penuh.
- Memperbarui script pipeline deployment `sync-frontend-assets-to-backend.sh` agar selalu menyinkronkan seluruh file pintu masuk (`public.html`, `landing.html`, `index.html`) bersamaan dengan `/assets/`.

### 7. Penguatan Header Keamanan CSP (Cloudflare Integration)
- Menambahkan domain Cloudflare Turnstile / Challenge (`https://challenges.cloudflare.com`, `https://cloudflare.com`, `https://*.cloudflare.com`) pada direktif `script-src` dan `connect-src` di `backend/Modules/Core/app/Security/Http/Middleware/SecurityHeaders.php`.

---

## Dampak & Konsekuensi

1. **Pengalaman Pengguna (UX)**:
   - Situs publik memuat langsung dalam tema gelap (*Dark Mode*) dan Bahasa Indonesia tanpa *flash* putih atau flickering.
   - Halaman layanan terintegrasi rapi dengan navigasi tab dan link hash yang dapat dibagikan (`/services#isp`, `/services#msp`).
2. **Resiliensi Deployment**:
   - Tidak ada lagi pemblokiran script oleh browser akibat SRI mismatch saat file prepaint diperbarui.
   - Pengunjung yang berada di situs saat deployment berlangsung tidak mengalami kegagalan modul ketika berpindah halaman.
3. **Kebersihan Arsitektur**:
   - Kode sumber mematuhi konvensi penamaan standar bahasa Inggris (`Team.vue`, `Services.vue`).
   - Database navigasi tersinkronisasi simetris antara staging dan live.

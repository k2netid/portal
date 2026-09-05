# ADR-007: Arsitektur Siklus Hidup Paket (Ekspor/Impor) Tema & Ekstensi serta Kontrol Lisensi dan Keamanan

**Status:** Accepted / Implemented  
**Tanggal:** 2026-09-05  
**Author:** Jejakawan Engineering  
**Scope:** `smkn6-portal` & `k2net-portal` (Backend, Frontend, Core System, Layout/Theme Engine, License Service, Security, E2E Testing)  
**Supersedes / Extends:** [ADR-004](./ADR-004-multi-theme-soc-cross-theme-isolation-and-identity-generalization.md), [ADR-005](./ADR-005-complete-brand-generalization-smkn6-and-k2net.md), [ADR-006](./ADR-006-official-plugin-floating-social-dock-and-extensions-categorization.md)

---

## 1. Konteks & Permasalahan

Seiring berkembangnya ekosistem modular Jejakawan Portal, kebutuhan untuk mendistribusikan, memindahkan (migrasi), mengarsipkan, serta memasang tema (*themes*) dan modul/plugin (*extensions*) antar instansi atau lingkungan deployment menjadi semakin krusial. 

Sebelumnya, terdapat beberapa ketidaksinkronan arsitektural:
1. **Asimetri UI/UX Ekspor & Impor**:
   - Backend `ThemePackageInstallService` sudah memiliki mekanisme unpacking ZIP untuk tema, namun UI di portal (`/dash/themes`) belum menyediakan tombol interaktif untuk ekspor paket ZIP tema yang terpasang.
   - Panel ekstensi (`/dash/extensions`) belum memiliki kontrol terpadu untuk mengunggah ZIP paket ekstensi baru maupun mengekspor ekstensi pihak ketiga/custom ke dalam arsip ZIP mandiri beserta `manifest.json`.
2. **Ketiadaan Kontrol Keamanan Administratif**:
   - Mengizinkan upload arsip ZIP sembarangan pada instansi produksi tanpa kontrol toggle berisiko menimbulkan celah keamanan (*arbitrary code execution / zip slip*).
   - Administrator sistem memerlukan kontrol granular (sakelar aktif/nonaktif) untuk membatasi fitur impor dan ekspor tema maupun ekstensi.
3. **Penyelarasan dengan Model Lisensi Komersial (Licensing Tiers)**:
   - Sesuai roadmap komersial Jejakawan Core Engine (JA-CP), kemampuan kustomisasi tingkat lanjut seperti instalasi dan ekspor tema/plugin pihak ketiga dialokasikan untuk tier **Pro**, **Enterprise**, dan **White-Label**, sementara tier **Community** dibatasi pada tema/ekstensi resmi (*first-party / bundled*).
   - Diperlukan integrasi yang transparan antara konfigurasi keamanan sistem (`sys_settings`), model lisensi (`LicenseService`), dan tampilan visual matriks kapabilitas di tab lisensi (`LicenseTab.vue`).

---

## 2. Keputusan Arsitektur

### A. Skema Basis Data & Konfigurasi Pengaturan Sistem (`sys_settings`)
Dibuat migration `backend/Modules/Core/database/migrations/2026_09_05_000003_add_package_upload_export_settings.php` serta pembaruan seeder `FoundationSeeder.php` untuk menambahkan 4 kunci pengaturan baru di bawah grup `security`:
* `enable_theme_upload` (boolean, default: `true`): Izin unggah dan instalasi paket tema ZIP dari konsol.
* `enable_plugin_upload` (boolean, default: `true`): Izin unggah dan registrasi paket ekstensi/plugin ZIP dari konsol.
* `enable_theme_export` (boolean, default: `true`): Izin pengunduhan/ekspor paket tema menjadi arsip ZIP.
* `enable_plugin_export` (boolean, default: `true`): Izin pengunduhan/ekspor paket ekstensi menjadi arsip ZIP terkompresi.

Pengaturan ini disimpan sebagai setting terkelola yang dapat dikonfigurasi langsung oleh Super Admin melalui panel pengaturan keamanan.

---

### B. Integrasi Licensing Service & Entitlements Matrix
Pada `backend/Modules/Core/app/System/Services/LicenseService.php`, ditambahkan pemetaan kapabilitas baru ke dalam `getFeaturesMatrix()`:
```php
'theme_upload' => [
    'name' => 'Custom Theme Import',
    'description' => 'Upload and install third-party ZIP themes directly from console',
    'min_tier' => 'pro',
    'allowed_tiers' => ['pro', 'enterprise', 'white_label'],
],
'plugin_upload' => [
    'name' => 'Custom Plugin/Extension Import',
    'description' => 'Upload and register custom extension packages from console',
    'min_tier' => 'enterprise',
    'allowed_tiers' => ['enterprise', 'white_label'],
],
'theme_export' => [
    'name' => 'Theme Package Export',
    'description' => 'Export installed themes as standalone portable ZIP packages',
    'min_tier' => 'pro',
    'allowed_tiers' => ['pro', 'enterprise', 'white_label'],
],
'plugin_export' => [
    'name' => 'Extension Package Export',
    'description' => 'Export registered extensions and manifests into certified ZIP archives',
    'min_tier' => 'enterprise',
    'allowed_tiers' => ['enterprise', 'white_label'],
],
```

Pada mode pengembangan non-produksi (`APP_ENV !== 'production'`), `ThemePackageInstallService` dan `ExtensionController` tetap mengizinkan operasi pengembang secara mulus (*developer pass-through*), kecuali apabila nilai `license_type` secara eksplisit diset ke `community` (misalnya saat pengujian unit lisensi).

---

### C. Backend Package Lifecycle: Layout Module & Core Extension Controller

#### 1. Manajemen Tema (`Modules/Layout`)
* **`ThemePackageInstallService`**:
  - Diperbarui metode `isEnabled()` untuk memverifikasi dua layer gerbang:
    1. Pengaturan keamanan `enable_theme_upload`.
    2. Hak lisensi `theme_upload` melalui `LicenseService::isFeatureAllowed('theme_upload')`.
* **`ThemeController`**:
  - Endpoint `uploadStatus` (`GET /api/v1/themes/upload-status`) mengembalikan payload lengkap:
    ```json
    {
      "enabled": true,
      "max_upload_size": 20971520,
      "allowed_extensions": ["zip"],
      "export_enabled": true
    }
    ```
  - Endpoint baru `export` (`GET /api/v1/themes/{theme}/export`):
    - Mengompresi seluruh direktori tema (komponen Vue, aset, `theme.json`) ke dalam file ZIP sementara di storage.
    - Men-stream unduhan dengan header `Content-Type: application/zip` dan `Content-Disposition: attachment; filename="{theme_id}-theme.zip"`.
    - Membersihkan file ZIP sementara secara otomatis setelah respons selesai via `deleteFileAfterSend(true)`.

#### 2. Manajemen Ekstensi & Plugin (`Modules/Core/System`)
* **`ExtensionController`**:
  - Ditambahkan helper method `isUploadAllowed()` dan `isExportAllowed()` yang memeriksa toggle setting dan lisensi `plugin_upload` / `plugin_export`.
  - Endpoint baru `capabilities` (`GET /manage/system/extensions/capabilities`): Mengembalikan status kesiapan upload dan ekspor untuk konsumsi frontend yang reaktif.
  - Ditambahkan atribut `can_export` pada setiap item hasil `index()`. Modul inti sistem (*core platform*) diproteksi agar tidak dapat diekspor sembarangan, sementara plugin kustom (seperti `floating-social-dock`) diizinkan untuk diekspor.
  - Endpoint baru `export` (`GET /manage/system/extensions/{slug}/export`):
    - Mengarsipkan direktori ekstensi beserta `manifest.json` yang tervalidasi.
    - Men-stream unduhan ZIP dan menghapus berkas arsip temporer setelah transmisi tuntas.
* **Manifest Kanonikal Plugin**:
  - Disediakan `backend/extensions/floating-social-dock/manifest.json` yang memuat metadata standar (id, name, version, author, compatibility, dan target slot).

---

### D. Frontend UI/UX Harmonization & Multilingual Alignment

1. **Katalog Tema (`frontend/src/modules/Layout/views/themes/Index.vue`)**:
   - Tombol **"Unggah ZIP"** di header halaman menampilkan status upload dinamis sesuai `uploadStatus.enabled`.
   - Kartu tema kini dilengkapi tombol aksi **"Ekspor ZIP"** dengan icon `Download` (`h-3.5 w-3.5`), memicu stream unduhan blob file ZIP secara aman dan menampilkan toast notifikasi sukses/gagal.
2. **Katalog Ekstensi (`frontend/src/modules/Core/System/views/settings/extensions/Index.vue`)**:
   - Tombol **"Unggah ZIP"** terpasang di header dengan pengecekan `canUpload`.
   - Kolom aksi tabel ekstensi menyertakan tombol **"Ekspor"** untuk setiap ekstensi yang memiliki flag `can_export === true`.
3. **Pengaturan Keamanan (`SecurityTab.vue`)**:
   - Ditambahkan grup akordion baru: **"Paket Tema & Ekstensi"** (`packages`) dengan icon `Package` dan 4 switch toggle independen (`enable_theme_upload`, `enable_plugin_upload`, `enable_theme_export`, `enable_plugin_export`).
4. **Matriks Lisensi (`LicenseTab.vue`)**:
   - Didaftarkan 4 kapabilitas (`theme_upload`, `plugin_upload`, `theme_export`, `plugin_export`) ke dalam `featureDefinitions` sehingga secara visual muncul pada kartu **"Current Tier Capabilities"** (dengan centang hijau atau ikon gembok terkunci sesuai tier aktif).
5. **Navigasi Tab Dinamis (`Index.vue`)**:
   - Ditambahkan watcher pada `route.query.tab` agar navigasi via URL langsung (seperti `/dash/settings?tab=security` atau `/dash/settings?tab=license`) secara instan mengaktifkan tab yang bersangkutan.
6. **Kesetaraan Penuh i18n**:
   - Ditambahkan kamus terjemahan lengkap untuk Bahasa Indonesia (`id.json`), Bahasa Inggris (`en.json`), dan Basa Sunda (`su.json`) pada modul `Layout` dan `Core/System`.
   - Lolos verifikasi validasi `npm run i18n:check` dengan 9.117 kunci simetris tanpa anomali.

---

## 3. Konsekuensi & Keamanan

### Keuntungan (Pros)
1. **Keamanan Bertingkat (Defense in Depth)**: Perlindungan ganda dari layer lisensi platform dan sakelar administratif lokal mencegah eksploitasi unggah berkas tak berizin pada instansi publik.
2. **Portabilitas Penuh**: Memudahkan pengembang dan administrator membuat cadangan atau memindahkan konfigurasi tema dan plugin antar instansi `smkn6-portal` dan `k2net-portal`.
3. **Pengalaman Pengguna yang Kohesif**: Tampilan konsisten antara manajemen tema dan manajemen plugin, dengan umpan balik visual yang jelas saat fitur dinonaktifkan oleh kebijakan sistem.

### Pertimbangan Keamanan (Mitigasi)
- Direktori ekstraksi ZIP dibatasi secara ketat di dalam direktori tema/ekstensi yang ditunjuk, dengan validasi sanitasi nama path untuk mencegah kerentanan *Zip Slip Directory Traversal*.
- Arsip ekspor dibuat di direktori sementara storage (`storage/app/temp/`) dengan nama acak aman dan segera dihapus pasca streaming (`deleteFileAfterSend(true)`).

---

## 4. Hasil Verifikasi & Pengujian

1. **Backend Unit & Feature Test Suite**:
   - `ExtensionControllerTest`: 48/48 pengujian lolos (`php artisan test --filter=ExtensionControllerTest`).
   - `ThemePackageLifecycleTest`: 16/16 pengujian lolos (`php artisan test Modules/Layout/tests`).
2. **Frontend End-to-End Test Suite (Playwright)**:
   - `theme-package-lifecycle.spec.ts`: 4/4 skenario pengujian lolos:
     - Verifikasi tombol unggah dan aksi ekspor pada kartu tema.
     - Verifikasi tombol unggah ZIP dan aksi ekspor pada baris tabel ekstensi.
     - Verifikasi grup pengaturan paket dan keempat sakelar toggle pada tab keamanan.
     - Verifikasi penampilan kapabilitas paket kustom pada tab lisensi.
3. **Pemeriksaan Internasionalisasi (i18n)**:
   - `npm run i18n:check`: 27 gate keys, 9.117 definisi simetris pada `en`, `id`, `su`, format JSON valid.

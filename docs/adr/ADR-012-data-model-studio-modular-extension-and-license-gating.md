# ADR-012: Modularisasi Data Model Studio Menjadi Ekstensi Pluggable Berlisensi Komersial (PRO/Enterprise)

**Status:** Accepted  
**Tanggal:** 2026-09-06  
**Author:** Jejakawan Engineering  
**Scope:** `backend/extensions/data-studio/`, `backend/Modules/Core/`, `frontend/src/modules/Core/Infra/`, `frontend/src/modules/Layout/`  

---

## Konteks & Latar Belakang

Fitur **Data Model Studio** (sebelumnya dikenal sebagai Data Studio pada `sys_content_types` & `sys_dynamic_records`) adalah mesin pemodelan entitas operasional kernel tingkat lanjut yang menyediakan:
1. Pembuatan skema entity kustom dinamis dengan berbagai tipe field (Text, Rich Text, Number, Date, Image, Relation, dll).
2. Validasi field berbasis aturan yang dihasilkan secara otomatis (`DataModelFieldRulesBuilder`).
3. Pembuatan instant REST API otomatis (`/api/v1/dynamic/{slug}`) lengkap dengan dokumentasi OpenAPI terintegrasi.
4. Integrasi dengan Visual Page Builder melalui modul `datamodel_collection` (`DataModelCollection.ts`) untuk merender koleksi dinamis pada halaman web.

### Permasalahan Sebelumnya:
- **Keterikatan Monolitik**: Data Model Studio selalu aktif dan tidak dapat dinonaktifkan dari antarmuka konsol ekstensi (`/ja-dash/extensions/`).
- **Tidak Terkunci Lisensi Komersial**: Kemampuan pembuatan API dan entity dinamis ini bernilai tinggi untuk use-case enterprise/headless CMS, namun belum diproteksi oleh matriks lisensi komersial.
- **Kebutuhan Pluggable**: Pengelola sistem membutuhkan kemampuan untuk menyalakan/mematikan (*on-demand toggle*) modul Data Model Studio per instalasi tanpa risiko kehilangan skema maupun data entitas yang sudah tersimpan (**Zero Data Loss**).

---

## Keputusan Arsitektur

### 1. Manifest Ekstensi Mandiri (`data-studio`)
Dibuat manifest ekstensi baru di `backend/extensions/data-studio/manifest.json`:
- **Slug**: `data-studio` (selaras dengan `group_slug: "studio"` dan dokumentasi arsitektur `data-studio-vs-cck.md`).
- **Tipe**: `plugin`
- **Family**: `infra`
- **Nama Modul**: `Data Model Studio`
- **Lisensi**: `Commercial PRO`
- **License Tier**: `pro`
- **Dependensi**: `core >= 1.0.0`
- **Settings**: `enable_instant_api: true`, `enable_scaffolding: true`

### 2. Gating Lisensi Komersial
- **Matriks Fitur (`LicenseService.php`)**:
  Menambahkan kapabilitas `'data_studio'` yang hanya terbuka pada tier `PRO`, `ENTERPRISE`, dan `WHITE_LABEL`:
  ```php
  'data_studio' => in_array($tier, [self::TIER_PRO, self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true),
  ```
- **License Blocker**:
  `ExtensionHealthService::licenseBlocker()` secara otomatis mencegah instalasi berlisensi `community` mengaktifkan modul dengan feedback penolakan lisensi yang jelas.

### 3. Gating Rute Backend API
Seluruh rute API yang melayani pengelolaan skema dan instant REST API diproteksi oleh middleware `['extension.active:data-studio']` pada `system_api.php`:
- `Route::prefix('manage/infra/models')->middleware(['auth:sanctum', 'extension.active:data-studio'])`
- `Route::prefix('dynamic/{slug}')->middleware(['extension.active:data-studio'])`

Bila ekstensi `data-studio` dinonaktifkan, seluruh panggilan API ke endpoint tersebut ditolak dengan respons **403 Forbidden**:
```json
{
  "success": false,
  "message": "Extension 'data-studio' is not active. Enable it from Module Registry & App Store.",
  "error_code": "DATA_STUDIO_EXTENSION_INACTIVE"
}
```

### 4. Navigasi Konsol & Proteksi Frontend
- **Grup Menu Sidebar (`ConsoleMenu.php`)**:
  - Menambahkan properti `extension_slug: 'data-studio'` pada grup `'studio'` dan item menu `'model-index'`.
  - Menambahkan badge komersial `'badge_text' => 'PRO'`, `'badge_variant' => 'primary'`.
  - `TheSidebar.vue` secara otomatis menyembunyikan grup menu Data Model Studio dari sidebar saat modul nonaktif.
- **Route Guards Frontend (`router/index.ts` & `navigation.ts`)**:
  - Seluruh rute studio (`model-index`, `model-create`, `model-edit`, `dynamic-records-index`, `dynamic-records-create`, `dynamic-records-edit`) kini membawa metadata `meta: { extension: 'data-studio' }`.
  - Rute infra umum lainnya (seperti `file-manager`) tetap terbuka tanpa terpengaruh ekstensi `data-studio`.
- **Integrasi Menu Builder (`SourcePanel.vue`)**:
  - Pilihan sumber menu "Data Models" diproteksi dengan `v-if="isDataStudioActive"`, menghindari request API tak perlu saat modul nonaktif.

### 5. Prinsip Zero Data Loss
- Menonaktifkan ekstensi hanya memperbarui status record pada tabel `sys_extensions` (`status = 'inactive'`).
- Tabel skema `sys_content_types` dan data record `sys_dynamic_records` tetap tersimpan 100% utuh di database. Saat ekstensi diaktifkan kembali, seluruh data langsung dapat diakses tanpa konfigurasi ulang.

---

## Verifikasi & Validasi

1. **Automated Backend Feature Tests (`DataStudioExtensionGateTest.php`)**:
   - `✓ license matrix gates data studio`: Lulus (community: false, pro: true, enterprise: true).
   - `✓ license blocker prevents activation on community`: Lulus.
   - `✓ data studio routes gated by extension active middleware`: Lulus (HTTP 403 saat nonaktif, HTTP 200 saat aktif).
   - `✓ deactivation preserves content types and dynamic records`: Lulus (skema dan record terbukti persisten).

2. **Automated Frontend Unit Tests (`dataStudioAvailability.spec.ts`)**:
   - `✓ assigns data-studio extension metadata to all Data Model Studio routes`: Lulus.
   - `✓ keeps pure infra routes ungated by data-studio extension`: Lulus.
   - `✓ declares data-studio extension in infraNavigation fallback`: Lulus.
   - `✓ filters navigation items when extension is inactive`: Lulus.

3. **Status Health Check**:
   - Hasil audit `ExtensionHealthService::report()` mengembalikan `{ "status": "ok", "issues": [] }` (Healthy, tanpa warning).

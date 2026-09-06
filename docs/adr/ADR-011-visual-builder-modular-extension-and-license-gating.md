# ADR-011: Modularisasi Visual Page & Site Builder Menjadi Ekstensi Pluggable Berlisensi Komersial (PRO/Enterprise)

**Status:** Accepted  
**Tanggal:** 2026-09-06  
**Author:** Jejakawan Engineering  
**Scope:** `backend/extensions/visual-builder/`, `backend/Modules/Layout/`, `backend/Modules/Core/`, `frontend/src/modules/Layout/`, `frontend/src/modules/Publishing/`  

---

## Konteks & Latar Belakang

Sebelumnya, fitur **Visual Page & Site Builder** (`JA-Builder`) tertanam secara monolitik di dalam modul inti `layout` (`Modules/Layout`). Setiap instalasi portal secara otomatis memuat builder tanpa mekanisme kontrol aktivasi modular dari konsol ekstensi (`/ja-dash/extensions/`).

Seiring dengan evolusi model bisnis portal Jejakawan / K2Net:
1. **Fitur Bernilai Tambah Tinggi (High-Value Commercial Module)**:
   - Fitur visual builder drag-and-drop dengan multi-device preview scaling, hardware mockup frames, responsive breakpoint editing, dan block layouting merupakan fitur premium.
   - Fitur ini harus diatur berdasarkan tingkatan lisensi komersial (**PRO**, **Enterprise**, dan **White Label**), sementara instalasi dengan lisensi **Community** tidak diizinkan mengaktifkan modul ini tanpa lisensi yang valid.
2. **Kebutuhan Pluggable (Enable/Disable On-Demand)**:
   - Administrator harus dapat mengaktifkan atau menonaktifkan modul Visual Builder secara instan dari konsol ekstensi (`/ja-dash/extensions/`).
   - Penonaktifan modul harus bersifat non-destruktif (**Zero Data Loss**): blok builder (`builder_blocks`) dan preset layout yang telah tersimpan di database tidak boleh hilang, dan halaman publik yang telah dirancang dengan builder harus tetap ter-render secara utuh bagi pengunjung situs.
3. **Graceful Fallback UI**:
   - Jika modul Visual Builder dinonaktifkan, antarmuka pengeditan konten (`ContentMain.vue`, `Create.vue`, `Edit.vue`) tidak boleh rusak atau memicu error.
   - Sistem harus memberikan fallback yang mulus ke editor Tiptap/Classic standar, menampilkan badge informatif mengenai status modul, serta menyediakan tautan navigasi langsung ke Pengaturan Ekstensi.

---

## Keputusan Arsitektur

### 1. Pembuatan Manifest Ekstensi Mandiri (`visual-builder`)
Dibuat direktori dan manifest ekstensi baru di `backend/extensions/visual-builder/manifest.json`:
- **Slug**: `visual-builder`
- **Tipe**: `plugin`
- **Family**: `cms`
- **License**: `Commercial PRO`
- **License Tier**: `pro`
- **Dependencies**: Memerlukan `layout >= 1.0.0` dan `publishing >= 1.0.0`.
- **Database Registration**: Registrasi awal melalui migration `2026_09_06_000001_register_visual_builder_extension.php` pada tabel `sys_extensions`.

### 2. Gating Lisensi pada `LicenseService` & `ExtensionHealthService`
- **Feature Matrix (`LicenseService.php`)**:
  Menambahkan kapabilitas `'visual_builder'` ke dalam matriks fitur:
  ```php
  'visual_builder' => in_array($tier, [self::TIER_PRO, self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true),
  ```
- **License Blocker Enforcement (`ExtensionHealthService.php`)**:
  Metode `licenseBlocker(Extension $extension)` mengevaluasi tier dari manifest (`license_tier: "pro"`). Apabila tier situs adalah `community` (rank 0), aktivasi ekstensi diblokir secara otomatis dengan pesan:
  `"Lisensi situs (community) tidak mencukupi untuk paket 'visual-builder' (butuh pro)."`

### 3. Perlindungan Rute Backend API (`EnsureExtensionActive`)
Rute-rute API builder pada `backend/Modules/Layout/routes/api.php` diproteksi menggunakan middleware `['extension.active:visual-builder']`:
- `POST /api/v1/manage/layout/builder/generate-blocks`
- `GET /api/v1/manage/layout/builder/dynamic-sources`
- `POST /api/v1/manage/layout/builder/resolve-dynamic`
- `apiResource('builder-presets', BuilderPresetController::class)`

Jika ekstensi `visual-builder` nonaktif, request ke endpoint di atas akan ditolak dengan respons **403 Forbidden**:
```json
{
  "success": false,
  "message": "Extension 'visual-builder' is not active. Enable it from Module Registry & App Store.",
  "error_code": "VISUAL_BUILDER_EXTENSION_INACTIVE"
}
```

### 4. Navigasi Konsol & Route Guard Frontend
- **Item Menu Console (`ConsoleMenu.php`)**:
  Properti `extension_slug` untuk menu Site Editor (`builder.site`) diperbarui dari `'layout'` menjadi `'visual-builder'`.
  Dengan demikian, `TheSidebar.vue` secara otomatis menyembunyikan tautan menu Site Editor dari navigasi konsol saat ekstensi `visual-builder` tidak aktif.
- **Route Guard (`router.ts` & `guards.ts`)**:
  Meta rute `/site-editor` (`builder.site`) dikonfigurasi dengan `meta: { extension: 'visual-builder' }`. Route guard navigasi frontend secara proaktif memblokir akses jika modul tidak terdaftar pada `systemStore.activeExtensions`.

### 5. Adaptasi Reaktif Form Konten (`ContentMain.vue`, `Create.vue`, `Edit.vue`)
- Menggunakan computed property reaktif:
  ```ts
  const isVisualBuilderActive = computed(() => systemStore.activeExtensions?.includes('visual-builder') ?? false);
  ```
- **Saat Ekstensi Aktif**:
  - Tombol aksi utama `"Buka di Visual Builder"` / `"Rancang dengan Visual Builder"` tampil normal dengan styling aksen primer.
- **Saat Ekstensi Nonaktif**:
  - Tombol builder disembunyikan.
  - Ditampilkan badge status `"Modul Tidak Aktif"` dan banner bantuan informatif.
  - Disediakan tombol navigasi cepat `<router-link :to="{ name: 'extensions.index' }">` bertuliskan `"Pengaturan Ekstensi"`.
  - Editor Tiptap di bawahnya tetap dapat digunakan penuh untuk menyunting konten artikel, SEO, dan metadata fallback tanpa kendala.
  - Guard pengaman pada `Create.vue` dan `Edit.vue` memvalidasi status sebelum membuka builder modal, dengan toast notifikasi pencegahan.

### 6. Prinsip Zero Data Loss & Public Rendering Invariance
- Penonaktifan ekstensi dari antarmuka konsol hanya mengubah kolom status pada entri `sys_extensions` (`status = 'inactive'`).
- Tidak ada operasi `DROP`, `TRUNCATE`, atau penghapusan field `builder_blocks` pada `pub_contents.meta` maupun `lay_builder_presets`.
- Modul publik tetap merender konten berbasis blok builder secara sempurna bagi pengunjung situs tanpa gangguan.

---

## Verifikasi & Validasi

1. **Automated Backend Feature Tests (`VisualBuilderExtensionGateTest.php`)**:
   - `test_license_matrix_gates_visual_builder`: Lulus (community: false, pro: true, enterprise: true).
   - `test_license_blocker_prevents_activation_on_community`: Lulus (blocker aktif pada community, lulus saat lisensi PRO diaktifkan).
   - `test_builder_routes_are_gated_by_extension_active_middleware`: Lulus (HTTP 403 saat inactive, HTTP 200 saat active).
   - `test_deactivation_preserves_content_blocks_and_presets`: Lulus (data preset dan blok konten tetap utuh 100% setelah deactivation).

2. **Automated Frontend Unit Tests (`visualBuilderAvailability.spec.ts`)**:
   - `renders visual builder button and emits open-builder when extension is active`: Lulus.
   - `hides builder button and displays inactive badge + extension settings link when extension is inactive`: Lulus.
   - `preserves Tiptap editor fallback when visual-builder is inactive even with builder_blocks present`: Lulus.

3. **Strict TypeScript & i18n Verification**:
   - `npm run type-check`: Lulus dengan kode 0 (27 gate keys, 9173 definitions symmetric, 0 typescript error).

4. **Sinkronisasi Multi-Repository**:
   - Seluruh perubahan, manifest, migrasi, rute, guard UI, dan test suite telah direplikasi dan diverifikasi secara identik di `/home/jejakawan/dev/k2net-portal` dan `/home/jejakawan/dev/smkn6-portal`.

---
status: accepted
scope: layout
date: 2026-09-19
related:
  - ADR-002
  - ADR-008
  - ADR-022
  - ADR-023
---

# ADR-024: Header Menu Alignment Customizer Controls and Multi-Theme Layout Parity

**Status:** Accepted ✅ implemented (2026-09-19)  
**Tanggal:** 2026-09-19  
**Author:** Jejakawan Core Engineering  
**Scope:** `frontend/src/modules/Layout/customizer/`, `frontend/src/modules/Layout/views/themes/`, `backend/Modules/Layout/`  
**Related:** ADR-002 (Customizer page isolation & branding), ADR-008 (Navigation hierarchy alignment), ADR-022 (Theme seeders), ADR-023 (First-party theme packs)

---

## 1. Konteks & Latar Belakang

Pada seluruh tema first-party Jejakawan (`janari`, `layung`, `sarangenge`, `sareupna`), posisi menu navigasi utama desktop (`<nav>`) sebelumnya ditentukan secara statis (*hardcoded*):
- Pada tema **`janari`** dan **`sareupna`**, navigasi diatur merapat ke kanan (`ml-auto pr-8` / `ml-auto pr-6`) mendekati utility dock.
- Pada tema **`layung`** dan **`sarangenge`**, navigasi berada di tengah antara logo dan tombol aksi kanan melalui flexbox `justify-between`.

Pengelola situs dan pengguna tidak memiliki kendali visual di **Theme Customizer** untuk menentukan posisi menu navigasi desktop sesuai preferensi identitas merek (misalnya ingin merapat ke kiri di samping logo, berada di tengah, atau merapat ke kanan).

---

## 2. Keputusan Arsitektur

### 2.1 Standardisasi Field `header_menu_alignment`
1. Ditambahkan ke dalam skema platform Customizer (`global.settings.schema.json`) pada kategori **Layout** dengan cakupan `platform`.
2. Opsi yang didukung:
   - **`center`** (Default untuk seluruh tema): Menempatkan navigasi di tengah (*centered*) di antara logo dan utility dock.
   - **`left`**: Merapatkan navigasi ke sisi kiri mendekati logo merek.
   - **`right`**: Merapatkan navigasi ke sisi kanan mendekati tombol aksi/utility dock.
3. Nilai default ditetapkan secara seragam ke **`center`** pada seluruh tema (`janari`, `layung`, `sarangenge`, `sareupna`).

### 2.2 Reaktivitas Komponen Header & Isolasi Tata Letak
Setiap komponen `Header.vue` first-party mengimplementasikan computed dynamic classes untuk `<nav>`:
- **`center`**: Menggunakan `mx-auto` (atau `mx-auto px-4`), mendistribusikan sisa ruang secara simetris tanpa risiko menumpuk elemen logo atau tombol aksi.
- **`left`**: Menggunakan `mr-auto ml-6 xl:ml-8`, menempelkan navigasi di sebelah kanan logo.
- **`right`**: Menggunakan `ml-auto mr-4 xl:mr-6` (atau `ml-auto pr-8`), menempelkan navigasi di sebelah kiri utility dock.

### 2.3 Perlindungan Viewport & Stabilitas Kontrol
1. Wrapper utility actions / CTA button di sisi kanan diberi kelas `shrink-0` untuk menjamin ikon bahasa, tema, dan tombol aksi tidak terdesak atau pecah pada viewport desktop 1024px–1280px.
2. Drawer navigasi mobile tetap sepenuhnya terisolasi dan mandiri (`Header.vue` mobile drawer menggunakan vertical list layout).
3. Pengaturan ini terhubung langsung dengan Customizer Live Preview Probe melalui atribut `data-ja-customizer-target="nav"`.

### 2.4 Fallback Skema Backend (`ThemeService.php`)
Pada method `ThemeService::getDefaultSettingsSchema()`, ditambahkan definisi `header_menu_alignment` agar tema custom pihak ketiga di masa mendatang otomatis memiliki schema fallback yang lengkap.

---

## 3. Konsekuensi & Dampak

1. **Konsistensi UI/UX**: Seluruh tema Jejakawan memiliki tampilan default menu header yang terpusat rapi (*center aligned*), modern, dan seimbang.
2. **Fleksibilitas Brand**: Pemilik situs dapat beralih ke tata letak kiri (*left-aligned*) atau kanan (*right-aligned*) langsung dari Theme Customizer dengan live preview instan tanpa memuat ulang halaman.
3. **Paritas Multi-Host & Downstream**: Fitur dibangun di upstream `ja-core_engine` dan disinkronkan secara simetris ke seluruh downstream portal (`ja-cms`, `k2net-portal`, `smkn6-portal`, `smkn1cijulang-portal`).

---
status: accepted
scope: layout
date: 2026-09-20
related:
  - ADR-008
  - ADR-012
  - ADR-018
  - ADR-024
---

# ADR-028: Menu Builder Properties, Mega Menu Architecture, and Multi-Theme Parity

**Status:** Accepted ✅ (2026-09-20)  
**Tanggal:** 2026-09-20  
**Author:** Jejakawan Core Engineering  
**Scope:** `frontend/src/modules/Layout/components/menus/`, `frontend/src/modules/Layout/views/themes/`, `backend/Modules/Layout/`  
**Related:** ADR-008 (Navigation hierarchy alignment), ADR-012 (Data Model Studio & source panel), ADR-018 (Plugin slots & theme navigation), ADR-024 (Header menu alignment)

---

## 1. Konteks & Latar Belakang

Menu Builder pada platform Jejakawan Core Engine (`ja-core_engine`) bertugas mengelola struktur navigasi hirarkis multi-level untuk berbagai slot tema (`header`, `footer`, dsb.). Pada pengujian lapangan dan kustomisasi menu, ditemukan beberapa masalah mendasar yang menyebabkan kegagalan penyimpanan konfigurasi serta ketiadaan render visual untuk fitur tingkat lanjut seperti **Mega Menu**:

1. **Validator URL Backend Memblokir Tautan `#` (Anchor)**:
   - Pada `MenuItemUrlValidator::isAllowed()`, aturan `! str_starts_with($trimmed, '#')` secara keliru memblokir tautan dengan awalan `#`.
   - Hal ini menyebabkan item induk dropdown/mega menu (seperti "Program Keahlian" atau "Layanan") atau tautan kustom baru (yang secara default memiliki URL `#`) ditolak dengan HTTP 422 (*Disallowed URL scheme*).
2. **Mismatched Fallback Binding pada Panel Properti (`ItemPropertyField.vue`)**:
   - Komponen input tipe `select` mengevaluasi `:model-value="String(value)"`. Ketika suatu properti berstatus `undefined`, nilai berubah menjadi string literal `"undefined"` yang tidak cocok dengan opsi mana pun (`'default'`, `'grid-2'`, `'primary'`, dll.).
   - Akibatnya, kontrol UI tampak kosong, default schema tidak terpasang, dan pembaharuan nilai gagal terpicu secara reaktif.
3. **Inkonsistensi Skema Tipe Item Menu**:
   - `CustomLinkItem` hanya mendefinisikan layout `'default'` dan `'mega'`.
   - `PageItem` mendefinisikan `'default'`, `'grid-2'`, `'grid-3'`, dan `'full'`.
   - `CategoryItem` dan `PostItem` sama sekali tidak memiliki field `mega_menu_layout`.
   - Properti pendukung seperti `mega_menu_show_dividers` tidak tersedia secara seragam di seluruh tipe item.
4. **Ketiadaan Renderer Mega Menu di Modal Preview (`MenuPreview.vue`)**:
   - Dialog preview menu builder hanya mengimplementasikan dropdown sempit 1 kolom (`min-w-[200px]`), mengabaikan atribut kolom, judul kolom, lencana (*badge*), gambar, maupun tata letak mega menu.
5. **Ketiadaan Renderer Mega Menu di Tema Publik (`Header.vue`)**:
   - Komponen `Header.vue` di seluruh tema first-party (`sarangenge`, `janari`, `layung`, `sareupna`) hanya memiliki implementasi popover vertikal 1 kolom.
   - Tidak ada parser untuk tata letak multi-kolom (`grid-2`, `grid-3`, `grid-4`, `full`), grup kolom (`column_group`), judul visual (`heading`), maupun kartu promosi bergambar.
6. **Limitasi Eager-Loading Backend API (Maksimal 2 Level)**:
   - Endpoint publik `getByLocation` dan `showPublic` pada `MenuController.php` memuat relasi dengan `parentItems.children`.
   - Struktur mega menu membutuhkan kedalaman 3 level: `Root Item` $\rightarrow$ `Column Group` $\rightarrow$ `Sub-item Link`. Level 3 terpotong dan tidak terkirim ke frontend.
7. **Hydration Metadata Terlewat pada Public Fetch (`useMenu.ts`)**:
   - Fungsi publik `fetchMenuByLocation` dan `fetchMenuByIdentifier` langsung menugaskan `menu.parent_items` tanpa menjalankan `hydrateMenuItem()`, sehingga atribut penting (`mega_menu_layout`, `badge`, `image`, dll.) tetap terkubur di sub-objek JSON `metadata`.

---

## 2. Keputusan Arsitektur

### 2.1 Perbaikan Validator URL (`MenuItemUrlValidator.php`)
1. Mengizinkan URL jangkar (`#`, `#anchor`, `#section`) serta path relatif (`/path`, `?query`) yang sah.
2. Mempertahankan pemblokiran terhadap skema berbahaya (`javascript:`, `data:`, `vbscript:`, dan URL skema ganda `//`).

### 2.2 Standardisasi Skema Mega Menu Terpadu
1. **Layout Modes (`mega_menu_layout`)**:
   - `default`: Dropdown vertikal 1 kolom standar.
   - `grid-2`: Mega menu grid 2 kolom (`grid-cols-2`, `min-w-[480px]`).
   - `grid-3`: Mega menu grid 3 kolom (`grid-cols-3`, `min-w-[680px]`).
   - `grid-4`: Mega menu grid 4 kolom (`grid-cols-4`, `min-w-[880px]`).
   - `full`: Mega menu kontainer lebar penuh (*full-width container*).
2. **Atribut Terpadu di Seluruh Registry Item** (`CustomLinkItem`, `PageItem`, `PostItem`, `CategoryItem`):
   - `mega_menu_layout` (select)
   - `mega_menu_column` (number, 1–6)
   - `mega_menu_show_dividers` (boolean)
   - `heading` (text)
   - `hide_label` (boolean)
   - `badge` & `badge_color` (`primary`, `secondary`, `success`, `warning`, `danger`)
   - `image`, `image_size`, & `description` (media card)
3. **Dukungan Tipe Struktur `column_group`**:
   - Memposisikan `column_group` sebagai wadah pengelompokan kolom di bawah item induk mega menu.
   - Menyediakan `heading`, `show_heading_line`, dan daftar anak-anak menu yang terikat pada kolom terkait.

### 2.3 Perbaikan Reaktivitas Panel Properti & Unifikasi Metadata
1. `ItemPropertyField.vue` diperbarui untuk menangani fallback nilai default secara andal:
   ```vue
   :model-value="String(value ?? setting.default ?? '')"
   ```
2. Mutasi properti pada `ItemPropertiesPanel.vue` menyinkronkan properti level atas (`current[key]`) sekaligus sub-objek `current.metadata[key]` agar tidak terjadi divergensi saat serialisasi JSON.

### 2.4 Eager-Loading 3 Level & Hydration Publik
1. `MenuController.php` pada metode publik memuat relasi rekursif 3 level:
   ```php
   with(['parentItems.children.children'])
   ```
2. `useMenu.ts` menjalankan rekursi `hydrateMenuItem()` pada setiap item hasil fetch publik sebelum diberikan ke tema, memastikan `mega_menu_layout`, `badge`, `image`, dsb. siap dibaca langsung oleh template.

### 2.5 Paritas Renderer Mega Menu di Tema Publik & Modal Preview
1. **`MenuPreview.vue`**: Mengimplementasikan template cerdas yang memeriksa `item.mega_menu_layout`. Jika bukan `'default'`, preview merender grid multi-kolom responsif, menampilkan `column_group` dengan judul pemisah, badge warna, dan kartu gambar.
2. **`Header.vue` (Tema Publik)**:
   - Mendeteksi `isMegaMenu(item)`.
   - Mengganti popover sempit dengan panel mega menu lebar yang diposisikan rapi terhadap kontainer navigasi.
   - Merender kolom-kolom navigasi, divider vertikal/horizontal, badge, deskripsi singkat, dan banner media promosi jika disetel.

---

## 3. Konsekuensi & Dampak

1. **Integritas Simpan Menu**: Seluruh input URL dummy `#` dan konfigurasi properti tersimpan mulus tanpa gagal validasi 422.
2. **Kesesuaian WYSIWYG**: Pengguna melihat representasi Mega Menu yang presisi baik di panel canvas (badge Mega Menu), di modal preview builder, maupun di website publik.
3. **Paritas Ekosistem Multi-Tema**: Fitur mega menu menjadi kapabilitas bawaan engine yang dapat langsung dikonsumsi oleh seluruh tema Jejakawan tanpa modifikasi ad-hoc di hilir.

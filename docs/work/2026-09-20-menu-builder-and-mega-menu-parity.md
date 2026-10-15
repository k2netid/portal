# Work Note: Menu Builder Properties, Mega Menu Parity & Documentation System Alignment

**Tanggal:** 2026-09-20  
**Status:** In Progress / Planning & Documented  
**Terkait:** [ADR-028](../adr/ADR-028-menu-builder-properties-mega-menu-and-multi-theme-parity.md), [ADR-024](../adr/ADR-024-header-menu-alignment-customizer-controls-and-layout-parity.md)

---

## 1. Latar Belakang & Keluhan Pengguna
Saat mengonfigurasi properti menu di konsol admin (**Menu Builder**), pengguna menemukan bahwa:
1. Perubahan konfigurasi pada properti (terutama tautan kustom baru ber-URL `#`) gagal tersimpan.
2. Pengaturan tingkat lanjut seperti **Mega Menu** (`mega_menu_layout`, `mega_menu_column`, `heading`, `badge`, `image`) tidak memberikan efek visual pada canvas, modal preview, maupun website publik.
3. Selama beberapa sesi kerja terakhir, pembaruan dokumentasi operasional (`/docs/`), sinkronisasi multi-host, dan changelogs belum ter-maintain secara konsisten.

---

## 2. Analisis Akar Masalah (Root Causes)

| No | Layer | Berkas | Masalah | Dampak |
|:---|:---|:---|:---|:---|
| 1 | Backend Validator | `backend/Modules/Layout/app/Support/MenuItemUrlValidator.php` | Aturan `! str_starts_with($trimmed, '#')` memblokir tautan `#` | Simpan menu gagal dengan HTTP 422 jika URL adalah `#` atau jangkar |
| 2 | Frontend UI Binding | `frontend/src/modules/Layout/components/menus/properties/ItemPropertyField.vue` | `:model-value="String(value)"` mengevaluasi `undefined` jadi `"undefined"` | Input select tampak kosong dan gagal memasang default schema |
| 3 | Item Registry Schema | `frontend/src/modules/Layout/components/menus/registry/types/` | Nilai `mega_menu_layout` berbeda antar tipe (`mega` vs `grid-2`, `grid-3`, `full`) | Inkonsistensi opsi dropdown tata letak bagi pengguna |
| 4 | State Management | `frontend/src/modules/Layout/components/menus/properties/ItemPropertiesPanel.vue` | `updateField` hanya memutasi `current[key]`, bukan `current.metadata[key]` | Potensi desinkronisasi antara properti runtime dan payload JSON |
| 5 | Preview Modal | `frontend/src/modules/Layout/components/menus/preview/MenuPreview.vue` | Hanya ada template 1 kolom sempit (`min-w-[200px]`) | Mega menu tidak tampak saat pengguna menekan tombol Preview |
| 6 | Public Theme Renderer | `frontend/src/modules/Layout/views/themes/sarangenge/components/layout/Header.vue` | Hanya ada popover sempit 1 kolom standar | Mega menu tidak tampak di website publik |
| 7 | Backend Eager-Loading | `backend/Modules/Layout/app/Http/Controllers/Api/MenuController.php` | `with(['parentItems.children'])` hanya memuat 2 level | Level 3 (`column_group -> items`) terpotong dari response publik |
| 8 | Public Hydration | `frontend/src/modules/Layout/composables/useMenu.ts` | `fetchMenuByLocation` tidak menjalankan `hydrateMenuItem()` | Atribut `metadata` tidak ter-unpack ke level atas |

---

## 3. Rencana Tindakan & Milestone

### Fase 1: Dokumentasi, Standarisasi & Changelog (Done)
- [x] Membuat [ADR-028](../adr/ADR-028-menu-builder-properties-mega-menu-and-multi-theme-parity.md).
- [x] Mendaftarkan ADR-028 di [docs/adr/README.md](../adr/README.md).
- [x] Menulis work note [docs/work/2026-09-20-menu-builder-and-mega-menu-parity.md](2026-09-20-menu-builder-and-mega-menu-parity.md).
- [x] Memperbarui root `CHANGELOG.md` dan modul `CHANGELOG.md` (Layout & Sarangenge).
- [x] Memperbarui ops docs di `/home/jejakawan/dev/docs/` dan menjalankan rsync multi-host (`ja-srv`, `ja-aksara`).

### Fase 2: Backend Core Hardening (Done)
- [x] Perbaiki `MenuItemUrlValidator.php` agar mengizinkan `#` dan `#anchor`.
- [x] Perbarui `MenuController.php` eager loading publik: `with(['parentItems.children.children'])`.
- [x] Tambahkan unit & feature test untuk validasi `#` dan hirarki 3-level di `MenuItemPersistTest.php`.

### Fase 3: Frontend Menu Builder Parity (Done)
- [x] Perbaiki binding fallback di `ItemPropertyField.vue` (`value ?? setting.default ?? ''`).
- [x] Seragamkan schema layout di `CustomLinkItem`, `PageItem`, `PostItem`, `CategoryItem`.
- [x] Sinkronkan mutasi `current[key]` dan `current.metadata[key]` di `ItemPropertiesPanel.vue`.
- [x] Beri visual badge "Mega Menu" dan "Col {n}" pada `MenuItemWrapper.vue`.
- [x] Implementasikan rendering Mega Menu multi-kolom di `MenuPreview.vue`.

### Fase 4: Public Theme Rendering (Done)
- [x] Jalankan `hydrateTree()` rekursif pada `fetchMenuByLocation` dan `fetchMenuByIdentifier` di `useMenu.ts`.
- [x] Tambahkan composable bersama `useMegaMenu.ts` untuk resolusi layout, kolom, divider, dan badge.
- [x] Tambahkan renderer Mega Menu cerdas pada `Header.vue` (tema `sarangenge`) untuk desktop dan drawer mobile.

### Fase 5: Build, Sync, & Deploy (In Progress)
- [x] Build frontend `ja-core_engine` & sync aset ke backend.
- [x] Jalankan verification tests (`php artisan test` - 4 passed, 27 assertions).
- [ ] Commit Conventional Commits `feat(...)`.
- [ ] Merge ke `smkn1cijulang-portal` dan deploy ke CT-104.

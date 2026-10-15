# Arsitektur Menu Builder & Mega Menu

Spesifikasi dan kontrak arsitektur sistem navigasi hirarkis serta Mega Menu pada **Jejakawan Core Engine**.

---

## 1. Ikhtisar Sistem Navigasi

Menu pada Jejakawan Core Engine melayani dua ranah:
1. **Admin / Console Shell**: Menggunakan database menu (`sys_console_menus`) dan RBAC capability discovery ([ADR-013](../adr/ADR-013-console-sidebar-database-menu-preloading-and-reactive-hydration.md)).
2. **Public Theme Portals**: Dikelola melalui **Menu Builder** (`lay_menus` & `lay_menu_items`), terikat pada slot tema aktif (`menu_location_header`, `menu_location_footer_col_1`, dsb.), dan disajikan melalui endpoint publik `GET /api/v1/public/layout/menus/location/{location}` atau `GET /api/v1/public/layout/menus/{id}`.

---

## 2. Struktur Data Hirarkis & Kolom Database

Tabel `lay_menu_items` menyimpan seluruh item navigasi dalam model tree relasional:
- `id` (UUID): Identitas unik item.
- `menu_id` (UUID): Induk menu yang memiliki item.
- `parent_id` (UUID|null): Menunjuk ke item induk (rekursif). Item level 1 memiliki `parent_id = null`.
- `title` (string): Label visual navigasi.
- `url` (string|null): Alamat tautan (mendukung internal route, external URL, path relatif, atau jangkar `#`).
- `type` (string): Jenis item (`custom`, `page`, `post`, `category`, `column_group`).
- `target_id` / `target_type`: Relasi polimorfik ke entitas konten.
- `icon` (string|null): Nama ikon Lucide.
- `css_class` (string|null): Kelas kustom CSS.
- `sort_order` (int): Urutan tampilan antar saudara (*siblings*).
- `open_in_new_tab` (bool): Membuka target di tab baru (`target="_blank"`).
- `metadata` (JSON): Kolom fleksibel untuk atribut tingkat lanjut (mega menu, badge, gambar, lokalisasi, visibilitas).

---

## 3. Spesifikasi Kontrak Mega Menu ([ADR-028](../adr/ADR-028-menu-builder-properties-mega-menu-and-multi-theme-parity.md))

### 3.1 Atribut Item Induk (Root Container)
Item root yang ingin membuka Mega Menu mendefinisikan:
- `mega_menu_layout`:
  - `'default'`: Dropdown vertikal 1 kolom standar.
  - `'grid-2'`: Grid 2 kolom (`min-w-[480px]`).
  - `'grid-3'`: Grid 3 kolom (`min-w-[680px]`).
  - `'grid-4'`: Grid 4 kolom (`min-w-[880px]`).
  - `'full'`: Kontainer lebar penuh mengikuti lebar kontainer header.
- `mega_menu_show_dividers`: Menampilkan garis pemisah antar kolom.

### 3.2 Atribut Pengelompokan Kolom (`column_group`)
Item bertipe `column_group` bertindak sebagai pembagi kolom di dalam Mega Menu:
- `heading`: Judul visual kolom (mis. "Teknologi & Rekayasa").
- `show_heading_line`: Garis aksen di bawah judul kolom.
- `hide_label`: Menyembunyikan judul jika hanya ingin mengalirkan item secara visual.
- `mega_menu_column`: Nomor target kolom (1–6).
- `children`: Daftar menu sub-item yang ditampilkan di dalam kolom tersebut.

### 3.3 Atribut Visual Item & Lencana
Setiap sub-item mendukung:
- `badge` (string): Teks lencana (mis. "Baru", "Hot", "LSP-P1").
- `badge_color` (enum): `primary`, `secondary`, `success`, `warning`, `danger`.
- `description` (string): Deskripsi singkat 1 baris di bawah judul.
- `image` (URL string): Kartu media / banner thumbnail.
- `image_size` (enum): `auto`, `landscape_sm`, `landscape_md`, `landscape_lg`, `portrait_sm`, `portrait_md`, `full`.

### 3.4 Visibilitas & Keamanan
- `requires_auth` (bool): Hanya tampil jika pengguna telah login.
- `guest_only` (bool): Hanya tampil bagi tamu yang belum login.
- `required_permission` (string): Hanya tampil bila pengguna memiliki izin Spatie terkait.

---

## 4. Alur Sinkronisasi & Serialisasi

```
[Registry Item Types]
         │ (default values)
         ▼
[MenuBuilder Canvas & Properties Panel]
         │ (updateField & updateMetadataField synced)
         ▼
[toPersistedPayload & flattenForSync]
         │ (metadata packed into JSON)
         ▼
[POST /manage/layout/menus/{id}/items/sync]
         │ (MenuItemUrlValidator allows '#' & relative)
         ▼
[lay_menu_items DB Table]
         │ (eager load: parentItems.children.children)
         ▼
[Public Theme fetch: fetchMenuByLocation]
         │ (hydrateMenuItem unpacks metadata to root)
         ▼
[Header.vue & MenuPreview.vue Mega Menu Renderer]
```

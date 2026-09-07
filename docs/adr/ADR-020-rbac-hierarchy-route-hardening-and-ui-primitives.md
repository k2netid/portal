# ADR-020: Standarisasi Hirarki Role RBAC, Pengamanan Rute Backend (Defense-in-Depth), dan Primitives UI Komprehensif

**Status:** Accepted / Implemented  
**Tanggal:** 2026-09-07  
**Author:** Jejakawan Engineering  
**Scope:** `backend/Modules/Core/app/System/Models/User.php`, `backend/Modules/Core/database/seeders/System/CmsRolesSeeder.php`, `backend/Modules/Core/routes/system_api.php`, `backend/Modules/Publishing/routes/api.php`, `backend/Modules/Media/routes/api.php`, `backend/Modules/Layout/routes/api.php`, `backend/Modules/Library/routes/api.php`, `frontend/src/modules/Core/System/stores/auth.ts`, `frontend/src/engine/stores/auth.ts`, `frontend/src/shared/directives/permission.ts`, `frontend/src/shared/components/auth/Can.vue`, `frontend/src/engine/api/client.ts`, `frontend/src/main-shared.ts`  
**Supersedes / Extends:** Core RBAC & Permission Architecture, Spatie Laravel-Permission Integration  

---

## 1. Konteks & Permasalahan

Sistem Role-Based Access Control (RBAC) pada Jejakawan Core Engine dan portal turunannya menghadapi beberapa kelemahan arsitektural sebelum dilakukan audit menyeluruh:

1. **Disparitas Nilai Bobot Role (*Role Ranks*)**:
   - Terdapat ketidaksinkronan nilai integer rank antar backend (`User::getRoleRankMap()`), store auth konsol (`Core/System/stores/auth.ts`), dan store auth engine (`engine/stores/auth.ts`).
   - Beberapa role operasional modern (seperti `system-admin`, `security-officer`, dan `operator`) belum terdaftar secara konsisten di seluruh lapisan sistem.
2. **Pemusnahan Izin oleh Seeder (`CmsRolesSeeder.php`)**:
   - `CmsRolesSeeder.php` sebelumnya menggunakan `syncPermissions()`. Akibatnya, setiap kali seeder dijalankan (misalnya saat instalasi ekstensi baru atau healing database), seluruh permission yang telah diberikan oleh modul lain (seperti `PublishingPermissionSeeder`, `MediaPermissionSeeder`) langsung terhapus bersih.
3. **Celah Keamanan Rute Backend (Ungated Endpoints)**:
   - Sejumlah endpoint API manajemen penting hanya dilindungi oleh middleware otentikasi umum (`auth:sanctum`), tanpa pengecekan izin Spatie granular (`permission:...`).
   - Contoh: Seorang pengguna dengan role `member` dapat mengakses daftar pengguna sistem (`/api/v1/manage/system/users`), rute manajemen folder media, dan rute layout/library.
4. **Typo Definisi Permission**:
   - Rute SEO pada Publishing module menggunakan middleware `permission:edit seo`, sementara seeder dan database mendaftarkan permission resmi `manage seo`.
5. **Ketiadaan Standar UI/UX untuk RBAC di Frontend**:
   - Belum ada konsensus atau komponen/direktif standar mengenai cara menampilkan elemen ketika user tidak memiliki hak akses (apakah disembunyikan, di-disable, dialihkan, atau menampilkan modal error).

---

## 2. Keputusan Arsitektur

### A. Penyelarasan Skala Bobot Role Terpadu (Unified Role Ranks)

Disepakati skala bobot wewenang resmi yang identik antara backend dan frontend:

| Role | Bobot (*Rank*) | Deskripsi Kewenangan |
| :--- | :---: | :--- |
| `super` | **100** | Pemilik root platform, bypass seluruh pengecekan izin Spatie. |
| `system-admin` | **95** | Administrator infrastruktur, server, dan modul teknis. |
| `admin` | **90** | Administrator institusi / sekolah penuh. |
| `security-officer`| **85** | Auditor keamanan, manajemen log, dan kepatuhan sistem. |
| `operator` | **80** | Staf operasional sekolah/kantor, entri data terpadu. |
| `editor` | **60** | Kepala redaksi / penyunting konten publik, layout, dan media. |
| `author` | **40** | Penulis artikel, pengumuman, dan materi berita. |
| `staff` | **30** | Staf penunjang non-redaksional. |
| `member` | **10** | Anggota terdaftar / siswa / pengunjung terdaftar. |

### B. Seeder Non-Destruktif (Idempotent Role Seeding)

- Pada `CmsRolesSeeder.php`, metode `syncPermissions()` digantikan secara mutlak dengan `givePermissionTo(...)`.
- Seeder melengkapi permission CMS dasar untuk `admin`, `editor`, `author`, dan hak operasional umum untuk `operator` tanpa menghapus hak akses yang telah dialokasikan oleh modul lain.

### C. Pengamanan Rute API Backend (Defense-in-Depth)

Seluruh rute sensitif dikelompokkan dan diproteksi dengan middleware `permission:...`:
- **Core Module**:
  - Pengguna: `permission:view users`, `create users`, `edit users`, `delete users`.
  - Roles: `permission:view roles`, `manage roles`.
  - Pengaturan: `permission:view settings`, `manage settings`.
  - Tugas Terjadwal & Log: `permission:view/manage scheduled tasks`, `view/delete logs`.
- **Publishing Module**: Memperbaiki typo `permission:edit seo` menjadi `permission:manage seo`.
- **Media Module**: Rute operasi folder diproteksi dengan `permission:view media`, `upload media`, `edit media`, `delete media`, `manage media`.
- **Layout Module**: Menus, widgets, dan redirects diproteksi dengan izin spesifik layout.
- **Library Module**: Operasi destruktif kategori dan custom fields diproteksi dengan izin library.

### D. Standarisasi Primitives UI/UX Frontend

Berdasarkan praktik terbaik industri (seperti pada GitHub, AWS Console, dan Salesforce), ditetapkan 4 tingkatan standar penanganan hak akses pada antarmuka:

1. **Strict Hide (Menu Navigasi & Aksi Destruktif)**:
   - Elemen dihapus dari DOM menggunakan direktif `v-can="'permission'"` atau `v-role="'role'"` agar antarmuka tetap bersih dan tidak menimbulkan kebingungan.
   - Contoh: Menu "Pengaturan Sistem", tombol "Hapus Akun".
2. **Disabled + Tooltip (Aksi Transisi Alur Kerja)**:
   - Elemen tetap ditampilkan tetapi dalam keadaan `disabled` disertai tooltip alasan mengapa tombol terkunci, menggunakan modifier `v-can.disabled="'permission'"` atau komponen `<Can :permission="..."> <template #fallback>...</template> </Can>`.
   - Contoh: Tombol "Terbitkan Artikel" bagi Author yang hanya boleh membuat draft.
3. **Graceful Redirect (Akses Halaman Penuh)**:
   - Navigasi URL langsung (deep-link) yang melanggar hak akses dicegat oleh Navigation Guard Vue Router dan diarahkan ke halaman `403 Forbidden` informatif dengan tombol kembali yang jelas.
4. **Global Event Listener (Penolakan API Tak Terduga)**:
   - Interceptor Axios / HTTP client memancarkan `CustomEvent('app:forbidden')` saat menerima kode HTTP 403, memicu notifikasi toast atau pengalihan rute secara elegan tanpa merusak state aplikasi.

---

## 3. Konsekuensi & Keuntungan

1. **Prinsip Hak Akses Terkecil (*Least Privilege*)**: Memastikan pengguna biasa (`member`) tidak memiliki celah akses sedikit pun ke endpoint manajemen data.
2. **Stabilitas Ekstensi Modular**: Penambahan modul baru tidak akan lagi merusak permission role CMS yang sudah ada.
3. **Pengalaman Pengguna Konsisten**: Pengembang UI memiliki komponen `<Can>` dan direktif `v-can` terstandar, menghindari inkonsistensi penanganan izin di masa mendatang.
4. **Full Test Coverage**: Dilengkapi dengan unit test Vitest untuk primitives frontend dan PHPUnit Feature test untuk backend.

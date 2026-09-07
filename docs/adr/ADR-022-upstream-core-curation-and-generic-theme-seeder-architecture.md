# ADR-022: Kurasi Codebase Upstream, Pemisahan Seeder Deployment Klien, dan Arsitektur Generic Theme Demo Seeders

**Status:** Accepted  
**Tanggal:** 2026-09-07  
**Author:** Jejakawan Engineering  
**Scope:** `ja-core_engine`, `k2net-portal`, `smkn6-portal`, `backend/Modules/Layout/`, `backend/database/seeders/`  

---

## Konteks & Latar Belakang

Sebelumnya, pengembangan intensif fitur core platform (Enterprise RBAC Registry, Extension License Gating, Dual-Mode Appearance Workspace, Console Menu Preloading, ADR-011 s.d. ADR-021) dilakukan pada repositori produk klien (`k2net-portal` dan `smkn6-portal`). Hal ini menyebabkan beberapa friksi arsitektural:

1. **Divergensi Upstream Core Engine**:
   - Repositori inti upstream (`ja-core_engine`) tertinggal 154 commit sejak commit `5b03d68` (31 Agustus 2026).
   - Fitur-fitur vital platform (seperti `CapabilityRegistryService`, `php artisan rbac:sync`, middleware `extension.active`) belum terserap ke upstream.
2. **Keterikatan Data Spesifik Klien di Core (*Hardcoded Client Data*)**:
   - Repositori memuat seeder identitas legal spesifik satu perusahaan (`K2netBrandingSeeder.php` yang mengunci PT Kirana Karina Network) dan seeder khusus satu sekolah (`Vocational*Seeder.php` yang mengunci jurusan dan lab riil SMKN 6 Bandung).
   - Upstream engine kehilangan sifat agnostik dan tidak dapat langsung di-*deploy* untuk klien atau instansi baru tanpa membersihkan data manual.
3. **Ketiadaan Standar Starter Demo Data per Tema**:
   - Tiga tema resmi platform (`janari`, `layung`, `sarangenge`) telah memiliki deklarasi frontend generik di `sample-data/bundle.json`, namun di layer backend database seeder belum ada mekanisme modular untuk menginisialisasi starter dataset generik per tema.

---

## Keputusan Arsitektur

### 1. Fast-Forward Sinkronisasi Upstream (`ja-core_engine`)
- Memverifikasi bahwa commit `5b03d68` pada upstream `ja-core_engine` adalah akar leluhur langsung (*direct ancestor / merge-base*) dari 154 commit pengembangan portal.
- Melakukan penggabungan *fast-forward* (`git merge --ff-only`) sehingga `ja-core_engine/main` menyerap seluruh 154 commit tanpa konflik.

### 2. Sanitasi Upstream Core
- Menghapus file seeder spesifik klien (`K2netBrandingSeeder.php`) dari upstream `ja-core_engine`.
- Memastikan upstream engine murni, bersih, dan bebas dari informasi rahasia, legalitas privat, kontak WhatsApp riil, maupun data operasional satu instansi tertentu.

### 3. Pustaka Seeder Demo Generik per Tema (`Themes/*ThemeDemoSeeder.php`)
Dibangun katalog seeder demo resmi di dalam `backend/Modules/Layout/app/Database/Seeders/Themes/`:

1. **`JanariThemeDemoSeeder.php`**:
   - Profil: Portal Publik / Pemerintahan / Komunitas (*"Portal Resmi Komunitas"*).
   - Identitas: Slogan layanan publik terpadu, kontak generik `info@portal-komunitas.id`.
   - Mengaktifkan tema `janari` dan menginstal bundle menu, halaman, dan form kontak.
   - Menginisialisasi kategori publik standar: `berita`, `pengumuman`, `agenda`.
2. **`LayungThemeDemoSeeder.php`**:
   - Profil: Portal Internet Service Provider & Managed Service Provider (*"Portal ISP Nusantara"*).
   - Identitas: Layanan dedicated fiber optic, broadband bisnis, dan IT operations.
   - Mengaktifkan tema `layung` dan menginstal bundle menu, halaman, dan form kontak.
   - Menginisialisasi kategori ISP generik: `layanan-internet`, `managed-services`, `infrastruktur`.
3. **`SarangengeThemeDemoSeeder.php`**:
   - Profil: Portal Sekolah Menengah Kejuruan Pusat Keunggulan (*"SMK Pusat Keunggulan Nusantara"*).
   - Identitas: Pendidikan vokasi berstandar industri, kemitraan DUDI, dan info PPDB.
   - Mengaktifkan tema `sarangenge` dan menginstal bundle menu, halaman, dan form kontak.
   - Menginisialisasi kategori vokasi: `program-keahlian`, `fasilitas`, `prestasi`.
   - Mengisi konten sampel program keahlian generik (RPL, TJKT, DKV, TOI) dan sampel bengkel/lab (Lab Komputer, Lab Jaringan, Studio Multimedia).

### 4. Perintah Konsol Terpadu: `php artisan theme:seed`
Dibuat artisan command baru di `backend/Modules/Layout/app/Console/Commands/ThemeSeedCommand.php`:
```bash
# Inisialisasi tema yang sedang aktif di sistem:
php artisan theme:seed

# Inisialisasi tema tertentu secara spesifik:
php artisan theme:seed layung
php artisan theme:seed sarangenge
php artisan theme:seed janari

# Inisialisasi seluruh dataset tema resmi:
php artisan theme:seed --all
```
Command ini terdaftar secara otomatis melalui `LayoutServiceProvider.php`.

### 5. Pola Pemisahan Seeder Deployment Klien
Repositori turunan klien tidak lagi mencemari kode tema generik, melainkan mengadopsi pola *Inheritance & Override*:

* **K2NET Portal (`/home/jejakawan/dev/k2net-portal`)**:
  - Menggunakan [`K2netDeploymentSeeder.php`](file:///home/jejakawan/dev/k2net-portal/backend/database/seeders/K2netDeploymentSeeder.php).
  - Alur: Menjalankan `LayungThemeDemoSeeder` sebagai fondasi dasar -> Melakukan *override* nama PT (`PT Kirana Karina Network`), logo resmi (`/logofull_k2net.png`), kontak WhatsApp CS, dan konfigurasi rute toko resmi.
* **SMKN 6 Bandung Portal (`/home/jejakawan/dev/smkn6-portal`)**:
  - Menggunakan [`Smkn6DeploymentSeeder.php`](file:///home/jejakawan/dev/smkn6-portal/backend/database/seeders/Smkn6DeploymentSeeder.php).
  - Alur: Menjalankan `SarangengeThemeDemoSeeder` sebagai fondasi dasar -> Melakukan *override* nama sekolah (`SMK Negeri 6 Bandung`), alamat kampus Jalan Riung Bandung No. 1, kontak resmi, dan mengeksekusi seeder vokasi riil (jurusan DPIB, TITL, TPM, TKR, TO).

---

## Dampak & Konsekuensi

### Positif
1. **Zero Merge Conflicts**: Pembaruan core di masa depan cukup di-commit pada `ja-core_engine`, lalu kedua portal klien dapat menjalankan `git pull upstream main` tanpa risiko konflik modify/delete pada aset tema.
2. **Kesiapan Multi-Tenant & Komersial**: Platform `ja-core_engine` siap didistribusikan ke klien baru secara instan cukup dengan memilih tema dan menjalankan `php artisan theme:seed`.
3. **Isolasi Domain Bersih**: Kode bisnis legalitas K2Net dan data internal guru/lab SMKN 6 terisolasi sepenuhnya di repositori masing-masing tanpa bocor ke upstream.

### Panduan Operasional (*Lifecycle Guide*)
* Setiap perbaikan arsitektural umum (RBAC, security, kernel modules, extension system) **wajib dilakukan atau di-*cherry-pick/merge* ke `ja-core_engine/main`**.
* Kustomisasi unik klien hanya boleh ditulis di `backend/database/seeders/*DeploymentSeeder.php` atau `.env` lokal masing-masing portal.

# Git Branching & Multi-Repository Architecture — `Jejakawan Portal Platform`

**Update:** 2026-09-07 (Pasca Kurasi Codebase & Modernisasi `ja-cms` — ADR-022)

Sistem portal Jejakawan beroperasi dengan model **Upstream Core Engine + Downstream Client Deployments**:

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          UPSTREAM CORE REPOSITORY                               │
│              ja-core_engine (git@github.com:jejak-awan/ja-core_engine.git)       │
│              Branch: main                                                       │
│              - Kernel, System, RBAC Registry, Extension Engine                  │
│              - Official Theme Library: Janari, Layung, Sarangenge               │
│              - Generic Theme Demo Seeders (php artisan theme:seed)              │
│              - ZERO Client Data / 100% Platform Agnostic                        │
└────────────────────────────────────────┬────────────────────────────────────────┘
                                         │
                        git pull upstream main (Fast-Forward / Merge)
                                         │
        ┌────────────────────────────────┼────────────────────────────────┐
        ▼                                ▼                                ▼
┌──────────────────────────┐ ┌──────────────────────────┐ ┌──────────────────────────┐
│  DOWNSTREAM JEJAKAWAN    │ │   DOWNSTREAM K2NET       │ │   DOWNSTREAM SMKN 6      │
│ ja-cms                   │ │ k2net-portal             │ │ smkn6-portal             │
│ (origin: ja-cmspro.git)  │ │ (origin: k2netid/portal) │ │ (origin: k2netid/portal) │
│ Branch: main             │ │ Branch: main             │ │ Branch: feat/sarangenge  │
│ Theme: Janari (Default)  │ │ Theme: Layung (ISP/MSP)  │ │ Theme: Sarangenge (SMK)  │
│ Seeder:                  │ │ Seeder:                  │ │ Seeder:                  │
│ JejakawanDeploymentSeeder│ │ K2netDeploymentSeeder    │ │ Smkn6DeploymentSeeder    │
│ Target: jejakawan.com    │ │ Target: k2net.id         │ │ Target: smkn6.sch.id     │
└──────────────────────────┘ └──────────────────────────┘ └──────────────────────────┘
```

---

## 1. Matriks & Peran Repositori

| Repositori | Peran / Scope | Branch Utama | Remote Upstream | Basis Tema | Seeder Deployment Khusus |
| :--- | :--- | :--- | :--- | :--- | :--- |
| [**`ja-core_engine`**](file:///home/jejakawan/dev/ja-core_engine) | **Upstream Source of Truth** | `main` | - | Janari, Layung, Sarangenge | `JanariThemeDemoSeeder`, `LayungThemeDemoSeeder`, `SarangengeThemeDemoSeeder` *(Semua Generic)* |
| [**`ja-cms`**](file:///home/jejakawan/dev/ja-cms) | **Official PT Jejak Awan Digital** | `main` | `ja-core_engine.git` | **`janari`** | [`JejakawanDeploymentSeeder`](file:///home/jejakawan/dev/ja-cms/backend/database/seeders/JejakawanDeploymentSeeder.php) |
| [**`k2net-portal`**](file:///home/jejakawan/dev/k2net-portal) | **PT Kirana Karina Network (ISP)** | `main` | `ja-core_engine.git` | **`layung`** | [`K2netDeploymentSeeder`](file:///home/jejakawan/dev/k2net-portal/backend/database/seeders/K2netDeploymentSeeder.php) |
| [**`smkn6-portal`**](file:///home/jejakawan/dev/smkn6-portal) | **SMK Negeri 6 Bandung (Vokasi PK)** | `feat/theme-sarangenge` | `ja-core_engine.git` | **`sarangenge`** | [`Smkn6DeploymentSeeder`](file:///home/jejakawan/dev/smkn6-portal/backend/database/seeders/Smkn6DeploymentSeeder.php) |

---

## 2. Batasan Kode (*Code Demarcation: What Goes Where*)

### A. Wajib Masuk ke Upstream `ja-core_engine` (Core Scope):
1. **Core Kernel & Modul Standar**:
   - `Modules/Core` (IAM, Security, System, Infra, Data Studio).
   - `Modules/Layout` (Theme engine, customizer, widget architecture, menu rendering).
   - Modul standar: `Publishing`, `Library`, `Forms`, `Mail`, `Media`, `Member`, `Analytics`, `Search`, `Newsletter`.
2. **Katalog Tema Resmi (*Theme Library*)**:
   - Struktur komponen tema publik di `frontend/src/modules/Layout/views/themes/{janari,layung,sarangenge}`.
3. **Seeder Demo Generik**:
   - `JanariThemeDemoSeeder` (Portal Komunitas/Pemerintahan umum).
   - `LayungThemeDemoSeeder` (Portal Bisnis ISP & Managed Services generik).
   - `SarangengeThemeDemoSeeder` (Portal Sekolah Kejuruan Pusat Keunggulan generik).
4. **Artisan Tooling**:
   - `php artisan theme:seed [slug] [--all]`
   - `php artisan rbac:sync`
5. **ATURAN MUTLAK CORE**:
   > [!CAUTION]
   > **ZERO CLIENT-SPECIFIC HARDCODING!** Dilarang keras menuliskan nama legal PT klien, nomor WhatsApp marketing klien, alamat fisik kantor/sekolah, jurusan riil, atau logo berhak cipta klien di dalam upstream `ja-core_engine`.

### B. Wajib Masuk ke Repositori Downstream Klien (Client Deployment Scope):
1. **Deployment Seeder Spesifik**:
   - Berlokasi di `backend/database/seeders/<Client>DeploymentSeeder.php`.
   - Seeder ini **memanggil generic theme seeder** sebagai fondasi, lalu meng-override:
     - `site_name`, `site_title`, `site_tagline`, `contact_email`.
     - Data legalitas perusahaan / sekolah.
     - Logo spesifik (`/logo_jejakawan.png`, `/logofull_k2net.png`, dsb.).
2. **Aset Branding Publik**:
   - File logo dan favicon spesifik di `frontend/public/` dan `backend/public/`.
3. **Konfigurasi Lingkungan Runtime**:
   - `.env`, setting database lokal (PostgreSQL), port Nginx staging/produksi.

---

## 3. Strategi Sinkronisasi Git (*Two-Way River Strategy*)

### A. Menarik Pembaruan dari Upstream ke Downstream (Hilir)
Setiap repositori downstream (`ja-cms`, `k2net-portal`, `smkn6-portal`) memiliki konfigurasi remote:
```bash
git remote -v
# origin   -> git@github.com:... (repo klien)
# upstream -> git@github.com:jejak-awan/ja-core_engine.git (core engine)
```
Saat ada pembaruan arsitektural di upstream:
```bash
git fetch upstream main
git merge upstream/main
```

### B. Mengirimkan Bugfix / Fitur Baru dari Downstream ke Upstream (Hulu)
Jika saat mengerjakan repositori klien seorang agen menemukan bug inti (misal: perbaikan query skema PostgreSQL di modul Mail):
1. **Komit & Push ke `ja-core_engine/main` terlebih dahulu**.
2. **Propagasikan ke seluruh branch downstream aktif** agar seluruh klien menikmati perbaikan bug yang sama.
3. **JANGAN PERNAH** membiarkan bugfix core terisolasi hanya di salah satu repo klien!

---

## 4. Topologi Server & Lingkungan Staging

### Host: `ja-dev` (CT 207 — `192.168.88.71` / `10.20.0.207`)
Semua proses **development, build Vite, test Playwright, dan staging preview** dilakukan di sini:
- **`ja-cms`**: Port **`8082`** | DB PostgreSQL `ja_cms` | Tema `janari`
- **`k2net-portal`**: Port **`8083`** | DB PostgreSQL `k2net_portal_staging` | Tema `layung`
- **`smkn6-portal`**: Port **`8080`** & **`49280`** | DB PostgreSQL `smkn6_portal_dev` | Tema `sarangenge`
- **Database Engine**: PostgreSQL 16 pada `127.0.0.1:5432`
- **Redis / Valkey Hub**: Container CT 206 (`10.20.0.206:6379`)

### Host: `ja-srv` (CT 200 — `192.168.88.66` / `10.20.0.200`)
Hanya sebagai **live production runtime** (`/home/jejakawan/www/*`). Diterbitkan dari `ja-dev` menggunakan script `publish-from-jadev.sh`. Tidak ada kompilasi node/composer di `ja-srv`.

---

## 5. Quality Gate Sebelum Selesai Tugas (*Checklist*)

Setiap agen yang menyelesaikan pekerjaan di repositori manapun wajib memverifikasi:
- [ ] Kompilasi frontend & backend: `npm run agent:verify` (atau build Vite).
- [ ] Sinkronisasi RBAC 11 Modul: `php artisan rbac:sync` (105 permissions terdaftar).
- [ ] Pengujian seeder: `php artisan theme:seed <theme>` atau `php artisan db:seed --class=<Client>DeploymentSeeder`.
- [ ] Tidak meninggalkan file untracked / residu build yang melanggar `.gitignore`.

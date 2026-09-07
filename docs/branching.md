# Git Branching & Multi-Repository Architecture — `Jejakawan Portal Platform`

Update: 2026-09-07 (Pasca Kurasi Codebase — ADR-022)

Sistem portal Jejakawan beroperasi dengan model **Upstream Core Engine + Downstream Client Deployments**:

```
┌─────────────────────────────────────────────────────────────┐
│                 UPSTREAM CORE REPOSITORY                    │
│     ja-core_engine (git@github.com:jejak-awan/ja-core_engine.git)   │
│     Branch: main                                            │
│     - Kernel, System, RBAC Registry, Extension Engine       │
│     - Official Theme Library: Janari, Layung, Sarangenge    │
│     - Generic Demo Seeders (theme:seed)                     │
└──────────────────────────────┬──────────────────────────────┘
                               │
               git pull upstream main (Fast-Forward)
                               │
         ┌─────────────────────┴─────────────────────┐
         ▼                                           ▼
┌─────────────────────────────┐             ┌─────────────────────────────┐
│    DOWNSTREAM K2NET PORTAL  │             │   DOWNSTREAM SMKN6 PORTAL   │
│ k2net-portal (origin: portal)│             │ smkn6-portal (origin: portal│
│ Branch: main                │             │ Branch: feat/theme-sarangenge│
│ Theme: Layung (ISP & MSP)   │             │ Theme: Sarangenge (Sekolah) │
│ Seeder: K2netDeploymentSeeder│             │ Seeder: Smkn6DeploymentSeeder│
└─────────────────────────────┘             └─────────────────────────────┘
```

---

## 1. Topologi Repositori

| Repositori | Peran / Role | Branch Utama | Remote Upstream | Konten Utama |
| :--- | :--- | :--- | :--- | :--- |
| **`ja-core_engine`** | **Upstream Source of Truth** | `main` | - | Platform engine agnostik, core modules, deklarasi manifest RBAC, generic theme seeders |
| **`k2net-portal`** | **K2NET Corporate Deployment** | `main` | `ja-core_engine.git` | Produk portal K2NET ISP/MSP, tema Layung, K2netDeploymentSeeder |
| **`smkn6-portal`** | **SMKN 6 Bandung Deployment** | `feat/theme-sarangenge` | `ja-core_engine.git` | Produk portal SMKN 6 Bandung, tema Sarangenge, Smkn6DeploymentSeeder |

---

## 2. Alur Pengembangan (*Development & Synchronization Workflow*)

### A. Perubahan Arsitektur & Fitur Umum (*Core Changes*)
* Perubahan pada modul inti (Core, Layout engine, RBAC, Security, Forms, Media, Publishing) **wajib dilakukan atau disinkronkan ke `ja-core_engine/main`**.
* Setelah diuji di upstream, repositori downstream memperbarui kode dengan:
  ```bash
  git fetch upstream main
  git merge upstream/main
  ```

### B. Perubahan Spesifik Instansi (*Client-Specific Changes*)
* Kustomisasi unik klien (kontak resmi, legalitas PT, data jurusan/lab sekolah) **hanya boleh ditulis di seeder deployment masing-masing**:
  - `K2netDeploymentSeeder.php` di `k2net-portal`
  - `Smkn6DeploymentSeeder.php` di `smkn6-portal`
* Tidak boleh menyentuh atau mengotori `ja-core_engine` dengan hardcoded nama klien.

---

## 3. Hygiene & Aturan Git

- **Jangan force-push** ke branch `main` di repositori manapun.
- Pastikan seluruh manifest modul menggunakan standar bahasa Inggris canonical (ADR-021).
- Jalankan `php artisan rbac:sync` setelah menambahkan atau memodifikasi kapabilitas di `manifest.json`.
- Gunakan `php artisan theme:seed [slug]` untuk menginisialisasi starter data tema.

Lihat juga: [ADR-022](adr/ADR-022-upstream-core-curation-and-generic-theme-seeder-architecture.md) · [AUDIT-2026-09-07](audit/AUDIT-2026-09-07-codebase-curation-and-upstream-sync.md)

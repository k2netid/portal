# Panduan Agen — SMKN 6 Bandung Portal (`smkn6-portal`)

**Peran:** Downstream Deployment Resmi untuk **SMK Negeri 6 Bandung** (Sekolah Menengah Kejuruan Pusat Keunggulan).
**Basis Arsitektur:** Fork modular dari `ja-core_engine` dengan tema default publik **`sarangenge`**.

---

## 1. Wajib Dibaca Sebelum Mulai (*Mandatory Reading*)
1. [`docs/branching.md`](docs/branching.md) — Matriks multi-repo & sinkronisasi upstream core.
2. [`docs/audit/AUDIT-2026-09-07-codebase-curation-and-upstream-sync.md`](docs/audit/AUDIT-2026-09-07-codebase-curation-and-upstream-sync.md) — Hasil kurasi & standardisasi seeder Sarangenge.
3. [`/home/jejakawan/dev/docs/configs/nginx-smkn6-staging-jadev.conf`](../docs/configs/nginx-smkn6-staging-jadev.conf) — Konfigurasi vhost staging SMKN 6.

---

## 2. Batasan Scope Repositori Ini
- **Yang Ditangani di Repositori Ini**:
  - Konfigurasi identitas resmi SMKN 6 Bandung (Jalan Riung Bandung No. 1, kuota PPDB, fasilitas bengkel/lab).
  - Program keahlian spesifik: DPIB, TITL, TPM, TKR, TO.
  - Seeder khusus: [`backend/database/seeders/Smkn6DeploymentSeeder.php`](backend/database/seeders/Smkn6DeploymentSeeder.php) (memanggil `SarangengeThemeDemoSeeder` sebagai basis).
  - Tema aktif default: **`sarangenge`**.
- **Jika Menemukan Bug Inti / Core Engine**:
  - Jangan hanya diperbaiki di repo ini! Komit dan kirimkan juga ke [`ja-core_engine`](../ja-core_engine).

---

## 3. Remote Git & Sinkronisasi
```bash
# Branch aktif: feat/theme-sarangenge
# origin   -> git@github.com:k2netid/portal.git (repo downstream SMKN 6)
# upstream -> git@github.com:jejak-awan/ja-core_engine.git (upstream core)
```
- **Tarik pembaruan core**:
  ```bash
  git fetch upstream main
  git merge upstream/main
  ```

---

## 4. Lingkungan Staging & Produksi
- **Staging Lokal (`ja-dev` / CT 207)**:
  - Port: **`8080`** dan **`49280`** (`http://192.168.88.71:8080` atau `http://192.168.88.71:49280`).
  - Database: PostgreSQL `smkn6_portal_dev` pada `127.0.0.1:5432`.
  - Nginx vhost: `/etc/nginx/sites-available/smkn6-staging`

---

## 5. Quality Gate Sebelum Selesai
```bash
# 1. Verifikasi linter & test
npm run agent:verify

# 2. Sinkronisasi izin modul
php artisan rbac:sync

# 3. Validasi seeder SMKN 6
php artisan db:seed --class=Smkn6DeploymentSeeder
```

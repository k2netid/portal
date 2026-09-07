# Panduan Agen — K2NET Portal (`k2net-portal`)

**Peran:** Downstream Deployment Resmi untuk **PT Kirana Karina Network** (`k2net.id` — ISP & Managed Services).
**Basis Arsitektur:** Fork modular dari `ja-core_engine` dengan tema default publik **`layung`**.

---

## 1. Wajib Dibaca Sebelum Mulai (*Mandatory Reading*)
1. [`docs/branching.md`](docs/branching.md) — Matriks multi-repo & sinkronisasi upstream core.
2. [`/home/jejakawan/dev/docs/handoff/k2net-portal-agent.md`](../docs/handoff/k2net-portal-agent.md) — Handoff spesifik K2NET.
3. [`/home/jejakawan/dev/docs/runbooks/k2net-portal.md`](../docs/runbooks/k2net-portal.md) — Runbook staging & production K2NET.

---

## 2. Batasan Scope Repositori Ini
- **Yang Ditangani di Repositori Ini**:
  - Konfigurasi identitas legal PT Kirana Karina Network, paket internet broadband & dedicated, WhatsApp sales/support.
  - Seeder khusus: [`backend/database/seeders/K2netDeploymentSeeder.php`](backend/database/seeders/K2netDeploymentSeeder.php) (memanggil `LayungThemeDemoSeeder` sebagai basis).
  - Tema aktif default: **`layung`**.
  - Aset logo: `/logofull_k2net.png`, `/logo.png`.
- **Jika Menemukan Bug Inti / Core Engine**:
  - Jangan hanya diperbaiki di repo ini! Komit dan kirimkan juga ke [`ja-core_engine`](../ja-core_engine).

---

## 3. Remote Git & Sinkronisasi
```bash
# origin   -> git@github.com:k2netid/portal.git (repo downstream K2NET)
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
  - Port: **`8083`** (`http://192.168.88.71:8083`)
  - Database: PostgreSQL `k2net_portal_staging` pada `127.0.0.1:5432`.
  - Nginx vhost: `/etc/nginx/sites-available/k2net-staging`
- **Produksi (`ja-srv` / CT 200)**:
  - Target: Port 8084 di `ja-srv`.

---

## 5. Quality Gate Sebelum Selesai
```bash
# 1. Verifikasi linter & test
npm run agent:verify

# 2. Sinkronisasi izin modul
php artisan rbac:sync

# 3. Validasi seeder K2NET
php artisan db:seed --class=K2netDeploymentSeeder
```

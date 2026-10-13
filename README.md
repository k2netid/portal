# K2NET Portal (`k2net-portal`)

<p align="center">
  <strong>Portal resmi PT Kirana Karina Network — ISP & Managed Services (`k2net.id`)</strong>
</p>

<p align="center">
  <a href="#ringkasan">Ringkasan</a> •
  <a href="#arsitektur">Arsitektur</a> •
  <a href="#menjalankan-dev">Menjalankan</a> •
  <a href="#quality-gates">Pengujian</a> •
  <a href="#sinkronisasi-upstream">Upstream</a>
</p>

> **Clone ini adalah downstream K2NET**, bukan repo kernel. Upstream kernel: [`ja-core_engine`](https://github.com/jejak-awan/ja-core_engine).  
> Identitas runtime: `K2netDeploymentSeeder` + tema publik default **`layung`**.  
> Panduan agen: [`AGENTS.md`](AGENTS.md) · Ops SoT: [`../docs/ja-dev/k2net/agent-handoff.md`](../docs/ja-dev/k2net/agent-handoff.md).

---

## Ringkasan

| Komponen | Spesifikasi |
| :--- | :--- |
| **Produk** | **K2NET Portal** (`k2net.id`) |
| **Repositori** | `k2netid/portal` (folder lokal: `k2net-portal`) |
| **Upstream** | `jejak-awan/ja-core_engine` (merge via `upstream`) |
| **Arsitektur** | Modular monolith (Laravel) + Vue SPA (console + public site) |
| **Versi** | `1.0.0-beta.2` |
| **Tema default** | `layung` |
| **Database** | PostgreSQL (`k2net_portal_staging` di staging ja-dev) |
| **Runtime** | PHP 8.2+ / 8.5 · Laravel 13 · Node.js 22+ · Vue 3.5 · Vite 8 |

Bagian `docs/guides/`, `docs/reference/`, dan `docs/architecture/` adalah **mirror kebijakan kernel** (lihat `.upstream-sot.md` di folder tersebut). Jangan rewrite massal — perbarui di upstream lalu sync. Dokumen khusus K2NET: `AGENTS.md`, seeder deployment, tema layung, modul `Billing`.

---

## Arsitektur

```
k2net-portal/
├── backend/                 # Laravel modular kernel + packs
│   ├── Modules/             # Core, Layout, Member, Billing, Site, …
│   ├── database/seeders/    # K2netDeploymentSeeder
│   └── public/              # SPA entry + synced assets
├── frontend/                # Vue 3 console + public theme SPA
│   └── src/modules/Layout/views/themes/layung/
├── docs/                    # Workflow + mirrors upstream + ADR lokal
└── scripts/                 # deploy/sync/quality
```

---

## Menjalankan Dev

### Backend
```bash
cd backend
composer install
php artisan migrate --force
php artisan db:seed --force
php artisan db:seed --class=K2netDeploymentSeeder
php artisan serve --host=0.0.0.0 --port=8000
```

### Frontend
```bash
cd frontend
npm install
npm run dev
```

Staging ja-dev (origin `:8083`): lihat [`AGENTS.md`](AGENTS.md) dan handoff ops.

---

## Quality Gates

```bash
npm run agent:verify
```

Detail: [`docs/guides/run-quality-gates.md`](docs/guides/run-quality-gates.md) · Testing: [`docs/reference/testing.md`](docs/reference/testing.md).

E2E smoke (Docker Playwright — host PVE tidak support browser lokal):
```bash
npm run test:e2e:smoke:docker
```

---

## Sinkronisasi Upstream

```bash
# origin   -> git@github.com-k2net:k2netid/portal.git
# upstream -> git@github.com:jejak-awan/ja-core_engine.git

git fetch upstream main
git merge upstream/main
```

Setelah merge: pastikan banner identitas K2NET / `AGENTS.md` / seeder tidak hilang. Bug inti → dual-commit ke `ja-core_engine`. Fitur khusus K2NET (Billing, copy layung, seeder) tetap di repo ini saja.

Lihat [`docs/branching.md`](docs/branching.md).

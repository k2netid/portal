# Panduan Agen — K2NET Portal (`k2net-portal`)

**Peran:** Downstream deployment resmi untuk **PT Kirana Karina Network** (`k2net.id` — ISP & Managed Services).  
**Basis arsitektur:** Fork modular dari `ja-core_engine` dengan tema default publik **`layung`**.

> Clone ini **bukan** SoT kernel. Perubahan inti → dual-commit ke [`ja-core_engine`](../ja-core_engine).  
> Guides/reference/architecture di `docs/` yang bertanda `.upstream-sot.md` adalah mirror — jangan rewrite massal.

---

## 1. Wajib dibaca sebelum mulai

1. [`docs/branching.md`](docs/branching.md) — Matriks multi-repo & sinkronisasi upstream core.
2. [`../docs/ja-dev/k2net/agent-handoff.md`](../docs/ja-dev/k2net/agent-handoff.md) — Handoff spesifik K2NET (ops SoT di `dev/docs`).
3. [`../docs/ja-srv/k2net/runbook.md`](../docs/ja-srv/k2net/runbook.md) — Runbook production K2NET di `ja-srv`.
4. [`docs/WORKFLOW.md`](docs/WORKFLOW.md) — Urutan kerja: audit → diskusi → rencana → implement → verify → catat.
5. [`docs/AGENT_START_HERE.md`](docs/AGENT_START_HERE.md) — Konteks kernel (baca dengan banner downstream di atasnya).

---

## 2. Batasan scope repositori ini

- **Yang ditangani di sini**:
  - Identitas legal PT Kirana Karina Network, paket broadband/dedicated, WhatsApp sales/support.
  - Seeder: [`backend/database/seeders/K2netDeploymentSeeder.php`](backend/database/seeders/K2netDeploymentSeeder.php).
  - Tema aktif default: **`layung`**. Edit tema di FE SoT saja — lihat [`docs/themes/locations-and-sync.md`](docs/themes/locations-and-sync.md).
  - Identitas brand/sosial: **seeder-only**, jangan rewrite default `theme.json` engine — [`docs/product/k2net-identity-and-theme-defaults.md`](docs/product/k2net-identity-and-theme-defaults.md).
  - Modul member self-billing (`Billing`) sebagai klien API `ja-cp`.
  - Aset logo: `/logofull_k2net.png`, `/logo.png`.
- **Bug inti / core engine**: jangan hanya diperbaiki di sini — commit juga ke [`ja-core_engine`](../ja-core_engine).

---

## 3. Remote Git & sinkronisasi

```bash
# origin   -> git@github.com-k2net:k2netid/portal.git
# upstream -> git@github.com:jejak-awan/ja-core_engine.git

git fetch upstream main
git merge upstream/main
```

Setelah merge upstream: verifikasi banner README/`AGENTS.md`/seeder K2NET masih utuh.

---

## 4. Lingkungan staging & produksi

- **Staging (`ja-dev` / CT 207)**: port **`8083`**, DB `k2net_portal_staging`, nginx `k2net-staging`.
- **Produksi (`ja-srv` / CT 200)**: origin port **8084** → `k2net.id`.

Detail: handoff + runbook di atas.

---

## 5. Quality gate sebelum selesai

```bash
npm run agent:verify
# di backend/, bila relevan:
php artisan rbac:sync
php artisan db:seed --class=K2netDeploymentSeeder
```

How-to: [`docs/guides/run-quality-gates.md`](docs/guides/run-quality-gates.md) · Testing: [`docs/reference/testing.md`](docs/reference/testing.md) · E2E: [`docs/guides/run-e2e-playwright.md`](docs/guides/run-e2e-playwright.md) · CLI: [`docs/reference/cli-commands.md`](docs/reference/cli-commands.md) · Changelog: [`docs/guides/update-changelog.md`](docs/guides/update-changelog.md) · Versioning: [`docs/guides/release-and-versioning.md`](docs/guides/release-and-versioning.md) · [`CONTRIBUTING.md`](CONTRIBUTING.md).

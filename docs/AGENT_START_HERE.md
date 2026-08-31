# Agent Start Here — Jejakawan Core Engine

## Cerita produk

**`ja-core_engine`** adalah fork/evolusi dari **`ja-cms`**. Tujuan fork:

- Menjadi **master kernel** untuk membangun berbagai aplikasi Jejakawan (CMS, portal, SaaS tenant, dsb.).
- Memisahkan **fondasi operasional** (IAM, infra, security, data studio) dari **produk vertical** (Content, billing member, dsb.).

Repo ini **bukan** `ja-control-plane`. Control plane (`ja-control-plane`) tetap hub lisensi, billing, dan provisioning multi-tenant. Core engine **bisa** phone-home ke JA-CP untuk aktivasi lisensi, tapi identitas produk di sini adalah **Core Engine**.

## Branch canonical

| Branch | Peran |
| :--- | :--- |
| **`main`** | Trunk — kernel + CMS packs (PR #14). Default basis semua kerja. |
| **`integrate/<domain>`** | Sementara — gelombang besar saja; merge ke `main` lalu hapus. (`integrate/cms` sudah merged.) |
| **`feat/…` / `fix/…`** | Pendek — PR ke `main` (atau ke integrate aktif); hapus setelah merge. |

Kebijakan penuh: [branching.md](branching.md).  
Branch **`develop`** dihapus Aug 2026. Snapshot status: [architectural-status.md](architectural-status.md).

## Struktur wajib dibaca

1. [docs/architectural-status.md](architectural-status.md) — apa yang live vs legacy
2. [docs/architecture/01-overview-and-tier-design.md](architecture/01-overview-and-tier-design.md) · [Data Studio vs CCK](architecture/data-studio-vs-cck.md) · [ja-CE audit 2026-08-27](architecture/ja-ce-comprehensive-audit-2026-08-27.md)
3. [docs/product/downstream-apps-and-licensing.md](product/downstream-apps-and-licensing.md)
4. [docs/extensions/module-contract.md](extensions/module-contract.md) — kontrak modul optional (Mail = golden sample)
5. [docs/extensions/lifecycle.md](extensions/lifecycle.md) — dual boot, deactivate vs uninstall
6. [docs/extensions/member-area.md](extensions/member-area.md) — RFC portal pembaca adaptif
7. [docs/extensions/rbac-and-lifecycle-seeders.md](extensions/rbac-and-lifecycle-seeders.md) — RBAC enable/disable + audit seeder
8. [docs/extensions/external-module-packaging.md](extensions/external-module-packaging.md) — packaging pack eksternal / CMS extract
6. [AGENTS.md](../AGENTS.md)

## Quality gate sebelum selesai

```bash
npm run agent:verify
```

## Dokumentasi modul

Setiap modul di `backend/Modules/*` dan `frontend/src/modules/*` wajib punya **`README.md`** + **`CHANGELOG.md`**. Rule: `.cursor/rules/module-documentation.mdc`.

## Aturan singkat

- Jangan sebut produk ini `ja-cms` / `JA-CMS` di string user-facing baru.
- JA-CP = licensing hub eksternal (OK). Jangan sebut engine ini "control plane".
- Perubahan minimal; jangan refactor di luar scope task.
- Jangan commit secret / `.env`.

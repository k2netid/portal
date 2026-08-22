# Agent Start Here — Jejakawan Core Engine

## Cerita produk

**`ja-core_engine`** adalah fork/evolusi dari **`ja-cms`**. Tujuan fork:

- Menjadi **master kernel** untuk membangun berbagai aplikasi Jejakawan (CMS, portal, SaaS tenant, dsb.).
- Memisahkan **fondasi operasional** (IAM, infra, security, data studio) dari **produk vertical** (Content, billing member, dsb.).

Repo ini **bukan** `ja-control-plane`. Control plane (`ja-control-plane`) tetap hub lisensi, billing, dan provisioning multi-tenant. Core engine **bisa** phone-home ke JA-CP untuk aktivasi lisensi, tapi identitas produk di sini adalah **Core Engine**.

## Branch canonical

| Branch | Peran |
| :--- | :--- |
| **`main`** | Kernel slim: `Modules/Core` (`System`, `Infra`, `Security`) + unified console SPA |
| **`develop`** | Line legacy CMS — tier Content, themes, member portal (87+ commit di depan `main` saat audit Aug 2026) |

Untuk kerja agent di **`main`**: jangan asumsikan modul `Content`, `Operational`, atau rute member/platform billing ada.

## Struktur wajib dibaca

1. [docs/architectural-status.md](architectural-status.md) — apa yang live vs legacy
2. [docs/architecture/01-overview-and-tier-design.md](architecture/01-overview-and-tier-design.md)
3. [docs/product/downstream-apps-and-licensing.md](product/downstream-apps-and-licensing.md)
4. [AGENTS.md](../AGENTS.md)

## Quality gate sebelum selesai

```bash
npm run agent:verify
```

## Aturan singkat

- Jangan sebut produk ini `ja-cms` / `JA-CMS` di string user-facing baru.
- JA-CP = licensing hub eksternal (OK). Jangan sebut engine ini "control plane".
- Perubahan minimal; jangan refactor di luar scope task.
- Jangan commit secret / `.env`.

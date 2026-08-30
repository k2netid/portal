# Architectural Status — kernel + CMS line

Update: 2026-08-30

Canonical integration branch for this snapshot: **`integrate/cms`** (rename dari `fix/module-registry-p0-kernel-lock`; ahead of `main` for CMS packs, member, and `/site`).  
Branching model: [branching.md](branching.md) — trunk `main` + integrate sementara + feat/fix pendek.

## Kernel (`main` lineage)

| Area | Status | Path |
| :--- | :--- | :--- |
| System IAM | **Live** | `backend/Modules/Core/app/System/` |
| Infra (data studio, backup, webhooks) | **Live** | `backend/Modules/Core/app/Infra/` |
| Security (RBAC/ABAC, SIEM, CSP) | **Live** | `backend/Modules/Core/app/Security/` |
| Unified console SPA | **Live** | `frontend/src/modules/Core/` |
| Extension engine | **Live** | Registry + Mail golden sample |
| License → JA-CP | **Live** | `LicenseService`, `license:check` |
| Downstream bootstrap | **Live** | `scripts/bootstrap-downstream-app.sh` |

## CMS packs on this branch (P0–P13)

Optional first-party modules, gated by `sys_extensions.status` (nwidart stays booted):

| Pack | Role |
| :--- | :--- |
| publishing, library, media, layout | Editorial + theme builder |
| forms, newsletter, analytics, search, cms-ai | Audience / insight |
| **member** | Reader accounts (`mem_members`, `auth:member`) — not console IAM |
| **site** | Public theme runtime at **`/site`** (console stays **`/dash`**) |

Public surfaces landed: search, contact → Forms (`contact` slug), analytics pageviews, newsletter footer, widgets, marketing routes, `/site` 404, member login/register/account, member verify-email via Mail.

P6: kernel Identity owns `general` (site name / tagline). Publishing settings own `seo` + `comments` only (`/dash/publishing/settings`). Analytics retention lives on the Analytics pack. Kernel API refuses `seo` / `comments` / `analytics`. Identity **Media** tab is object storage (S3/FTP/disk); editorial library is the **media** pack. Core File Manager stays in Infra.

P4: Data Studio (`sys_content_types`) is operational entities; Library `lib_fields` is CMS CCK. Reserved slugs block CMS collisions. See [data-studio-vs-cck.md](architecture/data-studio-vs-cck.md).

Mail SHOULD (archive folder, storage quota, attachment extension + MIME blocklist) is live in JA-Mail.

## Still open (docs vs backlog)

| Item | Notes |
| :--- | :--- |
| ja-CE honesty pass (pre-P5) | Landed 2026-08-27 — [audit](architecture/ja-ce-comprehensive-audit-2026-08-27.md). P5 still deferred. |
| P2 refine leftover (2, 7, 9–10, 12, 15–18) | Landed 2026-08-28. Public SPA defers Member/Analytics; Data Studio grandfathers reserved slugs; member verify gates bookmarks/comments; console Members list; pack tests; PHP 8.2 / Laravel 13 claims; SafeHtml `publishing`; Identity Media vs Media pack; SEO/Discussion tabs live on Publishing. |
| P5 vertical product modules | Same Mail contract; catalog grows later — needs a named product |
| GitHub Actions | Skipped until billing; local Playwright via Podman |
| Uninstall drop tables | First-party uninstall blocked; plugins only |

## Removed from repo (Aug 2026)

| Item | Notes |
| :--- | :--- |
| Branch **`develop`** | Old CMS line name — packs now live as optional modules on this kernel branch |
| CI `develop` trigger | Single canonical `main`; CMS line = `integrate/cms` until merge |

Console IAM (`srv_auth_users`) is **not** the public reader model. Member pack is optional CMS/audience, not ja-control-plane billing.

## Downstream / JA-CP

Platform billing, multi-tenant provisioning, and JA-CP licensing hub stay **outside** this repo. Fork + extra modules still apply for vertical products. See [bootstrap-downstream-app.md](product/bootstrap-downstream-app.md).

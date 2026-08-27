# Architectural Status — kernel + CMS line

Update: 2026-08-27

Canonical git branch for this snapshot: **`fix/module-registry-p0-kernel-lock`** (ahead of `main` for CMS packs, member, and `/site`).

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

Public surfaces landed: search, contact → Forms (`contact` slug), analytics pageviews, newsletter footer, widgets, marketing routes, `/site` 404, member login/register/account.

## Still open (docs vs backlog)

| Item | Notes |
| :--- | :--- |
| Member email verification | `email_verified_at` column only; no Mail send/verify flow |
| P4 DMS/CCK vs Data Studio | Registry architecture audit — not started |
| P5 vertical product modules | Same Mail contract; catalog grows later |
| P6 Core settings residue | SEO / Analytics / Discussion leftovers in kernel |
| GitHub Actions | Skipped until billing; local Playwright via Podman |
| Mail SHOULD list | Archive folder, quota, MIME blocklist — not merge blockers |
| Uninstall drop tables | First-party uninstall blocked; plugins only |

## Removed from repo (Aug 2026)

| Item | Notes |
| :--- | :--- |
| Branch **`develop`** | Old CMS line name — packs now live as optional modules on this kernel branch |
| CI `develop` trigger | Single canonical `main`; this CMS line is a feature branch until merge |

Console IAM (`srv_auth_users`) is **not** the public reader model. Member pack is optional CMS/audience, not ja-control-plane billing.

## Downstream / JA-CP

Platform billing, multi-tenant provisioning, and JA-CP licensing hub stay **outside** this repo. Fork + extra modules still apply for vertical products. See [bootstrap-downstream-app.md](product/bootstrap-downstream-app.md).

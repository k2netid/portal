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
| publishing, library, media, layout | Editorial + theme builder (**Janari** = default CMS reference theme). **Surfaces:** Site Editor = page/`builder_blocks` content + merged theme settings schema + live `/` preview; Theme Customizer = chrome/settings/CSS/bindings + menu slots; Menu Builder = menu trees + `location` (syncs theme `menu_location_*`). Deep-linked; content blocks still Janari-only. |
| forms, newsletter, analytics, search, cms-ai | Audience / insight |
| **member** | Reader accounts (`mem_members`, `auth:member`) — not console IAM |
| **site** | Public theme runtime at **apex `/`** when pack active (console stays **`/dash`** + `/auth/console-*`; legacy `/site/*` redirects) |

Public surfaces landed: search, contact → Forms (`contact` slug), analytics pageviews, newsletter footer, widgets, marketing routes, public 404, member login/register/account, member verify-email via Mail. **Boot gate:** Site pack off → `/` = console login; Site on → `/` = public web.

P6: kernel Identity owns `general` (site name / tagline). Publishing settings own `seo` + `comments` only (`/dash/publishing/settings`). Analytics retention lives on the Analytics pack. Kernel API refuses `seo` / `comments` / `analytics`. Identity **Media** tab is object storage (S3/FTP/disk); editorial library is the **media** pack. Core File Manager stays in Infra.

P4: Data Studio (`sys_content_types`) is operational entities; Library `lib_fields` is CMS CCK. Reserved slugs block CMS collisions. See [data-studio-vs-cck.md](architecture/data-studio-vs-cck.md).

Mail SHOULD (archive folder, storage quota, attachment extension + MIME blocklist) is live in JA-Mail.

## Still open (docs vs backlog)

| Item | Notes |
| :--- | :--- |
| Merge gate `integrate/cms` → `main` | Honesty + P2 + W5 partial landed. `npm run agent:verify` green (2026-08-30). Ready for PR when you want merge. |
| W5 naming residue | Partial: Media/Library defaults + theme docs use `publishing` / `Layout/*`. Keep API alias `Jejakawan`→`publishing`; do not rewrite customized console menus or seed brand strings. |
| P5 vertical product modules | Deferred until merge + named product. Same Mail contract. |
| GitHub Actions | Skipped until billing; local Playwright via Podman |
| Uninstall drop tables | First-party uninstall blocked; plugins only |
| PHP 8.5 + PhpSpreadsheet 1.x | `maatwebsite/excel` **3.1** pins `phpoffice/phpspreadsheet ^1.30` (composer platform ignore). Prefer upgrade path: **Excel 4.x → PhpSpreadsheet 2+/5+** (official 8.5), or streaming alts **openspout/openspout** / **rap2hpoutre/fast-excel** for export-heavy jobs. Do not stay on 1.x long-term. |

**Branch hygiene (2026-08-30):** remote heads = `main` + `integrate/cms` only. `feat/mail-*` absorbed and deleted.

## Removed from repo (Aug 2026)

| Item | Notes |
| :--- | :--- |
| Branch **`develop`** | Old CMS line name — packs now live as optional modules on this kernel branch |
| CI `develop` trigger | Single canonical `main`; CMS line = `integrate/cms` until merge |

Console IAM (`srv_auth_users`) is **not** the public reader model. Member pack is optional CMS/audience, not ja-control-plane billing.

## Downstream / JA-CP

Platform billing, multi-tenant provisioning, and JA-CP licensing hub stay **outside** this repo. Fork + extra modules still apply for vertical products. See [bootstrap-downstream-app.md](product/bootstrap-downstream-app.md).

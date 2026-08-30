# Architectural Status — kernel + CMS line

Update: 2026-08-30

Canonical trunk: **`main`** (CMS packs, member, and `/site` landed via [PR #14](https://github.com/jejak-awan/ja-core_engine/pull/14), 2026-08-30).  
`integrate/cms` merged and should be deleted. Branching model: [branching.md](branching.md).

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
| Merge gate `integrate/cms` → `main` | **Done** — PR #14 merged 2026-08-30 (`7b5180f`). |
| W5 naming residue | Partial: Media/Library defaults + theme docs use `publishing` / `Layout/*`. Keep API alias `Jejakawan`→`publishing`; do not rewrite customized console menus or seed brand strings. |
| P5 vertical product modules | Unblocked by merge; still needs a named product before build-out. Same Mail contract. |
| GitHub Actions | Org spending limit blocked CI on the merge PR; local Playwright via Podman |
| Uninstall drop tables | First-party uninstall blocked; plugins only |
| PHP 8.5 + spreadsheets | **Done:** `maatwebsite/excel` **4.0.2** + `phpoffice/phpspreadsheet` **^5.3** (no platform ignore). Streaming alts (`openspout` / `fast-excel`) remain optional for export-heavy jobs. |

**Branch hygiene (2026-08-30):** trunk = `main` only after CMS merge. Delete leftover `integrate/cms` remote/local. `feat/mail-*` already absorbed.

## Removed from repo (Aug 2026)

| Item | Notes |
| :--- | :--- |
| Branch **`develop`** | Old CMS line name — packs now live as optional modules on this kernel branch |
| CI `develop` trigger | Single canonical `main` (CMS packs on trunk) |

Console IAM (`srv_auth_users`) is **not** the public reader model. Member pack is optional CMS/audience, not ja-control-plane billing.

## Downstream / JA-CP

Platform billing, multi-tenant provisioning, and JA-CP licensing hub stay **outside** this repo. Fork + extra modules still apply for vertical products. See [bootstrap-downstream-app.md](product/bootstrap-downstream-app.md).

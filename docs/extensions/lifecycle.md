# Extension lifecycle: dual boot, deactivate, uninstall

Jejakawan keeps two switches on purpose. Mixing them is how operators lose data or think a pack is “off” when PHP is still running.

## Dual boot (nwidart vs registry)

| Layer | Source | Meaning |
|-------|--------|---------|
| Package boot | `modules_statuses.json` + `module.json` | Code, providers, and migrations are discoverable |
| Product active | `sys_extensions.status = active` | APIs, console menus, and public contribution points are usable |

First-party packs stay **booted** so the kernel can resolve classes. **Deactivate** is the product off switch: hide menus, skip gated routes, stop observers that check registry status.

Do not unload nwidart providers when a CMS pack is deactivated. Kernel (`core`) is always product-active.

## Deactivate vs uninstall

| Action | What it does | Data | Disk |
|--------|----------------|------|------|
| Deactivate | `status = inactive`, hide menus, skip product routes | Kept | Kept |
| Uninstall | Remove registry row + plugin files | Optional rollback (`keep_data`) | **Deletes plugin folder** |

Rules:

1. **First-party `Modules/*` packs cannot be uninstalled.** Deactivate them. Shipping Layout/Publishing/Member/Site as deletable ZIP plugins would wipe the product.
2. **Kernel slugs cannot be deactivated or uninstalled.**
3. **Uninstall is for uploaded plugins** (type `plugin` / not an in-tree `Modules/{Name}` folder).
4. Custom console menus stay sacred: deactivate hides by `extension_slug`, it does not delete operator edits.

## Contribution points

Manifests may declare `contribution_points` in addition to `permissions` and `features`:

- `permissions` — seeded on activate, never deleted on deactivate
- `widgets` — types a pack contributes to Layout widget areas (`html`, `text`, `recent_posts`, …)
- menus — still merged via `ConsoleMenu::ensureMissingDefaults()` on activate
- **Planned:** `member_area` — reader portal nav/routes/widgets (see [member-area.md](./member-area.md))
- **Planned:** `lifecycle.seeders_on_activate` — idempotent domain seeders (see [rbac-and-lifecycle-seeders.md](./rbac-and-lifecycle-seeders.md))

Public theme (apex `/` when Site is active) reads `GET /api/v1/public/layout/widgets/location/{sidebar|footer_bottom}`. Empty while pack `layout` is inactive. `recent_posts` / `categories` items hydrate from Publishing / Library only when those packs are product-active.

## Data on deactivate (hard rule)

| Allowed | Not allowed |
| :--- | :--- |
| Flip `status`, hide menus, 403 gated APIs | Migration rollback |
| Soft flags / cache invalidation via `extension_deactivated` | Truncate pack tables |
| Keep Spatie permission rows | Silent delete of operator role grants (default) |

Destructive cleanup = plugin uninstall or explicit future purge CLI — never App Store “Deactivate”.

## Public identity

Visitor accounts are pack **member** / **readers** (`mem_members`, `auth:member`). Console IAM (`srv_auth_users`) is **operators** only. Public comments and bookmarks use `MemberIdentityPort`, not `User`.

Do not confuse Spatie role name `member` (operator subscription self-service) with `mem_members` rows. Naming guidance: [rbac-and-lifecycle-seeders.md](./rbac-and-lifecycle-seeders.md), portal plan: [member-area.md](./member-area.md).

Register still issues a Sanctum token so the reader can call member APIs. `email_verified_at` is set only after the signed Mail link (`GET /api/v1/public/member/verify-email/{id}/{hash}`). Send uses `OutboundMailPortInterface` when JA-Mail is bound, otherwise Laravel `Mail::html`. Authenticated resend: `POST /api/v1/member/email/verification-notification`. Browser clicks redirect to `/member/verified` (legacy `/site/member/*` redirects when present).

## Public apex (Site on)

| Surface | Pack / API |
| :--- | :--- |
| Theme home, blog, post, marketing pages | site + layout + publishing |
| Search | `GET /api/v1/public/search` |
| Contact | Forms slug `contact` (seeded when Forms is active) |
| Newsletter footer | `POST /api/v1/public/newsletter/subscribe` |
| Pageviews | `POST /api/v1/public/analytics/track-visit` |
| Member verify-email | Signed verify → `/member/verified` |
| Member portal (planned adaptive shell) | [member-area.md](./member-area.md) |
| Publishing settings | SEO + discussion at `/dash/publishing/settings`; kernel settings API refuses those groups |

Console remains `/dash`. Do not send public 404 Home to the console SPA.

## Still not this document

Vertical modules and GitHub Actions billing — see [architectural-status.md](../architectural-status.md). Data Studio vs CCK: [data-studio-vs-cck.md](../architecture/data-studio-vs-cck.md). Full RBAC/seeder audit: [rbac-and-lifecycle-seeders.md](./rbac-and-lifecycle-seeders.md).

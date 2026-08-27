# First-party module contract (frozen)

**Status:** P1 — golden sample = **JA-Mail** (`slug: mail`)  
**Audience:** agents and humans adding optional modules to `ja-core_engine` (or packaging them from another repo later).

This document freezes the **product contract**. Package boot (nwidart) stays always-on for first-party modules; **capability** is gated by the Module Registry (`sys_extensions`).

## Dual enablement (intentional)

| Layer | Source | Meaning |
|-------|--------|---------|
| Package boot | `modules_statuses.json` + `module.json` providers | Code / providers / migrations discoverable |
| Product active | `sys_extensions.status = active` | Features, API, FE nav/routes usable |

Do **not** confuse “Mail is listed in nwidart” with “Mail is activated in the registry”.

Kernel (`Modules/Core`, slug `core`) is **always** product-active and cannot be deactivated/uninstalled.

## Required files

| File | Role |
|------|------|
| `backend/Modules/<Name>/manifest.json` | Registry discovery (preferred) |
| `backend/Modules/<Name>/module.json` | nwidart providers / alias |
| `frontend/src/modules/<Name>/module.ts` | `AppModule` (`id` === manifest `slug`) |
| Optional: `docs/extensions/<slug>.md` | Product notes |

JSON Schema: [`module-manifest.schema.json`](./module-manifest.schema.json)

## Manifest fields

| Field | Required | Notes |
|-------|----------|--------|
| `name` | yes | Display name |
| `slug` | yes | Stable id (`mail`); equals FE `AppModule.id` |
| `version` | yes | Semver string |
| `type` | yes | `module` (first-party) or `plugin` |
| `is_core` | yes | `false` for optionals; only kernel may be `true` |
| `author` | yes | |
| `description` | yes | Shown in registry UI |
| `license` | recommended | Stored on `sys_extensions.license` |
| `license_tier` | recommended | `free` \| `pro` \| `pro_plus` → badge |
| `settings_route` | recommended | Console route **name** (or path string) for Configure |
| `features[]` | optional | `{ slug, name, description?, category? }` |
| `dependencies` | optional | `{ "<slug>": ">=1.0.0" }` → `sys_extensions.requirements` |

Discovery **must preserve** existing `requirements` and `settings` keys it does not own when re-syncing from disk.

## Backend gating checklist

1. API routes under `auth:sanctum` + permission + **`extension.active:<slug>`** (Mail may still use legacy alias `mail.extension`).
2. Migrations live under `database/migrations/` (run on activate **and** via `loadMigrationsFrom` — keep idempotent).
3. Deactivate = status flip only (no provider unload, no rollback).
4. Uninstall of first-party shipped modules is discouraged; kernel uninstall is refused.

Packaging from other repos: [external-module-packaging.md](./external-module-packaging.md) · scaffold: `scripts/scaffold-optional-module.sh`.

## Frontend gating checklist

1. Routes: `meta.extension: '<slug>'` (+ permission).
2. Nav: `extension: '<slug>'` on items.
3. Bootstrap: register optional `AppModule` **only when** `active_extensions` contains the slug (see `deferredConsoleModules` / console bootstrap).
4. Prefer `settings_route` / `license_tier` from the extension API over hardcoded maps.

## Lifecycle

```
discover (disk → sys_extensions)
  → activate (migrate path + status=active + hook)
  → deactivate (status=inactive + hook)
  → uninstall (uploaded plugins only; first-party Modules/* and kernel blocked)
```

## Golden sample

- Manifest: `backend/Modules/Mail/manifest.json`
- Product doc: [`ja-mail.md`](./ja-mail.md)
- FE module: `frontend/src/modules/Mail/module.ts`

Next modules (CMS packs, verticals) copy this contract; do not embed product features into `Modules/Core`.

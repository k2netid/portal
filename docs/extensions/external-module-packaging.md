# External / first-party module packaging (P2)

**Status:** P2 — how optional modules (CMS packs from `ja-cms`, verticals, etc.) land on **ja-core_engine** without forking the kernel forever.

Prerequisites: [module-contract.md](./module-contract.md) (P1) · golden sample **JA-Mail**.

## Goal

| Keep in kernel repo | Live outside / land as packs |
|---------------------|------------------------------|
| `Modules/Core` | `publishing`, `library`, `media`, `layout`, `forms`, … |
| `Modules/Mail` (reference) | Intelligence: `analytics`, `newsletter`, `search`, `cms-ai` |
| Module Registry host | Future verticals |

Kernel stays thin. Product shape = activate packs in Module Registry **or** compose via DMS/CCK.

## Three install modes (pick one per pack)

### A. Path composer (recommended for local CMS extract)

Develop pack in a sibling repo (or `packages/` under a product fork), then link:

```json
// backend/composer.json (host app / product fork — not required on bare kernel)
{
  "repositories": [
    {
      "type": "path",
      "url": "../ja-module-forms",
      "options": { "symlink": true }
    }
  ],
  "require": {
    "jejakawan/module-forms": "*"
  }
}
```

Pack layout (Mail-shaped):

```
ja-module-forms/
  composer.json          # type: laravel-module or custom installer → Modules/Forms
  module.json
  manifest.json          # contract fields
  app/ ...
  routes/ ...
  database/migrations/
```

**Convention for P2/P3:** packages install (or symlink) into `backend/Modules/<Studly>/` so nwidart + discovery keep working like Mail. Autoload:

```json
"Modules\\Forms\\": "Modules/Forms/app/"
```

(host `composer.json` or pack `autoload` + composer merge plugin — prefer explicit host PSR-4 for clarity, matching Mail today.)

### B. Git / Composer VCS

Same as A, but `"type": "vcs"` / private Packagist. Use when packs are versioned releases (`jejakawan/module-publishing:^1.0`).

### C. In-tree copy (bootstrap / monorepo product)

Product fork of kernel vendors packs under `backend/Modules/` + `frontend/src/modules/` (what Mail already is). Use `scripts/scaffold-optional-module.sh` for empty shells; migrate code from `ja-cms` domains.

ZIP/Git **plugins** (`extensions/`) remain for 3rd-party — not for first-party CMS packs.

## Host wiring checklist (every pack)

### Backend

1. Folder `backend/Modules/<Studly>/` with `module.json` + `manifest.json` (`is_core: false`).
2. PSR-4 in host `composer.json` (+ `composer dump-autoload`).
3. `"<Studly>": true` in `modules_statuses.json`.
4. nwidart scan already covers `Modules/*` — no new scan path unless custom.
5. Routes: `auth:sanctum` + `extension.active:<slug>` + permission.
6. Migrations under `database/migrations/`; provider `loadMigrationsFrom`.
7. Permissions seeded on pack activate (`extension_activated`) or via pack seeder (do not dump CMS perms into Core forever).

### Frontend

1. `frontend/src/modules/<Studly>/module.ts` — `id` / `extensionSlug` === manifest `slug`.
2. Routes: `meta.extension: '<slug>'`; nav: `extension: '<slug>'`.
3. Register in `frontend/src/engine/bootstrap/deferredConsoleModules.ts` `OPTIONAL_FIRST_PARTY` list.
4. Locales: prefer pack-owned merge (avoid permanent Core locale dump).

### Registry

1. Open Module Registry → discover → Activate (runs migrations).
2. First activate of a FE optional module may reload SPA (router snapshot).

## CMS extract order (from ja-cms inventory)

Do **not** ship mega-`Modules/Content`. Extract Mail-shaped packs:

| Wave | Pack slug(s) | Source in ja-cms | Status |
|------|----------------|------------------|--------|
| P3-1 | `publishing` + `library` | `Content/Publishing`, `Content/Library` | **In-tree on kernel** (soft stubs for Layout/Newsletter/AI) |
| P3-2 | `media` | `Content/Media` | **In-tree on kernel** (File Manager stays Core Infra) |
| P3-3 | `layout` | `Content/Layout` (+ themes) | pending |
| P3-4 | `forms` | `Content/Forms` | pending |
| P3-5+ | `analytics`, `newsletter`, `search`, `cms-ai` | `Intelligence/*` | pending |

`dependencies` in manifest e.g. publishing → `"library": ">=1.0.0"`.

## Scaffold

```bash
bash scripts/scaffold-optional-module.sh forms "Forms" "Dynamic forms & submissions"
```

Creates BE+FE skeleton that already passes the module contract (manifest, middleware slug, FE `extensionSlug`). You still wire Composer PSR-4, `modules_statuses.json`, and `OPTIONAL_FIRST_PARTY`.

Downstream **product** apps (whole branded fork): still use `scripts/bootstrap-downstream-app.sh` — different intent.

## Anti-patterns

- Forking kernel long-term just to hold CMS code (prefer packs + product compose).
- Putting Publishing/Media back into `Modules/Core`.
- One App Store toggle for entire historical `Modules/Content`.
- Using `extensions/` ZIP path for first-party Jejakawan packs (sandbox + uninstall semantics differ).

## Related

- [module-contract.md](./module-contract.md)
- [ja-mail.md](./ja-mail.md)
- [bootstrap-downstream-app.md](../product/bootstrap-downstream-app.md)
- Canvas: module-registry architecture audit (CMS inventory table)

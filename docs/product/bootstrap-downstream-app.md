# Bootstrap downstream app from kernel

Quick scaffold for a product built on **ja-core_engine**.

## Script

```bash
bash scripts/bootstrap-downstream-app.sh my-product-id "My Product Name"
```

Creates:

- `backend/Modules/{Product}/` — module.json, ServiceProvider, sample API route
- `frontend/src/modules/{Product}/` — placeholder README

## After scaffold

1. Enable in `backend/modules_statuses.json`
2. Add module scan path in `backend/config/modules.php` (if needed)
3. Register Vue routes / optional FE bootstrap
4. Add console menu entries (Menu Editor or `ConsoleMenu` seeder)
5. Configure `.env` (`APP_NAME`, `DB_DATABASE=core_engine_db`, `APP_URL`)
6. Optional: JA-CP license for Pro/Enterprise tiers
7. If shipping **Content/Media**: implement `MediaLibrarySyncInterface` on `MediaService` and `MediaFileRecordInterface` on the media `File` model (see `backend/Modules/Core/app/Infra/Contracts/`). File Manager syncs automatically when those classes are bound.

## Optional first-party packs (Mail contract)

For CMS domains extracted from `ja-cms` (forms, media, publishing, …) use:

```bash
bash scripts/scaffold-optional-module.sh forms "Forms" "Dynamic forms and submissions"
```

Then follow [external-module-packaging.md](../extensions/external-module-packaging.md) (Composer path/VCS/in-tree + Module Registry activate).

## Where CMS / member / themes live

Those belong in **downstream repos / optional packs** (source inventory: `ja-cms` `Modules/Content` + `Intelligence`). Do not re-add Content tier into kernel `Modules/Core`.

See also: [downstream-apps-and-licensing.md](downstream-apps-and-licensing.md).

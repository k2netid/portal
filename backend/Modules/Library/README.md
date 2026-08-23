# Library (`library`)

Optional first-party taxonomy pack extracted from `ja-cms` Content/Library.

Contract: `docs/extensions/module-contract.md` · Packaging: `docs/extensions/external-module-packaging.md`

## Activate

1. `modules_statuses.json` → `"Library": true` (already for in-tree)
2. Module Registry → Activate **library** (migrations + shared CMS permissions via Publishing hook)
3. FE registers when `active_extensions` includes `library` (reload after first activate)

## Soft deps

- Search observers emit events; Search pack optional later.

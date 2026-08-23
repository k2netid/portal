# Media (`media`)

Optional first-party media library extracted from `ja-cms` Content/Media.

Contract: `docs/extensions/module-contract.md`

**Not included:** Core File Manager stays in `Modules/Core/Infra` (disk browser). This pack is the DB-backed library + picker (`/manage/media`).

## Soft deps

| Pack | Behavior |
|------|----------|
| Library | Tags on files optional (`class_exists`) |
| Publishing | Usage morph + MediaPicker consumers |

## Activate

1. Module Registry → Activate **media**
2. Permissions seed on `extension_activated`
3. Reload console after first activate (FE optional pack)

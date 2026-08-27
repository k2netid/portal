# Changelog — Modules/Core

## [Unreleased]

### Fixed
- Plugin uninstall returns the deactivate error instead of deleting files when dependents are still active.
- Kernel settings list includes `general` (Identity). Product groups remain refused.
- Data Studio one-shot rename of reserved slugs (`grandfatherReservedSlugs`).

### Added
- Module contract freeze docs + `ModuleManifestValidator` for first-party manifests.
- Discovery sync: `description`, manifest `license` / `license_tier` / `settings_route`; preserve `requirements` when dependencies omitted.
- `EnsureExtensionActive` middleware alias `extension.active:{slug}` for optional packs.
- Docs: `external-module-packaging.md` + `scripts/scaffold-optional-module.sh`.

### Fixed
- Extension discovery: slug `core` is platform kernel (`is_core`, always `active`); heals stale Inactive App Store rows.
- Deactivate/uninstall refuse kernel slugs even when `is_core` DB flag is wrong.
- Dropped stale CMS `is_core` whitelist (`analytics`, `media`, `publishing`, …).

### Changed
- `module.json` marks `is_core: true` for the consolidated kernel package.
- Module Registry UI shelves: Platform / Modules / Plugins (frontend System locales + nav).
- Console bootstrap registers optional first-party FE modules only when product-active.

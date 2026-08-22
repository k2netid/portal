# Changelog — Modules/Core

## [Unreleased]

### Fixed
- Extension discovery: slug `core` is platform kernel (`is_core`, always `active`); heals stale Inactive App Store rows.
- Deactivate/uninstall refuse kernel slugs even when `is_core` DB flag is wrong.
- Dropped stale CMS `is_core` whitelist (`analytics`, `media`, `publishing`, …).

### Changed
- `module.json` marks `is_core: true` for the consolidated kernel package.
- Module Registry UI shelves: Platform / Modules / Plugins (frontend System locales + nav).

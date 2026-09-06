# Changelog — Modules/Core

## [Unreleased]

### Added
- **3-Tier Identity & Smart Brand Sync**: Backend `SettingController` supports 3-tier identity model (`app_name`, `brand_logo`, `brand_favicon`, `branding_display`), smart synchronization to site identity (`brand_sync_site_identity`), and graceful fallback to Jejakawan baseline ([ADR-014](../../docs/adr/ADR-014-three-tier-identity-whitelabel-brand-and-smart-sync.md)).
- **Extension Gating & Manifests**: Registered first-party extension manifests for Visual Builder (`builder.site`) and Data Model Studio (`studio.datamodel`) with license tier gating ([ADR-011](../../docs/adr/ADR-011-visual-builder-modular-extension-and-license-gating.md), [ADR-012](../../docs/adr/ADR-012-data-model-studio-modular-extension-and-license-gating.md)).
- **Install profiles** (`core` | `cms` | `cms_site`): `InstallProfileApplicator` + `ja:apply-install-profile` + seed hook + `POST …/extensions/apply-install-profile`. Default `cms_site` when Site module ships so migrate:fresh yields public `/` without tinker.
- Module contract freeze docs + `ModuleManifestValidator` for first-party manifests.
- Discovery sync: `description`, manifest `license` / `license_tier` / `settings_route`; preserve `requirements` when dependencies omitted.
- `EnsureExtensionActive` middleware alias `extension.active:{slug}` for optional packs.
- Docs: `external-module-packaging.md` + `scripts/scaffold-optional-module.sh`.

### Fixed
- **Strict Console Favicon Resolution (`SpaHtmlFavicon.php`)**: `SpaHtmlFavicon::resolveHref('console')` and `landing` strictly check `brand_favicon` or return `/favicon.ico`—never falling back to `site_favicon`, eliminating console favicon race and cross-shell pollution ([ADR-015](../../docs/adr/ADR-015-favicon-isolation-prepaint-guards-and-zero-race-lifecycle.md)).
- **Site Identity Layout Guard (`SettingController.php`)**: Guarded `syncSiteIdentityToActiveTheme()` with `Extension::isProductActive('layout')` check to avoid unnecessary theme operations when the layout pack is inactive ([ADR-016](../../docs/adr/ADR-016-module-aware-site-identity-and-dependency-orchestration.md)).
- Extension discovery: slug `core` is platform kernel (`is_core`, always `active`); heals stale Inactive App Store rows.
- Deactivate/uninstall refuse kernel slugs even when `is_core` DB flag is wrong.
- Decoupled `appName` fallback in `SecurityNotificationService` from vendor name to generic kernel default.
- Dropped stale CMS `is_core` whitelist (`analytics`, `media`, `publishing`, …).
- Console Navigation: Synchronized database menu hierarchy order during authentication completion to ensure consistent menu rendering on initial dashboard mount ([ADR-013](../../docs/adr/ADR-013-console-sidebar-database-menu-preloading-and-reactive-hydration.md)).

### Changed
- `module.json` marks `is_core: true` for the consolidated kernel package.
- Module Registry UI shelves: Platform / Modules / Plugins (frontend System locales + nav).
- Console bootstrap registers optional first-party FE modules only when product-active.


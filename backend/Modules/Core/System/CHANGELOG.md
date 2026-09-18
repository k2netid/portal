# Changelog — Core / System (BE)

IAM, settings, identity, extensions registry, install profiles, console shell APIs.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

Index: [`../CHANGELOG.md`](../CHANGELOG.md)

## [Unreleased]

### Added
- **User Creation Verification Privilege Control**: Optional `is_verified` on `UserController::store` ([ADR-019](../../../../docs/adr/ADR-019-user-email-verification-and-privilege-lifecycle.md)).
- **3-Tier Identity & Smart Brand Sync**: `SettingController` 3-tier identity + `brand_sync_site_identity` ([ADR-014](../../../../docs/adr/ADR-014-three-tier-identity-whitelabel-brand-and-smart-sync.md)).
- **Extension Gating & Manifests**: Registry manifests for first-party extensions incl. builder/studio license tiers ([ADR-011](../../../../docs/adr/ADR-011-visual-builder-modular-extension-and-license-gating.md), [ADR-012](../../../../docs/adr/ADR-012-data-model-studio-modular-extension-and-license-gating.md)).
- **Install profiles** (`core` | `cms` | `cms_site`): `InstallProfileApplicator` + `ja:apply-install-profile` + seed/API hooks.
- Module contract freeze + `ModuleManifestValidator`.
- Discovery sync: `description`, `license` / `license_tier` / `settings_route`; preserve `requirements`.
- `EnsureExtensionActive` middleware alias `extension.active:{slug}`.
- **ADR-023 Theme Registry Serving**: `ExtensionController` attaches `is_served` indicator and `ExtensionFamilyCatalog` provides `themeSlugForPack()` and `isPremiumThemePackSlug()`.
- **ADR-023 Phase 2 JA-CP Themes Payload Integration**: `LicenseService` parses and dynamically applies `themes.*` quota from JA-CP heartbeat/activation responses, auto-triggers `ThemeDowngradeRemediator`, and displays theme quotas in `license:check` CLI.
- Docs: `external-module-packaging.md` + `scripts/scaffold-optional-module.sh`.

### Fixed
- **User Creation Verification Default**: New users `is_verified = false` / `email_verified_at = null` ([ADR-019](../../../../docs/adr/ADR-019-user-email-verification-and-privilege-lifecycle.md)).
- **Strict Console Favicon Resolution (`SpaHtmlFavicon.php`)**: Console/landing never fall back to `site_favicon` ([ADR-015](../../../../docs/adr/ADR-015-favicon-isolation-prepaint-guards-and-zero-race-lifecycle.md)).
- **Site Identity Layout Guard**: `syncSiteIdentityToActiveTheme()` gated on Layout pack ([ADR-016](../../../../docs/adr/ADR-016-module-aware-site-identity-and-dependency-orchestration.md)).
- Extension discovery: slug `core` always active; heal stale Inactive rows; refuse kernel deactivate/uninstall.
- Dropped stale CMS `is_core` whitelist (`analytics`, `media`, `publishing`, …).
- Console Navigation: menu hierarchy order synced at auth completion ([ADR-013](../../../../docs/adr/ADR-013-console-sidebar-database-menu-preloading-and-reactive-hydration.md)).
- Scramble OpenAPI: nullsafe `Rule::unique` on User/Role profile update (VR002).
- Product SemVer: `config('app.version')` / OpenAPI / `site_version` follow root `package.json` (or `APP_VERSION`).
- Manifest first-party: `version` must be SemVer (`ModuleManifestValidator` + schema pattern).
- Pack SemVer: Core `manifest.json` → `1.0.1` (OpenAPI version wiring + SemVer validator); CI `modules:versions:check`.

### Changed
- **Unified Role Ranks**: `User::getRoleRankMap()` hierarchy ([ADR-020](../../../../docs/adr/ADR-020-rbac-hierarchy-route-hardening-and-ui-primitives.md)).
- `module.json` marks `is_core: true` for consolidated kernel package.
- Scramble `api_path`: exclude `api/v1/dynamic` (documented by `dynamic:openapi`).

# Changelog — Core / System (FE)

Console System UI: IAM, settings/identity, appearance, module registry, auth shell.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

Index: [`../CHANGELOG.md`](../CHANGELOG.md)

## [Unreleased]

### Added
- **User Creation Email Verification Controls** in `UserModal.vue` ([ADR-019](../../../../../docs/adr/ADR-019-user-email-verification-and-privilege-lifecycle.md)).
- **3-Tier Identity & Smart Brand Sync** in `GeneralTab.vue` / `PlatformIdentityTab.vue` ([ADR-014](../../../../../docs/adr/ADR-014-three-tier-identity-whitelabel-brand-and-smart-sync.md)).
- **Console Appearance Dual-Mode Workspace** ([ADR-017](../../../../../docs/adr/ADR-017-console-appearance-dual-mode-workspace-and-license-gating.md)).
- **High-Contrast `Slider.vue`**.
- **Site Identity Module Awareness** + Module Registry Site Configure bridge ([ADR-016](../../../../../docs/adr/ADR-016-module-aware-site-identity-and-dependency-orchestration.md)).

### Changed
- **Unified Role Hierarchy & Ranks** in auth stores ([ADR-020](../../../../../docs/adr/ADR-020-rbac-hierarchy-route-hardening-and-ui-primitives.md)).
- **Console Branding & Display Select** / license gating on appearance portability.
- **Sidebar & Header Polish**.

### Fixed
- **Favicon race / DOM guards** for console shell ([ADR-015](../../../../../docs/adr/ADR-015-favicon-isolation-prepaint-guards-and-zero-race-lifecycle.md)).
- Graceful brand fallback; cross-tab guard when Site inactive.
- App Store Configure route names; identity settings refresh.
- Navigation preload on login ([ADR-013](../../../../../docs/adr/ADR-013-console-sidebar-database-menu-preloading-and-reactive-hydration.md)).
- i18n default language `id` on first load.

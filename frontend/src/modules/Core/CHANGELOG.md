# Changelog — Core (frontend)

## [Unreleased]

### Added
- **3-Tier Identity & Smart Brand Sync**: `GeneralTab.vue` and `PlatformIdentityTab.vue` implement the 3-tier identity hierarchy with the `brand_sync_site_identity` toggle and instant reactivity ([ADR-014](../../../../docs/adr/ADR-014-three-tier-identity-whitelabel-brand-and-smart-sync.md)).
- **Console Appearance Dual-Mode Workspace**: `AppearanceSettings.vue` split into Easy Mode (theme mode and curated color presets) and Advanced Mode (deep CSS design tokens and code editor), guarded by `ConfirmDialog.vue` ([ADR-017](../../../../docs/adr/ADR-017-console-appearance-dual-mode-workspace-and-license-gating.md)).
- **High-Contrast `Slider.vue`**: Added custom UI slider component with active filled progress track, glow effect, and real-time numeric readouts.
- **Site Identity Module Awareness**: `PlatformIdentityTab.vue` and `Index.vue` monitor `site`, `layout`, and `publishing` module lifecycles, rendering status badges, amber inactive notices with direct Module Registry CTA, and dependency warning alerts ([ADR-016](../../../../docs/adr/ADR-016-module-aware-site-identity-and-dependency-orchestration.md)).
- **Module Registry Site Extension Bridge**: Module Registry "Configure" button on the `Site` extension routes directly to `{ name: 'settings', query: { tab: 'identity' } }`.

### Changed
- **Console Branding & Display Select**: Refined Console Appearance by eliminating redundant logo upload fields, centralizing brand assets into General settings, and providing reactive `branding_display` mode selector (`both`, `logo_only`, `name_only`, `collapsed_icon_only`) in `TheSidebar.vue`.
- **License Gating on Appearance**: Portability (import/export JSON) and logo customizations are gated behind Enterprise/Pro license tiers.
- **Sidebar & Header Polish**: Streamlined appearance header, eliminated dropdown layout shift, and refined sidebar icons.

### Fixed
- **Favicon Race Condition Elimination & DOM Guards**: Separated prepaint cache key `ja_console_favicon_href`, added DOM equality check in `applyFavicon` (`useBrandIdentity.ts`), and designated `ConsoleApp.vue` as single source of truth for console favicon ([ADR-015](../../../../docs/adr/ADR-015-favicon-isolation-prepaint-guards-and-zero-race-lifecycle.md)).
- **Graceful Brand Fallback**: Automatic fallback to canonical Jejakawan `/logo.png` and `/favicon.ico` when custom brand assets are removed or empty.
- **Cross-Tab Dependency Guard**: Disabled `brand_sync_site_identity` setting in `GeneralTab.vue` when the `site` module is inactive.
- App Store Configure uses real Vue route names (`publishing-settings`, `analytics`).
- Identity settings refresh `general` after save.
- Navigation Store & Shell: Preload database console menus upon successful authentication in `Login.vue` and add resilient fallback hydration in `TheSidebar.vue` to ensure ordered sidebar menus without requiring a page refresh (`F5`) ([ADR-013](../../../../docs/adr/ADR-013-console-sidebar-database-menu-preloading-and-reactive-hydration.md)).
- i18n & Locale Resolution: Set site default language on initial load strictly to `id` (Bahasa Indonesia) by preventing browser language sniffing (`en-US`) from overriding the default when no user preference is stored.



# Changelog — Site

## [Unreleased]

### Added
- **Module Lifecycle Awareness**: Settings $\rightarrow$ Site Identity tab reacts dynamically to the `site` extension status, disabling configuration when the module is deactivated and alerting administrators of missing `layout` / `publishing` dependencies ([ADR-016](../../docs/adr/ADR-016-module-aware-site-identity-and-dependency-orchestration.md)).
- **Module Registry Navigation Shortcut**: "Configure" on the Site module in Module Registry / App Store links directly to `{ name: 'settings', query: { tab: 'identity' } }`.
- Public host pack (rewrite + SPA). Theme pages follow the active Layout theme.

### Changed
- Boot gate: product-active `site` serves public SPA at apex `/`; legacy `/site/*` redirects; console reserved paths unchanged.
- **Strict Public Favicon Resolution**: Server-side `SpaHtmlFavicon::resolveHref('site')` resolves `site_favicon` $\to$ `brand_favicon` $\to$ `/favicon.ico` while client-side `FrontendLayout.vue` isolates `ja_site_favicon_href` to avoid console collisions ([ADR-015](../../docs/adr/ADR-015-favicon-isolation-prepaint-guards-and-zero-race-lifecycle.md)).


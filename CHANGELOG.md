# Changelog

Semua perubahan penting pada **Jejakawan Core Engine (`ja-core_engine`)**.

> Fork dari **`ja-cms`** → master kernel untuk aplikasi downstream. Branch `develop` (line CMS) dihapus Aug 2026; Content/member/themes = downstream, bukan scope `main`.

Format: [Keep a Changelog](https://keepachangelog.com/id/1.0.0/) · [Semantic Versioning](https://semver.org/spec/v2.0.0.html)

---

## [Unreleased]

### Added
- **Theme Sarangenge Side Nav Presets & Viewport Scroll Snap**: Added floating side dot navigation (`SarangengeSideNav.vue`) with 4 presets (`glass`, `minimal`, `glow`, `bars`), GSAP spring motion, i18n tooltips, `<Teleport to="body">` viewport centering, and `yMandatory` scroll snap with Theme Customizer controls ([ADR-018](docs/adr/ADR-018-theme-sarangenge-side-nav-presets-and-viewport-scroll-snap.md)).
- **RBAC UI Primitives**: Added Vue directives `v-can` and `v-role` (with `.disabled` modifier) and `<Can>` component supporting `:permission`, `:role`, `:any`, `:minRank`, and `#fallback` slot. Registered globally in `main-shared.ts` ([ADR-020](docs/adr/ADR-020-rbac-hierarchy-route-hardening-and-ui-primitives.md)).
- **User Creation Email Verification Controls**: Added explicit `is_verified` toggle in `UserModal.vue` allowing authorized higher-rank roles to mark accounts as verified on creation ([ADR-019](docs/adr/ADR-019-user-email-verification-and-privilege-lifecycle.md)).
- **3-Tier Identity Architecture**: Formal separation of Core Engine Identity (fallback canonical Jejakawan), White-Label Brand Identity (`app_name`, `brand_logo`, `brand_favicon`, `branding_display` for Enterprise console), and Site Identity (`site_name`, `site_logo`, `site_favicon` for public theme portal). Includes smart brand sync option (`brand_sync_site_identity`) to copy brand identity to site identity and active theme settings in one click ([ADR-014](docs/adr/ADR-014-three-tier-identity-whitelabel-brand-and-smart-sync.md)).
- **Console Appearance Dual-Mode Workspace**: Split `/dash/system/appearance` into Easy Mode (streamlined theme mode and quick color presets) and Advanced Mode (deep CSS design tokens: corner radii, shadow elevation, typography scales, density spacing, saturation, and custom CSS code editor), protected by confirmation dialogs ([ADR-017](docs/adr/ADR-017-console-appearance-dual-mode-workspace-and-license-gating.md)).
- **Slider Component**: High-contrast, accessible `Slider.vue` component with filled dynamic progress track, hover/focus glow, and real-time numeric readouts.
- **Module Lifecycle Awareness in Site Identity**: Site Identity tab detects active status of `site`, `layout`, and `publishing` modules, showing `(Nonaktif)` status badge, amber notice banner with one-click CTA to Module Registry, red missing-dependencies warning, and disabling inputs when site module is inactive ([ADR-016](docs/adr/ADR-016-module-aware-site-identity-and-dependency-orchestration.md)).
- **Module Registry Site Extension Bridge**: Configured "Configure" button on the `Site` extension card to route directly to `{ name: 'settings', query: { tab: 'identity' } }`.
- **Modular Extensions License Gating**: Modularized Visual Builder (`builder.site`) and Data Model Studio (`studio.datamodel`) into pluggable first-party extensions gated by license tiers ([ADR-011](docs/adr/ADR-011-visual-builder-modular-extension-and-license-gating.md), [ADR-012](docs/adr/ADR-012-data-model-studio-modular-extension-and-license-gating.md)).

### Changed
- **Unified Role Hierarchy & Ranks**: Standardized role ranks across backend `User::getRoleRankMap()` and frontend `auth.ts` (`super: 100 > system-admin: 95 > admin: 90 > security-officer: 85 > operator: 80 > editor: 60 > author: 40 > staff: 30 > member: 10`) ([ADR-020](docs/adr/ADR-020-rbac-hierarchy-route-hardening-and-ui-primitives.md)).
- **Console Branding & Display Select**: Refined Console Appearance by eliminating redundant logo upload fields, centralizing brand assets into General settings, and providing reactive `branding_display` mode selector (`both`, `logo_only`, `name_only`, `collapsed_icon_only`) in `TheSidebar.vue`.

- **Commercial Licensing Gating**: Gated theme portability (import/export JSON) and custom console logos behind Enterprise/Pro license tiers with clear disabled states and tier badges.
- **Sidebar & Shell Polish**: Refined console sidebar icons, single-open accordion mode, streamlined appearance header, and eliminated dropdown layout shift.
- **Live Preview Modal & Viewport Scaling**: Visual Builder and Theme Customizer live preview modals feature proportional viewport scaling (`scale(0.8)` for tablet/mobile viewports) and adaptive mobile toolbar layout ([ADR-010](docs/adr/ADR-010-builder-viewport-preview-scaling-and-adaptive-responsive-toolbar.md)).
- Layung public theme: contact page uses the published Reach form (same pattern as Janari) and hides `/contact` menu items when the contact page is disabled.
- Layung public theme: menu list aligned to K2NET’s three business lines; mock/instructional copy removed from public pages.
- Docs: honesty pass marked landed on `integrate/cms`; merge-gate + W5 naming residual clarified; root `.env.example` notes Vite/Sanctum **5273**.
- W5 naming: Media/Library defaults `publishing`; theme docs paths `Layout/*`; console title fallbacks drop `JA Jejakawan`; i18n checker maps `Member` pack namespace.
- PHPStan baseline regenerated; Member model `@property` docs for Larastan.
- **Site boot gate:** when pack `site` is product-active, apex `/` serves the public theme SPA; console stays on `/dash` + `/auth/console-*`; legacy `/site/*` 301 to apex.
- Default public theme prefers **Janari** (CMS reference / builder contract); scaffolded themes get `parent_theme: janari` + `janari_canvas`.
- **Builder ↔ theme ↔ menus:** Site Editor saves theme settings to the real theme API; Menu Builder location syncs `menu_location_*` on the active theme; customizer publish keeps those keys; public resolve by menu UUID; deep-links between Site Editor, Theme Customizer (`?panel=menus`), and Menu Builder.
- Site Editor embeds merged Theme Customizer settings schema; toolbar opens live site preview iframe (`/` or current page slug).
- Site Editor Theme tab previews live Janari Vue pages instead of creating empty CMS drafts.
- Theme tab **Edit with Builder** binds/creates Publishing content by route slug and saves `meta.builder_blocks` overrides.

### Fixed
- **Backend API Defense-in-Depth**: Gated previously open backend API routes in Core (users, roles, settings, tasks, logs), Media (folder endpoints), Layout (menus, widgets, redirects), and Library (destructive category actions, custom fields) with explicit Spatie permission middleware; corrected Publishing SEO permission typo (`permission:manage seo`) ([ADR-020](docs/adr/ADR-020-rbac-hierarchy-route-hardening-and-ui-primitives.md)).
- **Seeder Permission Wipeout Elimination**: Replaced destructive `syncPermissions()` with idempotent `givePermissionTo(...)` in `CmsRolesSeeder.php`, guaranteeing that permissions attached by first-party extension seeders are never deleted on re-seed ([ADR-020](docs/adr/ADR-020-rbac-hierarchy-route-hardening-and-ui-primitives.md)).
- **User Creation Verification Default State**: Newly created users default to unverified (`is_verified = false`, `email_verified_at = null`) preventing false verified indicators in the dashboard table and aligning with email activation workflows ([ADR-019](docs/adr/ADR-019-user-email-verification-and-privilege-lifecycle.md)).
- **Global 403 Forbidden Handling**: Client HTTP interceptor dispatches `app:forbidden` event on HTTP 403 response for graceful UI notifications ([ADR-020](docs/adr/ADR-020-rbac-hierarchy-route-hardening-and-ui-primitives.md)).
- **Favicon Race Condition Elimination**: Completely resolved browser tab favicon race conditions and flickering across Console and Public Site shells by separating localStorage prepaint cache keys (`ja_console_favicon_href` vs `ja_site_favicon_href`), hardening inline Blade prepaint script, adding URL pathname equality guards in client `applyFavicon`, designating `ConsoleApp.vue` and `FrontendLayout.vue` as single sources of truth, and strictly isolating server-side `SpaHtmlFavicon::resolveHref('console')` to never leak `site_favicon` into console tabs ([ADR-015](docs/adr/ADR-015-favicon-isolation-prepaint-guards-and-zero-race-lifecycle.md)).
- **Brand Asset Fallback Resilience**: Implemented automatic fallback to canonical Jejakawan `/logo.png` and `/favicon.ico` when custom brand logos or favicons are deleted or unset, preventing broken image placeholders.
- **Site Identity Backend Guard**: Guarded `SettingController::updatePlatformIdentity` to only trigger `syncSiteIdentityToActiveTheme()` when `Extension::isProductActive('layout')` is true.
- **Cross-Tab Dependency Guard**: Disabled `brand_sync_site_identity` setting in General tab with an explanatory tooltip whenever the `site` module is inactive.
- **Visual Builder Preview Sandboxing**: Prevented accidental editor dismissal when clicking internal links or menus inside the live preview canvas using Capture-Phase Event Shield and Scoped Router/Route Mock (`provide(routerKey, canvasRouter)` & `provide(routeLocationKey, canvasRoute)`).
- **Preview Modal Controls Separation**: Separated "Open in New Tab" button from the modal close button with distinct button styling in Visual Builder and Customizer.
- **Canvas Header Z-Index Layering**: Fixed dropdown menu clipping and stacking order inside the Visual Builder header bar.
- **Advanced Mode Theme Presets**: Restored proper support for color presets and preset tokens in Advanced Mode appearance settings.
- Visual builder: empty pages no longer auto-fill demo sections or mark unsaved demo as saved.
- Visual builder keyboard (Delete, Esc, duplicate/copy/paste) matches Help; site editor waits for a page pick.
- Visual builder canvas leaves use public BlockRenderer; public preview is a sandboxed iframe; `meta.builder_schema_version` on save.
- Publishing rejects malformed `meta.builder_blocks` on create/update.
- Builder save snapshots a content revision; History panel can restore saved trees. Canvas acquires a 60-minute edit lock. AI generate-blocks is gated by Settings → AI.
- Builder lock shows a banner and blocks save; AI asks append vs replace; restore asks for confirmation.
- Publishing overlay no longer double-PUTs; new content body is derived from builder blocks.
- Public builder HTML/embed and Janari classic body go through SafeHtml.
- Layout `dynamicSources` dropped query-string debug log.
- Honesty pass: uninstall refuses when deactivate is blocked; kernel Identity owns `general`; Publishing no longer writes site identity; Member APIs gated; public theme pages follow the active theme; Sanctum 5273; layout public menus/themes gated; Mail/cron skip when pack off.
- Console navigation: Preload database console menus upon successful authentication in `Login.vue` and add reactive fallback in `TheSidebar.vue` to eliminate disordered menu flicker without requiring page refresh ([ADR-013](docs/adr/ADR-013-console-sidebar-database-menu-preloading-and-reactive-hydration.md)).
- Site Default Locale: Set site default language on initial load strictly to `id` (Bahasa Indonesia) by preventing client browser language sniffing from overriding the primary portal locale when no user preference is stored.



Kernel `/manage/ai/generate` stays settings-flag gated (`ai_enabled`), not `cms-ai` pack — by design for downstream apps without CMS.

### Added
- P2 refine: public SPA defers Member/Analytics; Data Studio grandfathers reserved slugs; member email verify gates bookmarks/comments; console Members directory; pack tests for Layout/Media/Library/Newsletter/Publishing content; Identity Media vs Media pack split documented.
- JA-Mail: honest AI gating vs Settings → AI; Core `AI_DISABLED` on generate; `mail-ai-governance` rule
- JA-Mail: in-app notification bridge (send failure / vacation); per-module README + CHANGELOG; agent rule `module-documentation.mdc`
- `docs/product/bootstrap-downstream-app.md` + `scripts/bootstrap-downstream-app.sh` — scaffold modul produk downstream.
- `Modules/Core/app/System/Services/Ai/AiHttpResponse.php` — typed HTTP helpers untuk provider AI.

### Changed
- PHP/Laravel claims: PHP 8.2+ (tested 8.3), Laravel 13. Pack manifests `laravel: ">=13.0"`.
- Janari SafeHtml mode `publishing` (alias `Jejakawan` kept for old builder HTML).
- Publishing SEO/Discussion tabs live under the Publishing pack; unused Core Analytics tab removed.
- **Module Registry P3-2:** port `media` from ja-cms as optional pack (picker + library; File Manager stays Core); retarget MediaLibraryBridge; console menu soft-sync.
- **Module Registry P3-1:** port `library` + `publishing` from ja-cms as optional packs (Mail contract); soft-stub Layout/Newsletter/AI until later waves.
- **Module Registry P2:** external packaging guide + `scaffold-optional-module.sh`; kernel middleware `extension.active:{slug}` (Mail alias delegates).
- **Module Registry P1:** freeze first-party module contract (`docs/extensions/module-contract.md` + JSON Schema); discovery syncs description/license/settings_route/license_tier and preserves requirements; FE registers Mail only when `active_extensions` includes `mail`.
- **Module Registry P0:** Core discovery marks slug `core` as kernel (`is_core` + always `active`); heal stale Inactive rows; uninstall/deactivate refuse kernel slugs even if `is_core` flag wrong; App Store UI → Platform / Modules / Plugins shelves + rename copy.
- JA-Mail: IMAP/SPF/DKIM documented as mail-server/DNS (out of kernel backlog)
- Identitas produk diselaraskan ke **Core Engine** (docs, UI, artisan, OAuth copy).
- PHPStan: perbaiki 138 error di luar baseline; baseline diregenerasi (~85 entri FileManager).
- E2E: hanya smoke kernel (auth, onboarding, console a11y); hapus theme/content/member specs.
- Router guards & error pages: hapus sisa `member-*` routes.
- CI: trigger hanya `main`; hapus payment-env-check & security audit commands orphan.

### Removed
- Branch **`develop`** (remote + local) — line CMS tidak lagi di repo ini.
- E2E legacy: `theme-*.spec.ts`, `member-register`, `member-a11y`, `console-content-studio`.
- Frontend scripts: `test:e2e:theme`, `build:theme:janari`, Janari theme schema merge/split.

---

## [1.0.0-beta.2] — 2026-08-19

Rilis stabilisasi kernel pasca-fork: i18n 3 bahasa, performance settings cache, dan pembersihan DNA control-plane/CRM dari tree.

### Added
- **Isolated i18n architecture** untuk modul berat (builder pattern — kini downstream CMS).
- **Paritas i18n `id` / `en` / `su`** dengan validator otomatis (`i18n-check-keys.mjs`).
- **Dialog z-index layering** — modal konfirmasi di atas fullscreen overlay.
- **Settings caching** — `Cache::rememberForever` + observer invalidation; indeks DB performa; `VACUUM ANALYZE` di maintenance.

### Changed
- **Identitas repo** disepakati sebagai fondasi modular (transisi ke `ja-core_engine`).
- **Module governance** disederhanakan ke tier Core (System, Infra, Security).
- Environment template: database schema `core_engine` (bukan `ja_cms_db`).

### Removed
- Sisa **control-plane operational** dari kernel: SME/Aksara/Exambro service keys, `Modules/Operational` scan, accounting/platform scheduled tasks, CRM/accounting frontend paths & E2E billing specs.

---

## [1.0.0-beta.1] — 2026-05-25

Baseline **Modular Monolith kernel** (upstream: ja-cms monolith).

### Added — Core tier (tetap di `main`)
- **System:** IAM (RBAC Spatie, ABAC, Passkeys, 2FA), settings, extensions marketplace, languages.
- **Infra:** Data Model Studio, backups, webhooks, Redis explorer, scheduled tasks, file manager.
- **Security:** IP management, rate limiting, audit/activity/system journals, CSP/SIEM hooks.
- **Frontend:** Vue 3 unified console SPA, engine router, Pinia, i18n.

### Added — Content/Intelligence tier *(dipindah ke downstream / ex-develop)*
- Publishing, Layout/Builder, Media, Forms, Library, AI, Analytics, Newsletter — **tidak lagi bagian `main`**.

---

## Catatan fork (Aug 2026)

| Keputusan | Detail |
|---|---|
| **`main`** | Canonical **Core Engine** kernel |
| **`develop`** | **Dihapus** — CMS line hidup di repo produk terpisah |
| **JA-CP** | Hub lisensi eksternal; bukan identitas engine |
| **Downstream** | Fork `main` + `bootstrap-downstream-app.sh` + modul produk |

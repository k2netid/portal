# Changelog

Semua perubahan penting pada **Jejakawan Core Engine (`ja-core_engine`)**.

> Fork dari **`ja-cms`** → master kernel untuk aplikasi downstream. Branch `develop` (line CMS) dihapus Aug 2026; Content/member/themes = downstream, bukan scope `main`.

Format: [Keep a Changelog](https://keepachangelog.com/id/1.0.0/) · [Semantic Versioning](https://semver.org/spec/v2.0.0.html)

---

## [Unreleased]

### Changed
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

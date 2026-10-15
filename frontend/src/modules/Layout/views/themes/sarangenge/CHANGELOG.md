# Changelog — Theme Sarangenge

All notable changes to the **Sarangenge** public theme package (school / campus).

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

Host / plugin platform changes (e.g. cinematic-nav) belong in Layout FE + ADR-018 (core).  
Docs index: `docs/themes/sarangenge/`.

## [Unreleased]

### Added

- **Mega Menu Container & Navigation Parity**: Implementation plan for multi-column mega menu dropdowns, column groups, badge indicators, and promotional media cards ([ADR-028](../../../../../../docs/adr/ADR-028-menu-builder-properties-mega-menu-and-multi-theme-parity.md)).
- **Dynamic Identity Bindings**: Added reactive composable `useSarangengeIdentity.ts` for principal portrait, name, credentials, quote, and vision statement, bound dynamically to `About.vue` and `VocationalTrackFinder.vue`.
- **7 Vocational Tracks Default Navigation**: Aligned `Header.vue` and sample data `bundle.json` with authentic vocational tracks (`tkro`, `tbsm`, `tjkt`, `dkv`, `mplb`, `akl`, `kuliner`) and Career/BKK link.
- `contact_form_slug` setting (theme.json + customizer); Contact submit uses Reach slug URL

### Changed

- Theme package version → `2.0.6`
- Faculty directory: `Tim.vue` → `Team.vue` (`/team`, aliases `/tim` `/guru` …); i18n `pages.team`; `enable_tim` → `enable_team`
- Archetype + Post: `after_hero` / `after_post_content` + `sidebar_article` PluginSlots
- Customizer: floating dock keys deferred to platform schema; removed dead `social_instagram_feed_*` keys (use `instagram-feed` extension)
- CMS pages: `after_hero` on About, Blog, Page
- Removed thin `Solusi.vue` / `Services.vue`; `/solusi` aliases → Programs, `/services` → Facilities

### Fixed

- Contact no longer hardcodes `/public/forms/contact/submit`
- Post.vue and Programs.vue TypeScript and ESLint typing refinements.

# Changelog — Theme Sarangenge

All notable changes to the **Sarangenge** public theme package (school / campus).

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

Host / plugin platform changes (e.g. cinematic-nav) belong in Layout FE + ADR-018 (core).  
Docs index: `docs/themes/sarangenge/`.

## [Unreleased]

### Added

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

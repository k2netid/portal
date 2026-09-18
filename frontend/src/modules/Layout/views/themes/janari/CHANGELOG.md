# Changelog — Theme Janari

All notable changes to the **Janari** public theme package.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

Host / customizer platform changes belong in `frontend/src/modules/Layout/CHANGELOG.md`.  
ADR theme-scoped (if any): `docs/themes/janari/`.

## [Unreleased]

### Changed
- `theme.json` version → `2.0.4`
- Team page: `Tim.vue` → `Team.vue` (`/team`, alias `/tim`)
- Removed dead `social_instagram_feed_*` keys (Instagram via `instagram-feed` extension slots)
- About page: standard `PluginSlot name="after_hero"`
- Product hub page: `Solusi.vue` → `Solutions.vue` (`/solutions`, alias `/solusi`); i18n `pages.solutions` (keep `pages.services*` for home ServicesSection)

### Added

### Fixed

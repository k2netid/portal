# Changelog — Theme Sareupna

All notable changes to the **Sareupna** public theme package.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

Host / customizer platform changes belong in `frontend/src/modules/Layout/CHANGELOG.md`.  
Docs index: `docs/themes/sareupna/`.

## [Unreleased]

### Added

- Own `pages/About.vue` (cyber chrome) — no silent Janari fallback when `enable_about=true`
- `sareupnaPublicSeo` + `useSareupnaIdentity` composables; wired into `FrontendLayout` SEO
- `contact_form_slug` in `theme.json` / customizer schema
- `menus` locations in `theme.json`
- Sample-data `pages` + `posts` for CMS demo seed

### Changed

- `disabled_page_behavior` enums normalized to `message` / `redirect` (aligned with Blog/Contact defaults)
- Theme package version → `1.1.4`
- Pricing / Search: `after_hero` PluginSlot
- Customizer schema stripped to **theme-only** (platform identity / floating dock / colors from host); dropped dead `home_side_nav_labels` / `home_side_nav_position`
- Readme: side-nav is host `cinematic-nav` (ADR-018), not local SectionNavDots
- `pages.solutions` / `pages.pricing` i18n (id/en/su); page file `Solutions.vue` (`/solutions`, alias `/solusi`)
- `after_hero` on About + Solutions; sample-data `menus` normalized to location-keyed dict (seeder-compatible)

### Fixed


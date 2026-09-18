# Changelog — Theme Layung

All notable changes to the **Layung** public theme package (ISP / MSP).

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

Host / customizer platform changes belong in `frontend/src/modules/Layout/CHANGELOG.md`.  
Theme ADRs: `docs/themes/layung/` (ADR-001, 007, 008).

## [Unreleased]

### Added

- `menus` locations declared in `theme.json` (header / footer / sidebar contract)

### Changed

- Theme package version → `1.0.5`
- Team page: `Tim.vue` → `Team.vue` (`/team`, alias `/tim`); `enable_tim` → `enable_team`
- Removed dead `social_instagram_feed_*` keys (Instagram via `instagram-feed` extension slots)
- CMS pages: `after_hero` on About (was `about-after-hero`), Blog, Page
- Dropped MSP `Solusi.vue` / `enable_solusi` (MSP via `PricingMsp`); `/solusi` aliases to `/services` (ISP)

### Fixed


## Notes (pre-changelog era)

Keputusan tema yang sudah terdokumentasi sebagai ADR (bukan pengganti entri di atas):

- About page refactor — [ADR-001](../../../../../../../docs/themes/layung/ADR-001-about-page-layung-refactor.md)
- Hero telemetry / slider / customizer isolation — [ADR-007](../../../../../../../docs/themes/layung/ADR-007-hero-section-telemetry-animations-slider-and-customizer-isolation.md)
- Navigation hierarchy / footer — [ADR-008](../../../../../../../docs/themes/layung/ADR-008-navigation-hierarchy-alignment-homepage-anchors-and-footer-refinement.md)

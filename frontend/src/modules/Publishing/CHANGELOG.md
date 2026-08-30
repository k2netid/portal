# Changelog — Publishing (frontend)

## [Unreleased]

### Added
- Theme Customizer hide/show controls labels (en/id/su).
- Theme Customizer preview load-failure copy + open-tab CTA (en/id/su).
- Theme Customizer preview click hint (en/id/su).
- Theme Customizer bridge copy (en/id/su): Menu Builder + Design/Content handoff callouts.
- Theme Customizer sidebar labels for CMS pages (en/id/su).
- Theme Customizer sidebar labels for Search page (en/id/su).
- Theme Customizer sidebar labels for Pricing page (en/id/su).
- Theme Customizer sidebar labels for Home Partners and CTA sections (en/id/su).
- Theme Customizer sidebar labels for Home Testimonials and Updates sections (en/id/su).

### Fixed
- Public comment API uses `/public/publishing/contents/{id}/comments`.
- Pinia store id is `publishing`. Public settings always come from the public system API.

### Changed
- `hasSubstantivePublicContent` treats non-empty `meta.builder_blocks` as public content.
- SEO and Discussion tabs imported from this pack, not Core Identity.

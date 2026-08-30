# Changelog — Publishing (frontend)

## [Unreleased]

### Added
- Theme Customizer sidebar labels for Home Partners and CTA sections (en/id/su).
- Theme Customizer sidebar labels for Home Testimonials and Updates sections (en/id/su).

### Fixed
- Public comment API uses `/public/publishing/contents/{id}/comments`.
- Pinia store id is `publishing`. Public settings always come from the public system API.

### Changed
- `hasSubstantivePublicContent` treats non-empty `meta.builder_blocks` as public content.
- SEO and Discussion tabs imported from this pack, not Core Identity.

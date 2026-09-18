# Changelog — Publishing

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added
- `BuilderDocumentValidator` rejects malformed `meta.builder_blocks` on content write.

### Changed
- Pack version → `1.0.1`
- Customizer sidebar i18n: `page_tim` → `page_team` (Team page labels)
- Settings groups are `seo` + `comments` only. Site identity (`general`) stays on kernel Identity.
- SEO and Discussion setting tabs live in this pack (`views/settings/tabs/`).

### Fixed
- Scramble OpenAPI: Publishing settings GEN001 — private `applyBulkSettings` shared by PUT + bulk-update.
- Scramble OpenAPI: Content publish body check after static validate (VR002).
- Cleaned up unused class imports and code style in feature test suites.

### Added

- Initial port from `ja-cms` Content/Publishing (+ ContentTemplate model).
- Soft stubs for Media / Layout / Newsletter / CMS AI until those packs land.
- Manifest dependency on `library`.
- Permission seeder runs on `library` / `publishing` activate (`extension_activated`).

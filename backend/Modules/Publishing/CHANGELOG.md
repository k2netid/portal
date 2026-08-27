# Changelog — Publishing

## [Unreleased]

### Changed
- Settings groups are `seo` + `comments` only. Site identity (`general`) stays on kernel Identity.
- SEO and Discussion setting tabs live in this pack (`views/settings/tabs/`).

### Added

- Initial port from `ja-cms` Content/Publishing (+ ContentTemplate model).
- Soft stubs for Media / Layout / Newsletter / CMS AI until those packs land.
- Manifest dependency on `library`.
- Permission seeder runs on `library` / `publishing` activate (`extension_activated`).

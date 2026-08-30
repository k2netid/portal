# Changelog — JA-Mail (frontend)

## [Unreleased]

### Changed

- AppModule `extensionSlug: 'mail'`; console registers Mail only when product-active
- Security inspector: SPF/DKIM preview cards replaced with MTA/DNS ownership copy
- AI settings: LIVE vs NOT AVAILABLE scopes; composer gated on `ai_ready`
- Sidebar folders: Scheduled + Archive; cancel scheduled from detail
- Detail respects `block_remote_images` from mail settings

### Added

- Module README + this changelog

## [P1–P2] — 2026-08-23

### Changed

- Nav/router permission → `use mail`
- Composer attachments via FormData; sync messaging stays local-index honest

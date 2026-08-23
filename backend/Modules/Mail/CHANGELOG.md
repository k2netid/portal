# Changelog — JA-Mail (backend)

All notable changes to `Modules/Mail` are documented here.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added

- In-app notifications on `MailMessageFailed` and `VacationAutoReplySent`
- Module README + this changelog
- Honest AI gating vs Settings → AI (`ai_ready`, locked unavailable scopes)
- `mail:process-snoozed` wake path; global quota/retention require `manage system`
- Cancel scheduled send → drafts; archive + scheduled folder counts
- Attachment extension blocklist + owned-path download checks
- Storage quota enforcement on send/schedule (`MAIL_QUOTA_EXCEEDED`)
- `MAIL_QUEUE_OUTBOUND` documented in `.env.example`

### Changed

- `EnsureMailExtensionActive` delegates to Core `EnsureExtensionActive` (same `MAIL_EXTENSION_INACTIVE` code)
- Manifest: `license`, `license_tier`, feature `category` — aligns with frozen module contract
- Docs: IMAP / SPF / DKIM removed from kernel backlog (mail server / DNS ownership)
- Mail AI settings no longer present summarize/smart-reply/sentiment as live
- Manifest copy aligned to kernel reality (no threads / live IMAP claims)
- FE: Mail AppModule registers only when registry status is active (reload after first activate)

## [P2] — 2026-08-23

### Added

- `trashed_at` + `mail:purge-trash`
- `MailboxIngestService`, `VacationAutoReplyService`
- `MailInboundSyncInterface` + `LocalIndexInboundSync`
- Soft MediaLibrary register on attachments

## [P1] — 2026-08-23

### Added

- Permission `use mail`
- `OutboundMailPortInterface` + `OutboundMailDispatcher`
- `MailMessageSent` / `MailMessageQueued` + optional `SendOutboundMailJob`

## [P0] — 2026-08-22

### Added

- `SystemMailConfig` for global SMTP settings
- Attachment upload → storage → SMTP
- `reply_to` on dispatch
- Eloquent relations + FK `account_id`

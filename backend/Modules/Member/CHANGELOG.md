# Changelog — Member

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added
- Reader register/login, bookmarks, signed email verification.
- `extension.active:member` on public and authenticated member APIs.
- Console Members directory (`GET /manage/members`, `members.index`). Email verify required for bookmarks and member comments (`EMAIL_UNVERIFIED`).

### Fixed
- Scramble OpenAPI: Member profile/directory/email unique rules nullsafe / validate-before-load (VR002).
- Fixed MemberPortalService return type annotation and strict usort order comparison.
- Added array query type annotation to MemberMailer::frontendUrl.

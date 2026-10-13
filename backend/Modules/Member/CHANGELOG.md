# Changelog — Member

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added
- **Sign in with Google (member portal)**: Socialite OAuth (`MEMBER_GOOGLE_*` or Integrations UI settings), `google_id` on `mem_members`, public routes `auth/google/{redirect,callback,2fa}`, FE Login/Register + `/member/oauth/callback`. Toggle `enable_member_google_login` + Client ID/Secret on Identity & Integrations. Console Google/GitHub stubs removed — operators stay on password/passkey/2FA.
- Reader register/login, bookmarks, signed email verification.
- `extension.active:member` on public and authenticated member APIs.
- Console Members directory (`GET /manage/members`, `members.index`). Email verify required for bookmarks and member comments (`EMAIL_UNVERIFIED`).
- Member Portal sidebar, breadcrumbs, and localization strings for Billing module integration.

### Fixed
- Scramble OpenAPI: Member profile/directory/email unique rules nullsafe / validate-before-load (VR002).
- Fixed MemberPortalService return type annotation and strict usort order comparison.
- Added array query type annotation to MemberMailer::frontendUrl.

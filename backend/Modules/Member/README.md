# Member

Public reader accounts. Not console IAM.

## Purpose

`mem_members` + `auth:member` (Sanctum). Publishing comments/bookmarks use `MemberIdentityPort`. Console users stay on `srv_auth_users`.

## Paths

- API: `routes/api.php` — gated `extension.active:member`
- Verify email: signed `GET /api/v1/public/member/verify-email/{id}/{hash}` → `/site/member/verified`
- FE public: `frontend/src/modules/Member/views/` (`/site/member/*`)
- FE console: `members.index` (`/dash/members`) — list only, permission `view members`

## Gates

- Pack off → register/login/me/bookmarks 403
- Register still issues a Sanctum token so `me` and resend-verification work
- Bookmarks and member comments require `email_verified_at` (`member.verified` / `EMAIL_UNVERIFIED`)
- Outbound verify mail uses JA-Mail when the **mail** pack is product-active; otherwise Laravel `Mail::html`

## Agent notes

- Do not wire comments to `User`
- Console Members is a thin directory, not IAM. Operators stay on `srv_auth_users`.

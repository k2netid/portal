# Member

Public reader accounts. Not console IAM.

## Purpose

`mem_members` + `auth:member` (Sanctum). Publishing comments/bookmarks use `MemberIdentityPort`. Console users stay on `srv_auth_users`.

## Paths

- API: `routes/api.php` — gated `extension.active:member`
- Verify email: signed `GET /api/v1/public/member/verify-email/{id}/{hash}` → `/site/member/verified`
- FE: `frontend/src/modules/Member/views/` (public shell only; no console `module.ts`)

## Gates

- Pack off → register/login/me/bookmarks 403
- Email verification is **soft**: register still issues a token; `email_verified_at` is set after the signed link
- Outbound verify mail uses JA-Mail when the **mail** pack is product-active; otherwise Laravel `Mail::html`

## Agent notes

- Do not wire comments to `User`
- Do not add a console member-admin UI unless a later product asks for it

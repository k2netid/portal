# Member Area — Adaptive Portal (RFC)

**Status:** Implemented (P0–P3 live on main)  
**Update:** 2026-09-01  
**Audience:** agents and humans building public reader UX and pack contributions  
**Depends on:** [module-contract.md](./module-contract.md), [lifecycle.md](./lifecycle.md), [rbac-and-lifecycle-seeders.md](./rbac-and-lifecycle-seeders.md)

---

## Update: 2026-08-31 (account lifecycle)

Reader account self-service beyond P1–P3:

| Path / API | Purpose |
| :--- | :--- |
| `/member/forgot-password` + `POST /public/member/forgot-password` | Request reset link |
| `/member/reset-password` + `POST /public/member/reset-password` | Set new password |
| `PUT /member/email` + signed confirm | Change email (confirm on new address) |
| `DELETE /member/account` | Delete reader account (`confirm=DELETE`) |
| Demo reader | `INSTALL_SEED_DEMO=true` → `reader@example.com` / `password12` |

**Registration settings (Security tab):**

| Setting key | Applies to | Default |
| :--- | :--- | :--- |
| `enable_registration` | Console operators only (`srv_auth_users` / `/auth/console-sign-up`) | off |
| `enable_member_registration` | Public readers only (`mem_members` / `/member/register`) | on |

These flags are independent. Disabling console registration does **not** close reader signup, and vice versa.

### Security reuse + audit (2026-09-01)

Readers reuse Core security controls (not a parallel stack):

| Control | Setting / port | Surfaces |
| :--- | :--- | :--- |
| Password policy | `password_*` + `PasswordPolicyPortInterface` | Register, reset, portal change, console create/edit |
| Captcha | `enable_captcha` + `captcha_on_*` + `MemberCaptchaGuard` | Login, register, forgot-password |
| Login throttle | `login_attempts_limit` / `block_duration_minutes` + `LoginThrottlePortInterface` (`realm=member`) | Login (cache only — not shared IpList with console) |
| 2FA | `enable_2fa` + `mem_member_two_factor` + `/member/2fa/*` | Portal Security + login step |

**Monitoring:** `MemberSecurityAuditPortInterface` writes to Core `sec_logs` with `metadata.realm=member` and `member_id` / `member_email`. Never puts member UUIDs in `user_id` (console User FK).

| Event types (examples) | When |
| :--- | :--- |
| `member_register`, `member_login_success` / `_failed` / `_throttled` | Auth |
| `member_password_changed`, `member_password_reset(_requested)` | Password |
| `member_email_change_requested`, `member_email_changed` | Email |
| `member_2fa_enabled` / `_disabled` | 2FA |
| `member_account_deleted` | Account delete |

**Operator UX:** Security Journal filter **Realm: Readers**; member detail card **Recent security events** via `GET /api/v1/manage/members/{id}/security-events`.

**Portal 2FA extras (2026-09-01):** `POST /api/v1/member/2fa/regenerate-backup-codes` (password required). Audit events: `member_logout`, `member_avatar_uploaded`, `member_2fa_backup_codes_regenerated`.

E2E: `cd frontend && PLAYWRIGHT_BASE_URL=http://127.0.0.1:5273 npm run test:e2e:member` (captcha bypass header; use `Password12!` or set `E2E_MEMBER_PASSWORD`). On PVE hosts without browser libs, use `npm run test:e2e:smoke:docker`.

API smoke: `bash scripts/member-security-qa.sh`

Legacy `Account.vue` removed; `/member/account` redirects to profile.

---

### Quiet member theme shell (2026-08-31)

Authenticated `/member/*` (portal) does **not** use the marketing Header/Footer. `FrontendLayout` swaps chrome when `meta.memberShell` is set:

| Surface | Chrome |
| :--- | :--- |
| Public pages | Theme `Header` + `Footer` (marketing) |
| `/member/login`, register, forgot/reset | Same marketing chrome |
| Authenticated portal (`memberShell`) | Theme `MemberHeader` + `MemberFooter` |

**Theme contract (bundled themes):** each theme ships:

- `components/layout/MemberHeader.vue` — logo, Back to site, account menu (Profile / Sign out); no marketing nav
- `components/layout/MemberFooter.vue` — one-line copyright; no Explore/Company columns

Resolver basename match (`MemberHeader` / `MemberFooter`) + bundled fallbacks (janari → sarangenge → layung). Uploaded themes without these files fall back to janari quiet chrome.

**Portal body** (`MemberPortalLayout`): sidebar + main workspace (full remaining width, card shell). Shared `MemberPortalTopBar`.

---

### Reader profile schema (2026-08-31 / avatar 2026-09-01)

Standard fields on `mem_members` (reader self-service via `PATCH /member/profile`):

| Field | Editable | Notes |
| :--- | :--- | :--- |
| `name` | Yes | Display name |
| `email` | Security flow | Signed confirm on new address |
| `phone` | Yes | Optional, max 32 chars |
| `avatar` | Yes | URL/path string, max 512 |
| `bio` | Yes | Optional, max 500 chars |
| `locale` | Yes | BCP 47-ish (`en`, `id`, `su`) |
| `timezone` | Yes | IANA (`Asia/Jakarta`, …) |
| `status` | Console only | `active` / `inactive` |
| `email_verified` | Verify link | `email_verified_at` |
| `last_login_at` | System | Updated on login |
| `created_at` | Read-only | Member since |

**Avatar:**

| Action | API / UX |
| :--- | :--- |
| Upload | `POST /api/v1/member/profile/avatar` (`multipart`, field `file`, image ≤2MB) → `media/members/{id}/` |
| Paste URL | Optional fallback in portal modal / console form (`https://…`) |
| Clear / replace | Owned files under `media/members/{id}/` are deleted; external URLs are never deleted |
| Account force-delete | Purges the member storage directory |
| Resize / optimize | Server-side via Media pack (`MediaService` max-width + optional WebP). **No client crop UI.** |

Serialization: `MemberPublicProfile::serialize()` — single source for `/member/me`, portal, auth responses.

---

## 1. Problem (historical — solved)

When this RFC was written, Member was a thin auth surface without a portal shell. **That gap is closed.** Current reality:

| Surface | Status |
| :--- | :--- |
| Public auth routes | `/member/login`, register, forgot/reset, verified |
| Portal shell | Quiet theme chrome + `MemberPortalLayout` (sidebar + top bar) |
| Core pages | Dashboard, Profile, Security |
| Adaptive packs | Bookmarks, Comments, Newsletter, Submissions via `MemberAreaRegistry` |
| Theme headers | Link to `/member/profile` — route exists (`member.profile`) |
| Console directory | Full CRUD under `/dash/members` + `/api/v1/manage/members` |

Operators keep modular console IAM; readers keep `mem_members` + `auth:member`.

---

## 2. Goals / Non-goals

### Goals

1. **Portal shell** for authenticated readers (`MemberPortalLayout` + theme `MemberHeader`/`MemberFooter`) with sidebar nav + main content (not console chrome, not marketing nav).
2. **Contribution registry** so product-active packs can inject member routes, nav items, and widgets.
3. **Capability gates** that hide UI and reject APIs when the contributing pack is inactive.
4. Keep **console IAM** (`srv_auth_users` + Spatie) completely separate from **reader identity** (`mem_members` + `auth:member`).
5. Remain themable: portal lives under the public Site SPA (apex `/` when Site is active).

### Non-goals (this RFC)

- Billing / subscription tiers (JA-CP / vertical product — see [architectural-status.md](../architectural-status.md)).
- Spatie roles on `mem_members`.
- Unloading nwidart providers when Member or other packs deactivate.
- Replacing theme marketing pages with a second public shell.
- Client-side avatar cropper (server Media optimize is enough).

---

## 3. Identity model (naming)

| Concept | Storage | Guard | Purpose |
| :--- | :--- | :--- | :--- |
| **Operator** | `srv_auth_users` | `auth:sanctum` (web) | Console; Spatie RBAC + ABAC |
| **Reader** | `mem_members` | `auth:member` | Public account; capabilities + verified email |
| Spatie role `member` | Role on **operators** | `web` | Historical name for Jejakawan subscription self-service — **not** a public reader |

**Docs / UI language:** prefer **Operator** vs **Reader**. Avoid saying “member role” when you mean a public account.

Future rename (breaking, separate RFC): Spatie `member` → `subscriber`.

---

## 4. Target architecture

```
Public SPA (Site pack active)
└── FrontendLayout (theme chrome)
    └── /member/*
        ├── guest: login, register, verified
        └── auth: MemberPortalLayout
            ├── core: dashboard, profile, security
            └── contributions from active packs
                ├── publishing → bookmarks, comments
                ├── newsletter → preferences
                ├── forms → my submissions
                └── …
```

### Components

| Piece | Responsibility |
| :--- | :--- |
| `MemberPortalLayout.vue` | Shell: header strip, nav, outlet |
| `MemberAreaRegistry` (FE) | Collects routes/nav/widgets from packs; filters by `active_extensions` |
| Manifest `member_area` | Declares what a pack contributes when **both** `member` and the pack are product-active |
| `EnsureMemberCapability` (BE) | Middleware: pack active + optional `email_verified_at` + capability flag |
| `MemberIdentityPort` | Existing Publishing bridge — unchanged contract |

### Registry flow

```
boot public SPA
  → load active_extensions from system boot payload
  → MemberAreaRegistry.register(core contributions from Member pack)
  → for each AppModule with memberArea contributions:
        if extensionSlug ∈ active_extensions AND deps satisfied:
          register routes + nav + widgets
  → router.addRoute under /member (auth children)
```

Unlike console optional modules, **reader contributions should not require a full page reload** when possible: filter nav client-side from `active_extensions`; route components stay code-split and 404/redirect if pack turns off mid-session.

---

## 5. Manifest contribution contract

Extend pack `manifest.json` (see schema notes in [module-contract.md](./module-contract.md)):

```json
{
  "slug": "publishing",
  "member_area": {
    "depends_on": ["member"],
    "nav": [
      {
        "slug": "bookmarks",
        "label_key": "member.nav.bookmarks",
        "route": "member.bookmarks",
        "icon": "Bookmark",
        "order": 20,
        "requires_verified": true,
        "capability": "member.bookmarks"
      }
    ],
    "widgets": [
      {
        "slug": "recent-bookmarks",
        "slot": "dashboard",
        "order": 10,
        "capability": "member.bookmarks"
      }
    ],
    "capabilities": ["member.bookmarks", "member.comments"]
  }
}
```

### Rules

1. `member_area` is ignored unless pack **and** `member` are product-active.
2. `depends_on` may list additional packs (e.g. Library for taxonomy labels).
3. `capability` strings are namespaced (`member.<feature>`), not Spatie permission names.
4. Console `permissions[]` stay for **operators** managing the directory (`view members`, `manage members`).

### Frontend module mirror

```ts
// frontend/src/modules/Publishing/memberArea.ts
export const publishingMemberArea = {
  extensionSlug: 'publishing',
  routes: [ /* RouteRecordRaw under /member */ ],
  navigation: [ /* nav items */ ],
  widgets: [ /* dashboard widgets */ ],
};
```

Register via `MemberModule` / public bootstrap the same way console uses `deferredConsoleModules`, but into `MemberAreaRegistry`.

---

## 6. Routing plan

### Core (Member pack)

| Path | Name | Meta |
| :--- | :--- | :--- |
| `/member/login` | `member.login` | `memberGuest` |
| `/member/register` | `member.register` | `memberGuest` |
| `/member/verified` | `member.verified` | public |
| `/member` | `member.dashboard` | `requiresMember` |
| `/member/profile` | `member.profile` | `requiresMember` |
| `/member/account` | redirect → profile | compatibility alias |
| `/member/security` | `member.security` | password / email / delete |

### Pack examples (adaptive)

| Path | Pack | Gate |
| :--- | :--- | :--- |
| `/member/bookmarks` | publishing | `extension.active:publishing` + verified |
| `/member/comments` | publishing | same |
| `/member/newsletter` | newsletter | `extension.active:newsletter` |
| `/member/submissions` | forms | `extension.active:forms` |

When a pack is inactive: nav item hidden; deep link → soft page “Feature unavailable” + link home (not console login).

---

## 7. Capability model (readers)

Do **not** put Spatie on `mem_members`.

### Evaluation order

1. Extension `member` product-active → else 404 entire `/member/*` (except documented public verify landing).
2. Contributing extension product-active → else capability false.
3. `email_verified_at` if `requires_verified`.
4. Optional future: plan/entitlement from vertical billing (out of scope).

### Storage options (capability overrides)

| Option | Pros | Cons |
| :--- | :--- | :--- |
| **A. Derived only** (no table) | Zero schema; capability = f(active packs, verified) | Cannot grant per-member overrides |
| **B. `mem_member_capabilities` JSON** | Overrides / beta flags | Sync story on deactivate |
| **C. Entitlement service port** | Ready for billing | Overkill until P5 vertical |

**Default:** **Option A** until a product needs per-reader grants; keep a port interface so B/C can plug in.

---

## 8. Backend API shape

Keep existing:

- `POST /api/v1/public/member/register|login`
- `GET /api/v1/member/me`
- `PATCH /api/v1/member/profile`
- `POST /api/v1/member/profile/avatar`
- Bookmarks under `/api/v1/member/bookmarks` (already gated)

Live portal APIs:

| Endpoint | Notes |
| :--- | :--- |
| `GET /api/v1/member/portal` | Nav + widgets + capabilities for current reader (computed from active extensions) |
| Pack-scoped APIs | Remain on owning pack routes with `extension.active:<slug>` + `auth:member` |

Console directory APIs stay under `/api/v1/manage/members` with Spatie `view members` / `manage members`.

**Console directory (2026-08-31):**

| Method | Path | Permission | Notes |
| :--- | :--- | :--- | :--- |
| `GET` | `/manage/members/stats` | view | Stat cards |
| `GET` | `/manage/members` | view | Search, filters, pagination |
| `GET` | `/manage/members/export` | view | CSV export |
| `POST` | `/manage/members` | manage | Create reader account |
| `GET` | `/manage/members/{id}` | view | Detail + activity counts |
| `PATCH` | `/manage/members/{id}` | manage | Profile, status, password, verify |
| `DELETE` | `/manage/members/{id}` | manage | Soft delete |
| `POST` | `/manage/members/{id}/restore` | manage | Restore from trash |
| `DELETE` | `/manage/members/{id}/force` | manage | Permanent delete + relation cleanup |
| `POST` | `/manage/members/bulk-action` | manage | activate, deactivate, verify, delete, restore, force_delete |

Soft delete uses `deleted_at` on `mem_members`. Force delete removes bookmarks; comments/submissions are anonymized (`member_id` null). Newsletter stays linked by email.

Console UI: list (DataTable + bulk), create/edit forms, detail page with activity stats — same patterns as Users / Newsletter subscribers.

---

## 9. Theme integration

| Concern | Approach |
| :--- | :--- |
| Layout | Portal under `FrontendLayout`; optional slim chrome via theme setting `member_portal_chrome` |
| Header CTAs | Prefer `/member` (dashboard) when authenticated; login URL from customizer |
| i18n | Pack locales under `theme.*` only for marketing; portal strings in `Member` + contributing pack locales (`id`/`en`/`su`) |
| Themes | Janari / Sarangenge / Layung consume same routes — no per-theme portal forks |

---

## 10. Phased delivery

| Phase | Deliverable | Exit criteria |
| :--- | :--- | :--- |
| **P0** | Docs (this RFC) + CMS roles fix (see RBAC doc) + `/member/profile` route + `MemberPortalLayout` stub + registry skeleton | Login → dashboard shell with Profile nav |
| **P1** | Profile + security pages; `GET /member/portal` bootstrap payload | Account settings work without pack widgets |
| **P2** | Publishing bookmarks/comments widgets | Pack off → nav gone, API 403 |
| **P3** | Newsletter + Forms contributions | Same adaptive pattern |
| **P4** | Entitlements / tiers | Requires named vertical product |

---

## 11. Testing

- Feature: member auth + portal payload with Publishing on/off.
- FE smoke: login → dashboard → bookmarks visible only if publishing active.
- Guard: inactive `member` → `/member/*` 404; inactive publishing → bookmarks 403.
- Regression: console Members directory unchanged.

---

## 12. Open questions

1. Guest bookmarks (cookie) vs require login — product call.
2. Does `INSTALL_PROFILE=cms` (Site off) need a minimal member SPA, or is Site always required for portal?

Resolved: `/member/account` redirects to `/member/profile` (compatibility alias only).

---

## Related

- Lifecycle & dual identity: [lifecycle.md](./lifecycle.md)
- RBAC + seeders: [rbac-and-lifecycle-seeders.md](./rbac-and-lifecycle-seeders.md)
- Module contract: [module-contract.md](./module-contract.md)
- Install profiles: [install-profiles.md](./install-profiles.md)

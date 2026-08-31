# Member Area — Adaptive Portal (RFC)

**Status:** Implemented (P0–P3 live on main, 2026-08-31)  
**Update:** 2026-08-31  
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

### Reader profile schema (2026-08-31)

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

Serialization: `MemberPublicProfile::serialize()` — single source for `/member/me`, portal, auth responses.

---

## 1. Problem

Today the **Member** pack is a thin reader auth surface:

| Surface | Reality |
| :--- | :--- |
| Public routes | Hardcoded in `frontend/src/engine/router/public.ts` (`/member/login`, `/register`, `/account`, `/verified`) |
| Portal shell | **None** — pages sit inside `FrontendLayout` with no dedicated nav |
| Cross-pack features | Bookmarks + comments via `MemberIdentityPort` only |
| Theme headers | Link to `/member/profile` but **no route exists** (tech debt) |
| Adaptivity | Enabling Publishing / Newsletter / Forms does **not** add member pages |

Operators get a modular console (`AppModule` registry). Readers get four static pages. That asymmetry blocks an adaptive “my account” experience when CMS packs turn on/off.

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
| `/member/profile` | `member.profile` | `requiresMember` — **fix existing header debt** |
| `/member/account` | alias → profile or security | keep redirect for compatibility |
| `/member/security` | `member.security` | password / sessions |

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

### Storage options (decide at P1)

| Option | Pros | Cons |
| :--- | :--- | :--- |
| **A. Derived only** (no table) | Zero schema; capability = f(active packs, verified) | Cannot grant per-member overrides |
| **B. `mem_member_capabilities` JSON** | Overrides / beta flags | Sync story on deactivate |
| **C. Entitlement service port** | Ready for billing | Overkill until P5 vertical |

**RFC default:** **Option A** until a product needs per-reader grants; keep a port interface so B/C can plug in.

---

## 8. Backend API shape

Keep existing:

- `POST /api/v1/public/member/register|login`
- `GET /api/v1/member/me`
- Bookmarks under `/api/v1/member/bookmarks` (already gated)

Add (phased):

| Endpoint | Notes |
| :--- | :--- |
| `GET /api/v1/member/portal` | Nav + widgets + capabilities for current reader (computed from active extensions) |
| Pack-scoped APIs | Remain on owning pack routes with `extension.active:<slug>` + `auth:member` |

Console directory APIs stay under `/api/v1/manage/members` with Spatie `view members` / `manage members`.

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

1. Should `/member/account` remain canonical or redirect permanently to `/member/profile`?
2. Guest bookmarks (cookie) vs require login — product call.
3. Does `INSTALL_PROFILE=cms` (Site off) need a minimal member SPA, or is Site always required for portal?

---

## Related

- Lifecycle & dual identity: [lifecycle.md](./lifecycle.md)
- RBAC + seeders: [rbac-and-lifecycle-seeders.md](./rbac-and-lifecycle-seeders.md)
- Module contract: [module-contract.md](./module-contract.md)
- Install profiles: [install-profiles.md](./install-profiles.md)

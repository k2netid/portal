# Install profiles — Core vs CMS vs public Site

Update: 2026-08-31

Fresh `migrate:fresh --seed` must leave a **usable product** without tinker. Pack activation is product state (`sys_extensions.status`), not “folder exists”.

## Contract (important)

Install profiles **enforce** the selected product shape:

| Profile | Activates | Deactivates (extras) |
| :--- | :--- | :--- |
| **cms_site** | CMS family + Site + Member | — |
| **cms** | CMS family | **Site** (and its reverse dependents) |
| **core** | — (discover) | CMS family + Site + Member |

Bulk activate / deactivate remain available for fine-grained ops. All bulk + profile mutations share `ExtensionLifecycleLock` (HTTP `423` when busy).

## Profiles

| Profile | Env | Apex `/` | Desired product-active set |
| :--- | :--- | :--- | :--- |
| **core** | `INSTALL_PROFILE=core` | Kernel **landing** | Kernel only |
| **cms** | `INSTALL_PROFILE=cms` | Kernel landing | CMS family (Site off) |
| **cms_site** | `INSTALL_PROFILE=cms_site` (default when `Modules/Site` exists) | **Public theme** | CMS family + **Site** + **Member** (login/register at `/member/*`) |

Console always stays at `/auth/console-sign-in` and `/dash` — never the default face of apex `/`, and **not linked** from the kernel landing HTML (operators learn the path from README / this doc).

## Entry points (all call the same applicator)

1. `DatabaseSeeder` after system seed  
2. `php artisan ja:apply-install-profile [core|cms|cms_site]`  
3. Web/CLI install (`migrate --seed` already runs seeder)  
4. Console API:
   - `GET …/extensions/install-profile-preview?profile=cms` → `will_activate` / `will_deactivate` / warnings
   - `POST …/extensions/apply-install-profile` `{ "profile": "cms" }` → `423` if lifecycle busy

Applicator steps: **discover** → **preview gates** → **activate** missing → **deactivate** extras → theme baseline (when applicable).

## Operator without code

- App Store shows a contextual banner when **Site is off** (“Enable public website” → `cms_site`) or when CMS packs are fully inactive
- App Store → **Install profile** → pick option (UI loads preview, shows activate/deactivate lists + warnings)
- Or CLI: `php artisan ja:apply-install-profile cms`
- Or re-run seed with the right `INSTALL_PROFILE`

## License note

Site declares `license_tier: pro`. Local/testing sets `INSTALL_SKIP_LICENSE_CHECKS=true` by default so fresh installs work without JA-CP. Production always enforces license (skip is ignored outside `local`/`testing`).

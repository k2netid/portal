# Install profiles — Core vs CMS vs public Site

Update: 2026-08-31

Fresh `migrate:fresh --seed` must leave a **usable product** without tinker. Pack activation is product state (`sys_extensions.status`), not “folder exists”.

## Profiles

| Profile | Env | Apex `/` | What gets product-active |
| :--- | :--- | :--- | :--- |
| **core** | `INSTALL_PROFILE=core` | Console login | Kernel only |
| **cms** | `INSTALL_PROFILE=cms` | Console login | CMS family (library→publishing→media→layout→…) |
| **cms_site** | `INSTALL_PROFILE=cms_site` (default when `Modules/Site` exists) | **Public theme** | CMS family + **Site** |

Console always stays at `/auth/console-sign-in` and `/dash` when Site is on.

## Entry points (all call the same applicator)

1. `DatabaseSeeder` after system seed  
2. `php artisan ja:apply-install-profile [core|cms|cms_site]`  
3. Web/CLI install (`migrate --seed` already runs seeder)  
4. Console API: `POST /api/v1/manage/infra/extensions/apply-install-profile` `{ "profile": "cms_site" }`

Applicator steps: **discover** Modules → **activate** graph plan → **scan themes** + ensure Janari/Sarangenge default → clear theme caches.

## Operator without code

- Re-run seed with the right profile, or  
- App Store / Extensions → apply install profile API, or  
- CLI: `php artisan ja:apply-install-profile cms_site`

## License note

Site declares `license_tier: pro`. Local/testing sets `INSTALL_SKIP_LICENSE_CHECKS=true` by default so fresh installs work without JA-CP. Production enforces license.

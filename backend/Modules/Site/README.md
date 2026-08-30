# Site

Host contract for the public theme runtime at **apex `/`** when this pack is product-active.

## Purpose

Laravel boot gate + Vite public SPA (`public.html`). Not a console module. Themes live in the Layout pack.

## Boot gate

| Site pack | `/` | `/dash`, `/auth/console-*` | Legacy `/site/*` |
| :--- | :--- | :--- | :--- |
| **inactive** | Console SPA → login | Console | 404 |
| **active** | Public theme SPA | Console | 301 → apex path |

## Paths

- Provider: `app/Providers/SiteServiceProvider.php`
- Web: `backend/routes/web.php` + `SpaController`
- FE router: `frontend/src/engine/router/public.ts` (`createWebHistory('/')`)
- Pages resolve via `ThemePageResolver` / `PublicThemePage` from the **active** Layout theme

## Agent notes

- Gate on `Extension::isProductActive('site')`, not “theme activated”
- Console reserved first segments: `auth`, `install`, `maintenance`, `dash`, `ja-dash`, configured dashboard slug
- Do not send public 404 Home to the console SPA
- No `frontend/src/modules/Site` folder by design

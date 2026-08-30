# Site

Host contract for the public theme runtime at **apex `/`** when this pack is product-active.

## Purpose

Laravel boot gate + Vite public SPA (`public.html`). Not a console module. Themes live in the Layout pack.

## Boot gate (3 shells)

| Site pack | `/` | `/dash`, `/auth/console-*` | Legacy `/site/*` |
| :--- | :--- | :--- | :--- |
| **inactive** | Kernel **landing** (`landing.html`) | Console | 404 |
| **active** | Public **theme** SPA (overrides landing) | Console | 301 → apex path |

Console login is **never** the default face of `/`. Operators use `/auth/console-sign-in`.

## Paths

- Provider: `app/Providers/SiteServiceProvider.php`
- Web: `backend/routes/web.php` + `SpaController`
- FE: `landing.html` (Site off) · `public.html` (Site on) · `index.html` (console reserved paths)
- Pages resolve via `ThemePageResolver` / `PublicThemePage` from the **active** Layout theme

## Agent notes

- Gate on `Extension::isProductActive('site')`, not “theme activated”
- Console reserved first segments: `auth`, `install`, `maintenance`, `dash`, `ja-dash`, configured dashboard slug
- Do not send public 404 Home to the console SPA
- No `frontend/src/modules/Site` folder by design

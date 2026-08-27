# Site

Host contract for the public theme runtime at `/site`.

## Purpose

Laravel rewrite + Vite public SPA (`public.html`). Not a console module. Themes live in the Layout pack.

## Paths

- Provider: `app/Providers/SiteServiceProvider.php`
- Web fallback: `backend/routes/web.php`
- FE router: `frontend/src/engine/router/public.ts` (`createWebHistory('/site')`)
- Pages resolve via `ThemePageResolver` / `PublicThemePage` from the **active** Layout theme

## Agent notes

- Do not send public 404 Home to the console SPA
- Activate theme must change `/site` pages, not only Header/Footer
- No `frontend/src/modules/Site` folder by design

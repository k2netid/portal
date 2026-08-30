# Global locale files

Runtime i18n is assembled in `src/engine/i18n/`:

- **`en/*.json` / `id/*.json`** — shared UI strings (`common.*`, `sharedConsole.*`)
- **`src/modules/**/locales/{en,id}.json`** — module-owned namespaces (`publishing.*`, `system.*`, …)

Do not add `modules/` or `features/` folders here; those were removed after the co-located migration.

## Scripts

| Command | Purpose |
|---------|---------|
| `npm run i18n:check` | **Gate:** JSON syntax + nav / breadcrumbs / `labelKey` (runs in `type-check`, `build`, `quality:frontend`) |
| `npm run i18n:check:full` | Audit all `t()` / `$t()` without fallback in `src/` (may report app-wide gaps) |
| `npm run type-check` | `i18n:check` → `vue-tsc -b` |
| `npm run build` | `i18n:check` → `vue-tsc -b` → Vite |

## Adding strings

1. **Module UI** → edit `src/modules/<Area>/<Module>/locales/en.json` and `id.json`
2. **Cross-module sidebar / console chrome** → `src/locales/{en,id}/console.json`
3. **Global actions, errors, pagination** → `src/locales/{en,id}/actions.json`, etc.

Register new modules in `src/engine/i18n/moduleLocales.ts`.

**Public themes** (Janari, future extensions): co-located under `src/modules/Layout/views/themes/<slug>/locales/` → namespace `theme.<slug>.*`. See `views/themes/readme.md` and `src/engine/i18n/themeLocales.ts`.

# Theme Host Contract

Public themes (e.g. **Janari**) run inside the Jejakawan **hub** (`ja-control-plane` — single control plane, **bukan** Jejakawan multi-pelanggan per DB). This document defines what themes may import and what must live inside the theme package.

Per-customer hosted Jejakawan (dulu istilah “organization”) direncanakan di **`ja-organization`**, bukan di repo ini.

## Goals

- **UI** is owned by the theme (`themes/<slug>/ui/`, `components/`) — no dependency on console/dashboard components or `@/shared/components/ui`.
- **Data & safety** stay on the host — API, settings, menus, sanitization, captcha.

## Theme package layout

```
themes/<slug>/
  theme.json
  assets/styles/<slug>.css
  locales/
  composables/
  ui/
  pages/              # Route views only
  components/
    layout/           # Header, Footer
    sections/         # Hero, CTA, …
    blog/             # PostCard, sidebar
    shared/           # SplitText, PageDisabled, …
```

Global shell CSS stays in `frontend/src/styles/` (`foundation/`, `shell/*-tailwind.css`, `console.css`, `editor.css`). **Do not** add theme-specific rules there — keeps console/public styles from leaking.

## Allowed host imports (stable contract)

| Area | Examples | Purpose |
|------|----------|---------|
| Layout / theme runtime | `useTheme`, `useMenu`, `useThemeMotion`, `useThemeI18n`, `useThemeDataBindings` | Settings, menus, motion, bindings |
| Engine | `@/engine/api/client`, `@/engine/api/paths`, shared types | Dynamic Jejakawan data |
| Publishing (data only) | `Content` types, `publicContent` utils, optional services | Articles, pages |
| Shared composables | `useLanguage`, `useResponsiveDevice`, `useToast`, `useDarkMode('frontend')` | Cross-cutting behavior (not visual) |
| Shared security utils | `@/shared/utils/sanitizer` (via `ThemeSafeHtml` only) | HTML sanitization config |
| Config | `@/config/security` (`SECURITY_ROUTES`) | Login/dashboard URLs |
| Core stores (read-only) | `useSystemStore` for site name/logo when needed | Site identity (hub public settings) |

## Theme-safe host components

| Component | Path | Use |
|-----------|------|-----|
| `ThemeSafeHtml` | `@/modules/Content/Layout/components/themes/ThemeSafeHtml.vue` | Sanitized `v-html` for Jejakawan body (replaces `Core/System/.../SafeHtml` in themes) |
| `PluginSlot` | `@/shared/components/PluginSlot.vue` | App Blocks from active plugins (do not register blocks inside theme) |

## Forbidden in theme views (UI boundary)
- Registering plugin App Blocks inside theme packages — use `<PluginSlot>` only; blocks are registered via `pluginBootstrap` + plugin loaders.


- `@/shared/components/ui` (shadcn/console kit)
- `@/shared/components/DarkModeToggle.vue` (console navbar)
- `@/shared/layouts/**` (console chrome)
- `@/modules/Core/System/components/ui/**` (except captcha where unavoidable — prefer host wrapper later)

## Janari UI kit

Janari ships primitives under `themes/janari/ui/`:

- `ThemeToggle`, `DropdownMenu*`, `Button`, `Input`, `Textarea`, `Label`, `Checkbox`, `Card`, `Alert*`, `Select*`, `Popover*`

Import from:

```ts
import { ThemeToggle, Button } from '@/modules/Content/Layout/views/themes/janari/ui';
```

Other themes should copy this pattern or extract a shared **theme-kit** package later — not reuse the console UI kit.

## i18n

- Theme copy: `themes/<slug>/locales/{en,id}.json` → keys `theme.<slug>.*`
- Per-locale customizer values: `setting_key_en` / `setting_key_id` (fallback `setting_key`) via `useLocalizedThemeSetting()`
- Public Jejakawan pages: `GET /public/publishing/contents/{slug}?locale=en|id` merges `sys_translations` + `meta.{field}_{locale}`
- Register in `src/engine/i18n/themeLocales.ts`
- Prefer `useThemeI18n()` for relative keys inside theme SFCs

## Customizer (admin only — not part of public theme runtime)

| Layer | Path |
|-------|------|
| Host | `Layout/customizer/` — [naming-conventions.md](../../customizer/naming-conventions.md) |
| Theme extension | `views/themes/<slug>/customizer/index.ts` |

Public theme Vue files must **not** import customizer extension code.

## Registration

Bundled themes are loaded via `import.meta.glob` in `FrontendLayout.vue` and locale registration in `themeLocales.ts`. Future extensions should call `registerThemeLocales(i18n, slug, bundles)` when activated and register `ThemeCustomizerExtension` in `customizer/loaders/resolveThemeCustomizerExtension.ts`.

## Checklist for a new theme

1. Add `theme.json` + `locales/`
2. Implement route views expected by `ThemePageResolver`
3. Add `ui/` primitives (do not import `@/shared/components/ui`)
4. Use `ThemeSafeHtml` for rich HTML fields
5. Register locales in `themeLocales.ts`
6. Keep captcha/auth as host integrations only

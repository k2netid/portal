# Janari theme package

Self-contained public theme for the Jejakawan hub site (marketing + Jejakawan on `ja-control-plane`). Follow [Theme Host Contract](../theme-host-contract.md) for allowed host imports.

## Layout

```
janari/
├── theme.json              # Manifest + settings_schema (transition; split → customizer/)
├── customizer/             # Theme-only customizer extension (see customizer/readme.md)
├── assets/
│   └── styles/
│       └── janari.css      # Canvas tokens, presets, motion (public shell only)
├── locales/                # theme.janari.* copy
├── composables/            # Theme-specific logic (e.g. useJanariIdentity)
├── ui/                     # Presentation primitives (no @/shared/components/ui)
├── pages/                  # Route views (ThemePageResolver → Home, Blog, …)
└── components/
    ├── layout/             # Header, Footer, breadcrumbs
    ├── sections/           # Home blocks (Hero, CTA, …)
    ├── blog/               # Blog sidebar, post card
    └── shared/             # Cross-page helpers (SplitText, PageDisabled)
```

## Routing

Host resolves views by name:

| Resolver `page` prop | File |
|----------------------|------|
| `Home` | `pages/Home.vue` |
| `Solusi` | `pages/Solusi.vue` (`/solusi`) |
| `Tim` | `pages/Tim.vue` (`/tim`) |
| `Blog` | `pages/Blog.vue` |
| `components/Header` | `components/layout/Header.vue` |
| `components/Footer` | `components/layout/Footer.vue` |

See `themeViewResolver.ts` for matching rules (`pages/` preferred).

## Imports

```ts
// UI kit
import { Button, ThemeToggle } from '@/modules/Layout/views/themes/janari/ui';

// Theme composable
import { useJanariIdentity } from '@/modules/Layout/views/themes/janari/composables/useJanariIdentity';

// Sanitized Jejakawan HTML (host)
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
```

## CSS

Loaded from `main-public.ts` (public shell): `assets/styles/janari.css`. Do not add Janari rules under `frontend/src/styles/`.

## IDE / TypeScript

- Edit files under this folder only (e.g. `pages/Home.vue`, `components/layout/Header.vue`).
- If the editor still shows errors on old paths like `janari/Home.vue` or `janari/components/Header.vue`, close those tabs — they were moved in the restructure.
- Local `tsconfig.json` extends `frontend/tsconfig.app.json` (inherits `@/*` paths; no extra `baseUrl`).

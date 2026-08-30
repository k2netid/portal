# Theme & customizer naming conventions

Applies to **`ja-control-plane`** (single hub). Not for `ja-organization` (per-customer Jejakawan — separate repo later).

## Mental model

| Layer | Bahasa | Folder | Persisted as |
|-------|--------|--------|----------------|
| **Platform** | Customizer host / shell | `Layout/customizer/` | — (code only) |
| **Platform settings** | Global theme settings (any active theme) | `Layout/customizer/platform/schema/` | `themes.settings` JSON (API) |
| **Theme package** | Public UI + theme-only customizer | `Layout/views/themes/<slug>/` | same `settings` object, keys namespaced by convention |
| **Theme extension** | Sidebar, bindings, visibility rules | `views/themes/<slug>/customizer/` | co-located with theme |

Inspired by **Shopify** (`settings_schema.json` global vs section `{% schema %}`) and **WordPress** (`theme.json` settings vs theme templates).

---

## Directory layout

```
src/modules/Layout/
├── customizer/                          # HOST — admin theme customizer (console UI)
│   ├── naming-conventions.md            # this file
│   ├── readme.md
│   ├── types/
│   │   ├── customizer-schema.ts         # SettingDefinition, scope, extension types
│   │   └── extension.ts
│   ├── platform/
│   │   ├── schema/
│   │   │   └── global.settings.schema.json   # keys with scope "platform"
│   │   └── sidebar.groups.json               # host sidebar (identity, design, layout)
│   ├── loaders/
│   │   ├── resolveThemeCustomizerExtension.ts
│   │   └── mergeSettingsSchema.ts
│   └── shell/
│       └── ThemeCustomizerView.vue      # route target (`themes.customizer`)
│   └── loaders/
│       ├── mergeThemeSettingsSchema.ts
│       └── resolveThemeCustomizerExtension.ts
│
├── components/themes/customizer/        # HOST UI primitives (sidebar, preview, controls)
├── composables/
│   ├── useThemeCustomizer.ts            # save/history/API (host)
│   └── useThemeCustomizerLabels.ts
│
└── views/themes/
    ├── naming-conventions.md            # symlink-style pointer → ../../customizer/naming-conventions.md
    ├── theme-host-contract.md
    ├── ThemeCustomizer.vue              # legacy path; imports shell + extension
    └── <slug>/                          # e.g. janari
        ├── theme.json                   # manifest: name, supports, merged settings_schema (transition)
        ├── customizer/
        │   ├── readme.md
        │   ├── index.ts                 # exports ThemeCustomizerExtension
        │   ├── bindings.registry.json
        │   ├── sidebar.navigation.json   # reserved manifest categories (host sidebar)
        │   ├── sidebar.pages.json        # special-page nav items (optional)
        │   ├── schema.settings.json      # theme-scoped settings only
        │   └── composables/
        │       └── filterJanariCustomizerSettings.ts
        ├── pages/
        ├── components/
        ├── locales/
        └── assets/
```

---

## File naming

| Kind | Pattern | Example |
|------|---------|---------|
| Vue route view (admin) | `PascalCase` + semantic suffix | `ThemeCustomizerView.vue`, `ThemeIndex.vue` |
| Vue public page | `PascalCase` | `Home.vue`, `Contact.vue` |
| Vue section/layout | `PascalCase` under `components/<area>/` | `Hero.vue`, `Header.vue` |
| Composable | `use` + `PascalCase` + area | `useThemeCustomizer`, `useJanariCustomizerSettings` |
| Theme extension entry | `index.ts` | `janari/customizer/index.ts` |
| JSON schema (settings) | `kebab-case` + `.schema.json` or role suffix | `global.settings.schema.json`, `bindings.registry.json` |
| JSON navigation | `kebab-case` + `.navigation.json` | `sidebar.navigation.json` |
| CSS (theme) | `<slug>.css` under `assets/styles/` | `janari.css` |
| Types | `kebab-case.ts` or domain name | `customizer-schema.ts`, `extension.ts` |
| Config registry (TS re-export) | `camelCase` or legacy UPPER_SNAKE | prefer `getThemeBindingRegistry(slug)` over `THEME_BINDING_REGISTRY` |

**Avoid**

- `organization*`, `multi-organization*` in paths or keys (legacy; use `site`, `hub`, `subscription`, `member`)
- Theme name in host paths (`customizer/janari/` under host) — theme code stays under `views/themes/janari/`
- Generic `settings.json` without scope prefix in filename

---

## Setting keys (DB / `theme.settings`)

| Scope | Key pattern | Owner | Example |
|-------|-------------|-------|---------|
| `platform` | generic semantic | Host schema | `site_title`, `color_primary`, `layout_style` |
| `theme` | may include slug prefix for clarity | Theme schema | `hero_slide_count`, `color_preset` (Janari canvas) |
| `platform` bindings | fixed constant | Host | `theme_data_bindings` (`THEME_DATA_BINDINGS_KEY`) |
| i18n field (value) | `{key}_{locale}` | Theme | `hero_title_en`, `hero_title_id` |

**Schema field** (in JSON / `theme.json`):

```json
{
  "color_primary": {
    "type": "color",
    "scope": "platform",
    "category": "Colors",
    "group": "design.colors",
    "default": "#0ea5e9"
  },
  "hero_slide_count": {
    "type": "range",
    "scope": "theme",
    "category": "Hero Section",
    "group": "components.hero",
    "default": 3
  }
}
```

During migration, `scope` may be omitted; host treats unknown as `theme` if key only exists in theme package schema.

---

## TypeScript / import paths

Use alias `@/modules/Layout/...` (no new barrel unless `index.ts` is the extension entry).

```ts
// Host loading theme extension
import { resolveThemeCustomizerExtension } from '@/modules/Layout/customizer/loaders/resolveThemeCustomizerExtension';

// Theme extension (inside janari only)
import { janariCustomizerExtension } from '@/modules/Layout/views/themes/janari/customizer';
```

**Do not** import `janari/customizer` from other themes or from `PublicApp` / `FrontendLayout`.

---

## i18n key namespaces

| Namespace | Use |
|-----------|-----|
| `layout.customizer.platform.*` | Host shell: tabs, sidebar group labels shared by all themes |
| `publishing.theme_customizer.*` | Legacy shared labels (fallback) |
| `theme.<slug>.customizer.items.<setting_key>` | Field labels / hints for theme schema |
| `theme.<slug>.customizer.manifest_categories.<slug>` | Category headings from manifest |
| `theme.<slug>.pages.*` | Public theme copy (not customizer) |

Slug = theme folder name (`janari`), lowercase kebab-case.

---

## Vue routes & route names

| Route path | Route name | Component |
|------------|------------|-----------|
| `themes` | `themes` | `views/themes/Index.vue` |
| `themes/:slug/customizer` | `themes.customizer` | `ThemeCustomizer.vue` (→ shell) |

Param `slug` must match folder name under `views/themes/<slug>/`.

---

## `theme.json` manifest (transition)

**Current (split active):**

- `customizer/platform/schema/global.settings.schema.json` — 36 platform keys (`scope: "platform"`)
- `views/themes/janari/customizer/schema.settings.json` — 70 Janari keys (`scope: "theme"`)
- Legacy organization-demo keys removed via `REMOVED_KEYS` in `frontend/scripts/split-janari-theme-schema.mjs` (majors, programs/offerings pages, unused stats title)
- `theme.json` — **merged** `settings_schema` for PHP `theme.json` readers (generated, not hand-edited):

```bash
cd frontend && npm run theme:schema:merge    # split files → theme.json (default)
cd frontend && npm run theme:schema:split  # re-classify from theme.json (rare)
```

- Runtime/customizer merge: `mergeThemeSettingsSchema(slug)` in `useThemeCustomizer` + `useTheme`

---

## Adding a new theme

1. Copy `views/themes/janari/` structure (without janari-specific composables). **Janari is the CMS reference theme** — new themes should set `parent_theme: janari` and `supports.janari_canvas: true` unless they intentionally opt out.
2. Add `views/themes/<slug>/theme.json` + `locales/`.
3. Add `views/themes/<slug>/customizer/index.ts` implementing `ThemeCustomizerExtension`.
4. Register locales in `engine/i18n/themeLocales.ts`.
5. Register extension in `resolveThemeCustomizerExtension.ts` registry map.
6. Do **not** edit host sidebar hardcoded lists; use `sidebar.navigation.json` + `sidebar.pages.json` on the theme extension.
7. Validate host-only flow with `views/themes/hub-stub/` (empty theme schema + extension registered in `resolveThemeCustomizerExtension.ts`).
8. On first CMS boot (layout/site activate), auto-active frontend theme prefers **janari** over sarangenge.

---

## Related docs

- [theme-host-contract.md](../views/themes/theme-host-contract.md) — runtime imports for public theme Vue
- [views/themes/readme.md](../views/themes/readme.md) — public theme package layout

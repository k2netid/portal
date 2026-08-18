# Public themes

Each theme is a self-contained package (future: installable extension).

**Theme Host Contract** (what themes may import from the app vs. what must stay in the theme folder): see [theme-host-contract.md](./theme-host-contract.md).

```
themes/<slug>/
  theme.json
  readme.md
  customizer/         # theme-only customizer extension (bindings, sidebar rules)
  assets/styles/<slug>.css
  locales/
  composables/
  ui/
  pages/              # route views (Home.vue, Blog.vue, …)
  components/
    layout/           # Header, Footer
    sections/         # page sections
    blog/             # blog-specific
    shared/           # shared blocks
```

**Naming & folder rules:** [naming-conventions.md](./naming-conventions.md) (full doc under `Layout/customizer/`).

Example: [janari/readme.md](./janari/readme.md).

**Customizer smoke test:** [hub-stub/readme.md](./hub-stub/readme.md) — platform-only customizer (no Janari fields).

Public shell loads theme CSS from the active package (e.g. `janari/assets/styles/janari.css`). Shared tokens stay in `frontend/src/styles/foundation/` + `shell/public-tailwind.css`.

`npm run i18n:check` requires **symmetric** en/id definition counts across all locale bundles (same keys in both languages).

## i18n

- **Module Layout** (`layout.*`): menus, widgets admin, redirects, theme manager UI — stays in `src/modules/Content/Layout/locales/`.
- **Theme UI** (`theme.<slug>.*`): all copy inside this folder — `views/themes/<slug>/locales/{en,id}.json`.

Register bundled themes in `src/engine/i18n/themeLocales.ts`. Runtime keys example: `theme.janari.pages.contact.title`.

In theme components, prefer:

```ts
import { useThemeI18n } from '@/modules/Content/Layout/composables/useThemeI18n';
const { t } = useThemeI18n();
t('pages.contact.title'); // → theme.janari.pages.contact.title
```

Or explicit: `t('theme.janari.pages.contact.title')`.

### Theme customizer

| Layer | Location |
|-------|----------|
| Host shell (admin UI) | `Layout/customizer/` + `components/themes/customizer/` |
| Platform setting keys | `Layout/customizer/platform/schema/global.settings.schema.json` |
| Theme extension | `views/themes/<slug>/customizer/` (`index.ts`, `bindings.registry.json`, …) |

`theme.json` keeps English `label` / `options[].label` as stable fallbacks until schema is split. Translations live in the same theme package:

- `theme.<slug>.customizer.items.<setting_key>` — field labels and `*_hint` descriptions
- `theme.<slug>.customizer.common_options.<slugified_option_label>` — select/repeater options
- `theme.<slug>.customizer.manifest_categories.<slugified_category>` — schema category headings

`SettingControl` resolves **theme customizer keys first**, then `publishing.theme_customizer.items.*` for shared defaults.

Extensions should call `registerThemeLocales(i18n, slug, { en, id })` when activated.

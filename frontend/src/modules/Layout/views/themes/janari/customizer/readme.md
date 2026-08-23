# Janari / customizer extension

Theme-specific customizer data for **Janari** only. Loaded by the host via `resolveThemeCustomizerExtension('janari')`.

| File | Purpose |
|------|---------|
| `index.ts` | Exports `janariCustomizerExtension` |
| `bindings.registry.json` | Jejakawan binding components (hero, cta, partners, testimonials) |
| `sidebar.navigation.json` | Manifest categories reserved for host sidebar groups |
| `sidebar.pages.json` | Landing-page sidebar entries |
| `composables/filterJanariCustomizerSettings.ts` | Conditional field visibility (canvas / presets) |
| `composables/onJanariSettingChange.ts` | `color_preset` ↔ `color_primary` sync |
| `schema.settings.json` | 70 theme-scoped settings (`scope: "theme"`) |

After editing split schema files:

```bash
npm run theme:schema:merge
```

Re-classify from `theme.json` only when needed:

```bash
npm run theme:schema:split
```

See [naming-conventions](../../../customizer/naming-conventions.md).

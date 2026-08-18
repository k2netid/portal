# Layout / customizer (host)

Admin **theme customizer shell** for the Jejakawan hub. Theme-specific schema and rules live under `views/themes/<slug>/customizer/`.

See [naming-conventions.md](./naming-conventions.md) for full layout and naming rules.

## Quick reference

| Path | Role |
|------|------|
| `platform/schema/global.settings.schema.json` | Settings any theme should support (brand, layout, colors base) |
| `loaders/resolveThemeCustomizerExtension.ts` | Resolve `ThemeCustomizerExtension` by theme slug |
| `types/` | `ThemeCustomizerExtension`, `CustomizerSettingDefinition` |

Public theme runtime (Header, Home, …) is **not** here — see `views/themes/<slug>/`.

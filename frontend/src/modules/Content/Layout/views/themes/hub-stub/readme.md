# Hub Stub theme

Minimal theme package for **testing the platform customizer shell** without Janari-specific settings or bindings.

- Platform settings come from `Layout/customizer/platform/schema/global.settings.schema.json`
- No theme `settings_schema` keys (empty `customizer/schema.settings.json`)
- Register in `resolveThemeCustomizerExtension.ts` as `hub-stub`

Install/scan via Themes admin like any other `views/themes/<slug>/` package.

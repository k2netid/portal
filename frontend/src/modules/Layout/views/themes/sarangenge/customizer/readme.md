# Sarangenge Theme Customizer Extension

This directory contains theme-only customizer extensions for the **Sarangenge** school website theme.

## Architecture

| File | Purpose |
| --- | --- |
| `index.ts` | Exports `ThemeCustomizerExtension` implementation |
| `schema.settings.json` | Theme-scoped settings (School profile, PPDB, Hero, Bento, Programs, Facilities, Staff) |
| `bindings.registry.json` | Content data bindings for dynamic sections |
| `sidebar.navigation.json` | Manifest categories mapped to customizer sidebar |
| `sidebar.pages.json` | Special page customizer sidebar shortcuts |
| `preview.targets.json` | Live click-to-edit targets for visual inspection |
| `composables/` | Filter rules and setting change handlers |

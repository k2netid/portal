# Changelog — Layout (FE)

## [Unreleased]

- Empty pages stay empty in the builder; demo templates are library inserts only.
- Keyboard: Delete, Esc, Ctrl+D/C/V match Help. Site editor does not auto-open the first page.
- Canvas leaves render via public BlockRenderer; Layers Eye-off toggles sandboxed iframe preview. Save stamps `builder_schema_version`.
- Publishing overlay no longer double-saves; Create derives body from builder blocks.
- Public BlockRenderer + Janari Page body use SafeHtml (`publishing`).
- Janari SafeHtml uses `mode="publishing"` (legacy `Jejakawan` alias kept).

## 1.1.0 — P3-3b

- ~500 files: builder, themes, customizer, content-renderer.
- Publishing imports Builder/BlockRenderer from `@/modules/Layout`.

## 1.0.0 — P3-3a

- Console views for menus, widgets, and redirects.

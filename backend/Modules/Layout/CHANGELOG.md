# Changelog — Layout

## [Unreleased]

### Fixed
- `dynamicSources` no longer logs the full query string.

### Changed
- Manifest widget types match runtime (`content_list`, `menu`, `form`).
- Public menus/themes return empty when the layout pack is inactive.
- Widget types register even when an active theme supplies locations.
- Default frontend theme is **Janari** (builder/fork reference); Zenith remains optional alternate.

## 1.1.0 — P3-3b (themes + visual builder)

- Full Theme API, theme CLI commands, and Janari theme bundle.
- Visual Builder + BlockRenderer wired into Publishing create/edit.
- `lay_builder_presets` migration; builder dynamic-sources API.
- Console menu: Site Editor + Themes under Editorial.

## 1.0.0 — P3-3a (menus / widgets / redirects)

- Optional first-party pack extracted from ja-cms Content/Layout (menus-first slice).
- Console: Menus, Widgets (Editorial); Redirects (Infrastructure).
- Soft Theme model for menu usage only.

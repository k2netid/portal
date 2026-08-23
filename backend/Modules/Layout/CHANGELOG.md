# Changelog — Layout

## 1.0.0 — P3-3a (menus / widgets / redirects)

- Optional first-party pack extracted from ja-cms Content/Layout (menus-first slice).
- Console: Menus, Widgets (Editorial); Redirects (Infrastructure).
- Themes, Visual Builder, BlockRenderer deferred to P3-3b.
- Soft Theme model for menu usage only; `themes/active/locations` maps to LayoutRegistry defaults.
- Widget API accepts FE `title`/`content` aliases for DB `name`/`settings`.

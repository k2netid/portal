# Layout (P3-3a)

Optional first-party pack: **site menus**, **widgets**, and **URL redirects**.

Themes, Visual Builder, and BlockRenderer remain deferred to **P3-3b**. Publishing continues to use soft stubs for builder/renderer until then.

## Activate

1. Ensure `Layout: true` in `backend/modules_statuses.json` and composer PSR-4.
2. Activate `layout` in Module Registry (or seed Extension active).
3. Run migrations / re-login so new permissions appear on `/me`.

## Soft integrations

| Consumer | Behavior when Layout active |
|----------|----------------------------|
| Publishing Content sidebar | `/manage/layout/menus` live |
| Publishing `HandleRedirects` | Uses `Modules\Layout\Models\Redirect` |
| Menu usage analysis | Soft-reads `lay_themes.settings` if table exists |

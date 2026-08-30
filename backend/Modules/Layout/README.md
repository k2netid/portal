# Layout (P3-3a)

Optional first-party pack: **site menus**, **widgets**, **redirects**, **themes**, and **visual builder** (with block renderer for Publishing).

## Activate

1. Ensure `Layout: true` in `backend/modules_statuses.json` and composer PSR-4.
2. Activate `layout` in Module Registry (or seed Extension active).
3. Run migrations / re-login so new permissions appear on `/me`.

## Soft integrations

| Consumer | Behavior when Layout active |
|----------|----------------------------|
| Publishing Content sidebar | `/manage/layout/menus` live |
| Publishing create/edit | Visual Builder + BlockRenderer |
| Publishing `HandleRedirects` | Uses `Modules\Layout\Models\Redirect` |
| Menu usage analysis | Reads `lay_themes.settings` when present |

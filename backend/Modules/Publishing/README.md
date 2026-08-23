# Publishing (`publishing`)

Optional first-party CMS publishing pack extracted from `ja-cms` Content/Publishing.

**Depends on:** `library` (>=1.0.0) — activate Library first.

Contract: `docs/extensions/module-contract.md`

## Soft stubs (until later waves)

| Missing pack | Behavior |
|--------------|----------|
| Layout | Visual Builder → placeholder UI |
| Newsletter | Member newsletter endpoints → 503 |
| cms-ai | AI assist → stub service |

Media picker uses `@/modules/Media` when the Media pack is present (P3-2).

## Activate

1. Activate `library` then `publishing` in Module Registry (dependency order)
2. Permissions seed automatically on `extension_activated` for either slug
3. FE registers when `active_extensions` includes slug (reload after first activate)

Manual: `php artisan db:seed --class=Modules\\Publishing\\Database\\Seeders\\PublishingPermissionSeeder`

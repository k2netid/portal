# How-to: Sync RBAC & capabilities

Setelah menambah/mengubah capability di manifest modul atau mengubah default role, sinkronkan registry ke Spatie permissions.

## Prerequisites

- Working tree backend bisa boot (`cd backend && php artisan about` OK)
- Perubahan manifest / capability sudah tersimpan di disk

## Steps

1. Sync semua capability → permission:

```bash
cd backend
php artisan rbac:sync
```

2. Reset role standar ke factory default modul (hati-hati di lingkungan dengan custom role assignment):

```bash
php artisan rbac:sync --reset-defaults
```

3. Reset satu role saja:

```bash
php artisan rbac:sync --role=editor --reset-defaults
```

## Verify

- Console: permission baru muncul di UI RBAC / role matrix
- Route yang memakai middleware Spatie tidak 403 untuk role yang seharusnya punya akses

## Related

- Explanation / policy: [rbac-and-lifecycle-seeders.md](../extensions/rbac-and-lifecycle-seeders.md)
- Decisions: [ADR-020](../adr/ADR-020-rbac-hierarchy-route-hardening-and-ui-primitives.md), [ADR-021](../adr/ADR-021-capability-registry-auto-discovery-and-sso-scope-mapping.md)
- Reference CLI: [cli-commands.md](../reference/cli-commands.md)

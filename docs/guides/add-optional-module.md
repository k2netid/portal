# How-to: Add an optional first-party module

Scaffold pack opsional yang mengikuti kontrak Mail (golden sample).

## Prerequisites

- Baca [module-contract.md](../extensions/module-contract.md)
- Putuskan `slug` stabil (huruf kecil, tanpa spasi)

## Steps

1. Scaffold:

```bash
bash scripts/scaffold-optional-module.sh forms "Forms" "Dynamic forms and submissions"
```

Ganti argumen sesuai modul. Skrip membuat kerangka `backend/Modules/{Name}/` dan FE placeholder.

2. Lengkapi kontrak:

- `backend/Modules/<Name>/manifest.json` (sesuai [schema](../extensions/module-manifest.schema.json))
- `backend/Modules/<Name>/module.json` (nwidart)
- `frontend/src/modules/<Name>/module.ts` — `id` === manifest `slug`
- `README.md` + `CHANGELOG.md` di backend & frontend modul

3. Enable package boot di `backend/modules_statuses.json` (dan path scan di `config/modules.php` bila perlu).

4. Discover + activate di Module Registry (`sys_extensions`), atau lewat install profile yang relevan.

5. Gate API/FE:

- Backend: middleware `extension.active:<slug>` (+ permission)
- Frontend: `meta.extension` / nav `extension` + register module hanya jika slug ada di `active_extensions`

6. Sync RBAC jika menambah capability:

```bash
cd backend && php artisan rbac:sync
```

7. Quality gate:

```bash
npm run agent:verify
```

## Related

- Packaging eksternal / extract CMS: [external-module-packaging.md](../extensions/external-module-packaging.md)
- Downstream product module: [bootstrap-downstream-app.md](../product/bootstrap-downstream-app.md)
- Reference modul: [modules.md](../reference/modules.md)

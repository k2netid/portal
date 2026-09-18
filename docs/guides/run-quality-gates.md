# How-to: Run quality gates

Jalankan gate sebelum mengklaim task selesai atau membuka PR.

## Prerequisites

- Node **≥ 22.12** (script memakai `scripts/use-node22.sh`)
- Composer deps di `backend/` sudah terpasang
- Frontend deps: `npm --prefix frontend install` (sekali)

## Steps

1. Dari root repo `ja-core_engine`:

```bash
npm run agent:verify
```

Ini menjalankan `quality:frontend` lalu `quality:backend` (Pint `--test` + `composer run quality`).

2. Jika hanya menyentuh frontend dan butuh feedback cepat:

```bash
npm run agent:verify:quick
```

3. Opsional setelah ubah RBAC / tema / docs (bukan selalu bagian `agent:verify` historis — `docs:links` sekarang ikut `agent:verify`):

```bash
cd backend && php artisan rbac:sync
cd backend && php artisan theme:seed --all   # hanya jika menyentuh demo seeder tema
# dari root — cek link docs saja:
npm run docs:links
```

## Related

- Script root: `package.json` → `agent:verify`
- Pedoman agen: [`AGENTS.md`](../../AGENTS.md)
- RBAC sync detail: [sync-rbac-and-capabilities.md](sync-rbac-and-capabilities.md)

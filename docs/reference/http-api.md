# Reference: HTTP / OpenAPI API

Dua jalur dokumentasi API di Core Engine — jangan dicampur.

## 1. Console / route API (Scramble)

| Item | Nilai |
| :--- | :--- |
| Paket | `dedoc/scramble` |
| Config | `backend/config/scramble.php` |
| UI docs | `/docs/api` (middleware `EnsureApiDocsAccess` — selalu butuh login **admin+**, termasuk `APP_ENV=local`) |
| Export default | `api.json` (`export_path` di config) |
| Cakupan | Route di bawah path `api`, **kecuali** `api/v1/dynamic` (lihat `api_path.exclude` — dynamic CRUD pakai generator §2) |

**Cara pakai (dev):**

1. Boot backend (`php artisan serve` atau stack lokal).
2. Login console sebagai operator **admin** ke atas (`Gate::viewApiDocs`).
3. Buka `{APP_URL}/docs/api`.

Guest diarahkan ke `/auth/console-sign-in`. Role di bawah admin mendapat **403**. JSON di `/docs/api.json` juga di-gate (guest → 403).

Ini adalah **generator resmi** untuk permukaan HTTP Laravel yang di-anotasi / diinfer Scramble — bukan salinan manual di markdown.

## 2. Data Model Studio — dynamic OpenAPI

API runtime: `/api/v1/dynamic/{slug}` (gate `extension.active:data-studio`).

| Item | Nilai |
| :--- | :--- |
| Builder | `Modules\Core\System\Support\DynamicOpenApiBuilder` |
| HTTP index | `GET /api/v1/manage/infra/models/types/openapi-index` |
| HTTP per slug | `GET /api/v1/manage/infra/models/types/by-slug/{slug}/openapi` |
| CLI export | `php artisan dynamic:openapi [slug] [--output=dir]` |
| Output default CLI | `docs/api/dynamic-<slug>.openapi.json` |

## 3. Unified exporter (disarankan)

Satu pintu untuk kedua generator:

```bash
# dari root repo
npm run docs:openapi
# atau
bash scripts/export-openapi.sh
```

Menulis:

- `docs/api/console.openapi.json` — Scramble
- `docs/api/dynamic-*.openapi.json` — Data Model Studio
- `docs/api/INDEX.md` — indeks artifact

## 4. Smoke tanpa OpenAPI UI

```bash
bash scripts/api-smoke.sh
bash scripts/member-security-qa.sh   # member auth surface
```

## Related

- Data Studio vs CCK: [data-studio-vs-cck.md](../architecture/data-studio-vs-cck.md)
- ADR-012 (Data Model Studio modular)
- CLI index: [cli-commands.md](cli-commands.md)
- Security how-to: [security-checks.md](../guides/security-checks.md)

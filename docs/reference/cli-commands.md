# Reference: CLI & npm commands

Perintah yang sering dipakai di `ja-core_engine`. Jalankan dari lokasi yang disebutkan.

## Root repo (`ja-core_engine/`)

| Command | Fungsi |
| :--- | :--- |
| `npm run agent:verify` | Quality FE+BE + docs/SemVer (bukan PHPUnit penuh — lihat [testing.md](testing.md)) |
| `npm run agent:verify:quick` | Quality frontend saja |
| `npm run quality:frontend` | Lint/typecheck/i18n + Vitest + e2e:list |
| `npm run quality:backend` | Pint `--test` + `composer run quality` |
| `npm run build` | Build Vite frontend |
| `npm run sync` / `npm run deploy:assets:sync` | Sync asset FE → backend |
| `npm run test:backend` | Pest/PHPUnit via `php artisan test` |
| `npm run test:backend:coverage` | Backend coverage |
| `npm run test:e2e:smoke` | Smoke e2e FE |
| `npm run test:e2e:smoke:docker` | Smoke e2e di container Playwright |
| `npm run test:e2e:list` | Inventaris Playwright (no browser) |
| `npm run test:e2e:auth` | E2E auth subset |

## Backend (`cd backend`)

| Command | Fungsi |
| :--- | :--- |
| `php artisan rbac:sync` | Discover capability → Spatie permissions |
| `php artisan rbac:sync --reset-defaults` | Reset role standar ke default modul |
| `php artisan rbac:sync --role=editor --reset-defaults` | Reset satu role |
| `php artisan theme:seed` | Demo seeder tema aktif / default |
| `php artisan theme:seed <slug>` | Demo seeder satu tema |
| `php artisan theme:seed --all` | Semua tema resmi |
| `php artisan ja:apply-install-profile <core\|cms\|cms_site>` | Terapkan install profile |
| `php artisan dynamic:openapi [slug]` | Export OpenAPI 3 JSON dynamic models → `docs/api/` |
| `php artisan migrate:fresh --seed` | Reset DB + seed (hormati `INSTALL_PROFILE`) |
| `./vendor/bin/pint` | Format PHP |
| `./vendor/bin/pint --test` | Cek format tanpa tulis |

## Root — docs tooling

| Command | Fungsi |
| :--- | :--- |
| `npm run docs:links` | Cek relative link di `docs/` |
| `npm run docs:openapi` | Export Scramble + dynamic OpenAPI → `docs/api/` |
| `npm run docs:sync-downstream` | Mirror guides + reference + architecture + policy → downstream |
| `bash scripts/sync-docs-guides-reference-downstream.sh` | Sama dengan `docs:sync-downstream` |

## Scaffold scripts (root)

| Command | Fungsi |
| :--- | :--- |
| `bash scripts/scaffold-optional-module.sh …` | Pack first-party optional |
| `bash scripts/bootstrap-downstream-app.sh …` | Modul produk downstream |
| `bash scripts/api-smoke.sh` | Smoke API console |
| `bash scripts/member-security-qa.sh` | Smoke auth member |

## Related how-tos

- [run-quality-gates.md](../guides/run-quality-gates.md)
- [run-e2e-playwright.md](../guides/run-e2e-playwright.md)
- [testing.md](testing.md) — peta suite QA
- [sync-rbac-and-capabilities.md](../guides/sync-rbac-and-capabilities.md)
- [seed-theme-demo-data.md](../guides/seed-theme-demo-data.md)
- [apply-install-profile.md](../guides/apply-install-profile.md)
- [add-i18n-keys.md](../guides/add-i18n-keys.md)
- [security-checks.md](../guides/security-checks.md)
- [http-api.md](http-api.md)

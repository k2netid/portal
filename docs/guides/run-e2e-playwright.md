# How-to: Run Playwright E2E

E2E browser untuk console / public / member di **ja-core_engine**.  
Peta suite lengkap: [testing.md](../reference/testing.md).

## Prerequisites

- Node **≥ 22.12**, deps: `npm --prefix frontend install`
- Backend bisa boot (migrate/seed sesuai kebutuhan spec)
- Di host **PVE / kernel tanpa browser Playwright lokal**: pakai **Docker/Podman** (`scripts/e2e-docker.sh`) — jangan `npx playwright install` di host itu
- Captcha bypass (local/testing): header `X-E2E-Captcha-Bypass` (default token `local-e2e`; set `E2E_CAPTCHA_BYPASS_TOKEN` di `.env` bila diganti)

## Mode base URL

Config: [`frontend/playwright.config.ts`](../../frontend/playwright.config.ts)

| Mode | Env | `baseURL` default |
| :--- | :--- | :--- |
| Vite preview/dev proxy | (default) | `http://127.0.0.1:4173` |
| Full stack (CI-like) | `PLAYWRIGHT_USE_FULL_STACK=1` | `http://127.0.0.1:8081` |
| Custom | `PLAYWRIGHT_BASE_URL=…` | override |
| Auto webServer Vite | `PLAYWRIGHT_WEB_SERVER=1` (dan **bukan** full stack) | start `npm run dev` di :4173 |

API proxy saat webServer: `VITE_DEV_API_PROXY` / `E2E_API_PROXY_TARGET` (default `http://127.0.0.1:8081` di config Playwright; Vite dev default sering `:8000`).

## Steps — smoke cepat (disarankan)

### A. Docker / Podman (host tanpa browser)

1. Pastikan app + Vite reachable dari host (contoh `:8000` / `:5273` — sesuaikan `PLAYWRIGHT_BASE_URL`).
2. Dari root:

```bash
npm run test:e2e:smoke:docker
# atau perintah custom di dalam image:
bash scripts/e2e-docker.sh npm run test:e2e:auth
```

Script mem-pin image `mcr.microsoft.com/playwright:v{lockfile}-noble` (sama semangat dengan CI).

### B. Full stack lokal (mirip CI)

```bash
# stack di :8081 sesuai preflight CI / docker-compose e2e bila dipakai
PLAYWRIGHT_USE_FULL_STACK=1 npm --prefix frontend run test:e2e:ci
# alias:
npm --prefix frontend run test:e2e:smoke:full
```

CI job `e2e-smoke` menjalankan preflight lalu smoke di container Playwright.

### C. Hanya inventaris (tanpa browser)

```bash
npm run test:e2e:list
```

Sudah termasuk `quality:frontend` / `agent:verify`.

## Matrix script npm

Jalankan dari `frontend/` atau via root wrapper bila ada.

| Script | Spec / notes |
| :--- | :--- |
| `test:e2e:smoke` / `test:e2e:ci` | `auth-probe-notfound`, `login`, `onboarding-wizard` |
| `test:e2e:auth` | probe + login |
| `test:e2e:public` | `public-site-smoke`, `layung-theme-public` |
| `test:e2e:member` | `member-security` |
| `test:e2e:console` | appearance + shell/modal a11y + pagination mobile |
| `test:e2e:console-appearance` | appearance saja |
| `test:e2e:console-a11y` | shell a11y |
| `test:e2e:console-modal` | modal a11y |
| `test:e2e:console-pagination` | pagination mobile |
| `test:e2e:cms` | `cms-registry-publishing` |
| `test:e2e:staging` | public vs `PLAYWRIGHT_BASE_URL=http://127.0.0.1:8083` |
| `quality:frontend:gate` | `quality:frontend` + full-stack CI smoke |

Spec lain di `frontend/tests/e2e/` (mail, widgets, themes, extensions, …) jalankan eksplisit:

```bash
cd frontend && npx playwright test tests/e2e/mail-smoke.spec.ts
```

## Helpers & snapshots

- Helper bersama: `frontend/tests/e2e/helpers/`
- Snapshot visual: folder `*.spec.ts-snapshots/` di samping spec — update sadar (`--update-snapshots`) hanya bila perubahan UI disengaja

## Troubleshooting

| Gejala | Cek |
| :--- | :--- |
| Browser tidak jalan di host | Pakai `e2e-docker.sh` |
| 401 / captcha | `E2E_CAPTCHA_BYPASS_TOKEN` selaras `.env` + header config |
| API 404/CORS | `PLAYWRIGHT_BASE_URL`, proxy `VITE_DEV_API_PROXY`, Sanctum domains |
| Flaky timeout | Naikkan stabilitas stack; CI memakai `PLAYWRIGHT_WORKERS` (default 2 di CI) |
| `node_modules` symlink | `e2e-docker.sh` mount parent tree agar symlink resolve |

## Verify

- [ ] `npm run test:e2e:list` OK
- [ ] Smoke hijau (Docker atau full stack)
- [ ] Tidak commit captcha bypass token produksi

## Related

- Suite map: [testing.md](../reference/testing.md)
- Security smoke (non-browser): [security-checks.md](security-checks.md)
- Quality gates: [run-quality-gates.md](run-quality-gates.md)
- CLI: [cli-commands.md](../reference/cli-commands.md)

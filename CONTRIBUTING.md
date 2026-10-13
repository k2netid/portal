# Contributing — K2NET Portal (`k2net-portal`)

Kontribusi ke **downstream K2NET** (`k2netid/portal`). Kernel upstream: **`ja-core_engine`** — bug inti wajib dual-commit ke sana.

## Mulai di sini

1. [`AGENTS.md`](AGENTS.md) — identitas & batasan scope K2NET
2. [`docs/WORKFLOW.md`](docs/WORKFLOW.md) — **Audit → Diskusi → Rencana → Implement → Verify → Catat**
3. [`docs/DOCUMENTATION.md`](docs/DOCUMENTATION.md) — SoT / SoC / Diátaxis
4. [`docs/AGENT_START_HERE.md`](docs/AGENT_START_HERE.md) — konteks kernel (baca banner downstream)
5. Task M+: brief dari [`docs/templates/task-brief.md`](docs/templates/task-brief.md) → [`docs/work/`](docs/work/README.md)

## Definition of Done (ringkas)

- Scope sesuai rencana; SoC core vs tema vs tenant
- `npm run agent:verify` (termasuk `docs:links` + SemVer checks)
- BE runtime: juga `npm run test:backend` (atau CI) — lihat [reference/testing.md](docs/reference/testing.md)
- Changelog dual-layer: [guides/update-changelog.md](docs/guides/update-changelog.md)
- Release / SemVer: [guides/release-and-versioning.md](docs/guides/release-and-versioning.md) (`npm run versions:check`, `npm run modules:versions:check`)
- E2E UX kritis: [guides/run-e2e-playwright.md](docs/guides/run-e2e-playwright.md)
- Modul `README.md` / `CHANGELOG.md` tetap akurat
- Commit hanya jika diminta maintainer/user

## Branch & sync

- Trunk: **`main`**
- Kebijakan multi-repo: [`docs/branching.md`](docs/branching.md)
- Mirror guides/reference ke downstream: `npm run docs:sync-downstream` (k2net-portal, smkn6-portal, smkn1cijulang-portal, ja-cms)

## Jangan

- Data/brand klien di core (lihat `AGENTS.md`)
- Renumber ADR
- Melemahkan security gate “supaya lulus lokal” di production path

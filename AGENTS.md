# Panduan Agen — K2NET Portal

**Produk:** portal publik + member + CMS untuk **k2net.id** (fork dari ja-core_engine).

## Baca dulu (wajib)

1. **[`../docs/handoff/k2net-portal-agent.md`](../docs/handoff/k2net-portal-agent.md)** — workspace, build, deploy, larangan
2. [`../docs/handoff/agent-workspace.md`](../docs/handoff/agent-workspace.md) — ja-dev vs ja-srv
3. [`../docs/runbooks/k2net-portal.md`](../docs/runbooks/k2net-portal.md) — staging/prod, NPM, port

## Workspace Cursor

- **Host:** `ja-dev` (`10.20.0.207`)
- **Path:** `/home/jejakawan/dev/k2net-portal`
- **Bukan** `ja-srv` / `~/www/*` untuk coding

## Aturan singkat

1. **Staging origin = ja-dev** `:8083` (`backend/public`). Deploy: `bash scripts/deploy-staging-local.sh` — **jangan** rsync ke ja-srv.
2. NPM forward: `http://192.168.88.71:8083` (SoT: `../docs/configs/nginx-k2net-staging-jadev.conf`).
3. Frontend build: `NODE_OPTIONS=--max-old-space-size=4096 npx vite build` (satu project, tidak parallel).
4. Jangan `php artisan ja:install` di staging.
5. Commit ke repo **`k2netid/portal`**, bukan `ja-core_engine`.
6. Production `k2net.id` tetap di ja-srv `:8084`. Engine docs: [`docs/AGENT_START_HERE.md`](docs/AGENT_START_HERE.md).

## Verify

```bash
npm run agent:verify
```

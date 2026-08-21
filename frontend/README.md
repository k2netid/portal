# Jejakawan Core Engine (Frontend)

Vue 3.5 + Vite 8 Single Unified Console SPA untuk **Jejakawan Core Engine (`ja-core_engine`)**.

## Modul Domain

| Domain | Modul Sub-fitur |
| :--- | :--- |
| **System** | IAM, RBAC, Settings, Journals, Extensions, Languages |
| **Infra** | Data Studio, Tasks Scheduler, Backups, Redis Cache Explorer, Webhooks |
| **Security** | IP Firewall, 2FA, Passkeys WebAuthn, ABAC Policies, Security Logs |

## Shell Architecture

Single Unified Console SPA (`index.html` → `src/main.ts`) dengan rute autentikasi langsung dan zero-404 landing redirect.

## Development

```bash
npm install
npm run dev
```

## Quality Gates

```bash
npx vue-tsc -b       # TypeScript Typecheck
npm run test:unit    # Vitest Unit Tests
npm run build        # Production Build
```

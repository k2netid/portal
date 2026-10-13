# Billing (frontend)

Thin member-area UI for **K2NET self-billing** against `ja-cp` (control plane). Backend pack: `backend/Modules/Billing`.

## Surface

| Piece | Path |
| :--- | :--- |
| Member contribution | `memberArea.ts` — nav + route `member.billing` |
| View | `views/Billing.vue` — subscription, packages, checkout |
| API client | `services/billingService.ts` |

Registered from the public/member shell when the `billing` extension is product-active (depends on `member`).

## Env (backend)

Config lives in `backend/Modules/Billing/config/billing.php`:

- `JA_CP_URL` / `CONTROL_PLANE_URL`
- `JA_CP_API_KEY` / `CONTROL_PLANE_API_KEY`
- `JA_CP_WEBHOOK_SECRET`

## Docs

- Backend changelog: `backend/Modules/Billing/CHANGELOG.md`
- Product identity: [`docs/product/k2net-identity-and-theme-defaults.md`](../../../../docs/product/k2net-identity-and-theme-defaults.md)

# Billing (backend)

K2NET member self-billing and subscription lifecycle as a **client of `ja-cp`**. Not a source of truth for invoices — control plane is.

## Responsibilities

- Authenticated member API under `/api/v1/member/billing*`
- Public webhook `/api/v1/public/billing/webhook`
- `ControlPlaneBillingService` → JA-CP HTTP API

## Config

See `config/billing.php` (`JA_CP_*` / `CONTROL_PLANE_*` env keys).

## Related

- Manifest: `manifest.json` (`slug: billing`, depends on `member`)
- Frontend: `frontend/src/modules/Billing/`
- Tests: `tests/Feature/MemberBillingIntegrationTest.php`

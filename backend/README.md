# Jejakawan CMS (backend)

| | |
| :--- | :--- |
| **Produk** | Jejakawan CMS (`ja-cms`) |
| **Peran deploy** | Content Management System, Visual Builder & Publishing Hub |

Laravel API untuk **Jejakawan CMS** (satu database PostgreSQL).

Developed by **Jejakawan** ([jejakawan.com](https://jejakawan.com)) for **PT. Kirana Karina Network (K2NET)**.

## Stack

- Laravel 12, PHP ^8.2
- PostgreSQL (production) / SQLite (CI)
- Redis, Horizon, Reverb

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan test
```

## Platform API

- Manage: `GET/POST /api/v1/manage/platform/...` (auth + `manage settings`)
- Webhooks: `POST /api/v1/platform/webhooks/{provider}` — `midtrans`, `xendit`, atau `internal`
- License: `POST /api/v1/license/verify` — body: `domain`, `license_key`
- Public features: `GET /api/v1/public/subscription/features`

### Payment

Audit log: `platform_payment_webhook_deliveries`; admin `GET /api/v1/manage/platform/transactions/{id}/webhook-deliveries`; UI **Webhooks** di `/dash/platform`.

Checklist: [docs/operational/payment-go-live-checklist.md](../docs/operational/payment-go-live-checklist.md) · `php artisan platform:payment-env-check --profile=staging`

| Env | Purpose |
| :--- | :--- |
| `PLATFORM_PAYMENT_WEBHOOK_SKIP_VERIFY` | Skip signature (local only) |
| `PLATFORM_PAYMENT_INTERNAL_WEBHOOK_TOKEN` | Header untuk provider `internal` |
| `MIDTRANS_ENABLED` / `MIDTRANS_SERVER_KEY` | Verifikasi Midtrans |
| `XENDIT_ENABLED` / `XENDIT_CALLBACK_TOKEN` | Verifikasi Xendit |

Checkout: `POST /api/v1/manage/platform/transactions/{id}/checkout` — body opsional `billing_period` (`monthly`|`yearly`).

## Member API

Header **`X-Subscription-Domain`** (atau body `subscription_domain`) untuk baris **active** di `platform_subscriptions`.

- `GET /api/v1/public/member/registration-policy`
- `POST /api/v1/public/member/register`
- `POST /api/v1/public/member/login`
- Admin: `GET /api/v1/manage/platform/subscriptions/{id}/members`

Email yang sama boleh terdaftar di beberapa subscription (satu baris `mbr_members` per `subscription_id`).

## Quality

```bash
composer run quality          # Laravel Pint + PHPStan (level 9)
composer run phpstan:baseline # Regenerate phpstan-baseline.neon
```

## Docs

- [docs/architectural-status.md](../docs/architectural-status.md)
- [docs/architecture/hub-domain-and-module-tiers.md](../docs/architecture/hub-domain-and-module-tiers.md)
- [docs/operational/deploy-app-role.md](../docs/operational/deploy-app-role.md)

---
[Root README](../../README.md)

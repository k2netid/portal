# Forms (P3-4)

Optional first-party pack: **dynamic forms**, **submissions**, and **public embed API**.

Integrates with Layout builder form blocks (ContactForm, Newsletter, etc.) when both packs are active.

## Activate

1. `"Forms": true` in `backend/modules_statuses.json` + composer PSR-4.
2. Activate `forms` in Module Registry.
3. `php artisan migrate` + re-login for permissions.

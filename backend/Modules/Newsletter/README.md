# Newsletter (`newsletter`)

Optional first-party module — contract: `docs/extensions/module-contract.md`.

## Wire-up

1. PSR-4 in `backend/composer.json`: `"Modules\\Newsletter\\": "Modules/Newsletter/app/"`
2. `composer dump-autoload`
3. `backend/modules_statuses.json`: `"Newsletter": true`
4. Seed permission `use newsletter`
5. FE: add slug to `OPTIONAL_FIRST_PARTY` in `deferredConsoleModules.ts`
6. Module Registry → Activate

Packaging modes: `docs/extensions/external-module-packaging.md`.

# Identitas K2NET vs default engine di `theme.json`

## Keputusan (2026-10-13)

**Identitas brand / sosial / copy legal K2NET diterapkan lewat seeder & settings runtime**, bukan dengan menimpa default generik di `theme.json` first-party (yang ikut sync dari / ke `ja-core_engine`).

| Lapisan | SoT | Contoh |
| :--- | :--- | :--- |
| Engine theme defaults | `views/themes/*/theme.json` (+ schema platform) | Fallback `Jejakawan`, URL sosial demo `jejakawan` — aman untuk seed generik |
| Produk K2NET | `K2netDeploymentSeeder` + settings DB + tema `layung` aktif | `site_name=K2NET`, `company_legal_name=PT Kirana Karina Network`, `theme_active=layung` |
| Aset logo | `frontend/public/logofull_k2net.png` dll. | Di-set seeder / identity settings |

## Mengapa tidak rewrite `theme.json` defaults?

1. Merge `upstream/main` sering menyentuh schema/defaults tema — rewrite brand di sini = konflik berulang.
2. ADR-022: core/seed generik harus **zero client hardcoding**.
3. `npm run check:client-hardcoding` menjaga `theme-packs` / seeders generik tetap bersih.

## Yang boleh di repo ini

- Override di `backend/database/seeders/K2netDeploymentSeeder.php`.
- Copy/i18n khusus K2NET di pack yang memang produk (mis. Member billing strings).
- Docs & AGENTS yang menjelaskan default produk = **layung**.

## Yang ditunda

- Mengganti URL sosial default di semua `theme.json` menjadi akun K2NET (kecuali diputuskan eksplisit + dual-commit upstream bila perlu).
- Rename `composer.json` package `jejakawan/ja-core-engine`.

Staging/prod: setelah migrate/seed, jalankan `php artisan db:seed --class=K2netDeploymentSeeder` (lihat [`AGENTS.md`](../../AGENTS.md)).

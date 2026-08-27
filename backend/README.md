# Jejakawan Core Engine (Backend)

| | |
| :--- | :--- |
| **Produk** | Jejakawan Core Engine (`ja-core_engine`) |
| **Peran** | Modular Headless Kernel, Data Model Engine & Admin Console Backend |

Laravel API Kernel untuk **Jejakawan Core Engine** dengan schema PostgreSQL terisolasi.

## Stack

- Laravel 13, PHP ^8.2 (tested on 8.3)
- PostgreSQL (schema: `core_engine`) / SQLite (CI)
- Redis (dedicated cache & session database)

## Quick Start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan test
```

## Quality & Static Analysis

```bash
composer run quality          # Laravel Pint + PHPStan (level 9)
composer run phpstan:baseline # Regenerate phpstan-baseline.neon
```

## Dokumentasi
- [Dokumentasi Lengkap](../docs/README.md)
- [Ikhtisar Arsitektur](../docs/architecture/01-overview-and-tier-design.md)
- [Standar Backend](../docs/architecture/02-backend-standards.md)

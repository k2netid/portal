# 01. Overview & Architecture — Jejakawan Core Engine

## 🌟 1. Ikhtisar Sistem
**Jejakawan Core Engine (`ja-core_engine`)** adalah platform Headless Kernel dan Admin Console modern berbasis arsitektur **Modular Monolith** yang dirancang untuk performa tinggi, isolasi domain yang kokoh, serta keandalan tata kelola data & infrastruktur.

Sistem ini menggabungkan:
- **Backend**: Laravel **13** (PHP 8.2+, tested on 8.3) with domain modules under `Modules\Core\` and optional packs under `Modules/*`.
- **Frontend**: Vue 3.5 Single Unified Console SPA + TypeScript + Vite 8 + Tailwind CSS v4.
- **Database**: PostgreSQL 16+ (atau SQLite untuk isolated automated testing) dengan skema terisolasi `core_engine` serta Redis untuk caching, session, dan queuing.

---

## 🏛️ 2. Domain & Modul Core Engine

Arsitektur Core Engine berfokus pada fondasi kernel operasional:

```
                          ┌───────────────────────────┐
                          │   Jejakawan Core Engine   │
                          └─────────────┬─────────────┘
                                        │
         ┌──────────────────────────────┼──────────────────────────────┐
         │                              │                              │
         ▼                              ▼                              ▼
┌──────────────────┐          ┌───────────────────┐          ┌───────────────────┐
│      System      │          │       Infra       │          │      Security     │
├──────────────────┤          ├───────────────────┤          ├───────────────────┤
│ • User & IAM     │          │ • Data Studio     │          │ • RBAC + ABAC     │
│ • RBAC Matrix    │          │ • Task Crons      │          │ • 2FA & Passkeys  │
│ • Settings       │          │ • Backups         │          │ • Rate Limiting   │
│ • Plugins/Ext    │          │ • Redis Cache     │          │ • IP Lists        │
│ • Languages      │          │ • Webhooks        │          │ • Audit Journals  │
└──────────────────┘          └───────────────────┘          └───────────────────┘
```

### A. System Domain (`Modules/Core/app/System/`)
Menyediakan tata kelola sistem operasi aplikasi, otentikasi, dan konfigurasi global:
1. **IAM (Identity and Access Management)**:
   - User Accounts, Role Hierarchy (Spatie RBAC), Permission Registry, KYC Document Review Flow.
   - Dual Login support: Autentikasi fleksibel menggunakan Email atau Username.
2. **Configuration & Internationalization**:
   - Dynamic System Settings dengan cache memori otomatis.
   - Multi-bahasa terpadu (`id`, `en`, `su`) dengan modul lazy-loader.
3. **Extensions & Plugins**:
   - Extension Registry dengan AST Security Sandbox Scanner untuk memvalidasi paket pihak ketiga.
4. **Activity & Audit Logging**:
   - Full Activity Journaling yang mencatat seluruh lifecycle mutasi entitas dan aktivitas admin.

### B. Infrastructure & Data Studio (`Modules/Core/app/Infra/`)
Mengelola utilitas komputasi, storage, automasi, dan schema modeling:
1. **Data Model Studio**:
   - Desain data model dinamis (entities, custom fields, validation rules, OpenAPI schema generation).
   - Dynamic Record CRUD engine otomatis untuk model yang dibuat di runtime.
   - **Not** CMS CCK. Editorial fields stay in pack Library. See [data-studio-vs-cck.md](data-studio-vs-cck.md).
2. **Task Scheduler & Automation**:
   - Cron runner terpadu dengan reporting status eksekusi, preset runtime, dan scheduler logs.
3. **Backup & Snapshot Manager**:
   - Backup database terisolasi dan instant snapshot management.
4. **Redis Cache Engine**:
   - Key search, memory analytics, dan TTL inspection.
5. **Webhook Dispatcher**:
   - Outbound webhook delivery system dengan retry policy otomatis.

### C. Security Domain (`Modules/Core/app/Security/`)
Perlindungan perimeter, tata kelola akses, dan observabilitas keamanan:
1. **Access Governance**:
   - Attribute-Based Access Control (ABAC) Policy engine.
   - Two-Factor Authentication (TOTP 2FA) & WebAuthn / Passkeys.
2. **Perimeter Defense**:
   - IP Filtering (Allowlist & Blocklist) dengan progressive auto-block.
   - Dynamic Rate Limiting & Probe Path Sinkhole.
3. **Observability & SIEM**:
   - Security Audit Logs, SIEM JSON Exporter, dan CSP (Content Security Policy) report collector.

---

## 🔒 3. Prinsip Isolasi Data & Multi-Schema

Untuk menjaga kestabilan dalam integrasi multi-project:
- `ja-core_engine` menggunakan schema terisolasi: `core_engine` pada PostgreSQL (`DB_SCHEMA=core_engine`).
- Database Redis menggunakan dedicated database index (`REDIS_DB=6`, `REDIS_CACHE_DB=7`) dengan prefix unik `core_engine_cache:`.
- Hal ini mencegah tabrakan data dan cache dengan instance CMS maupun Control Plane lainnya pada host yang sama.

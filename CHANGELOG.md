# Changelog

All notable changes to **ja-control-plane** (Jejakawan hub, `APP_ROLE=ops`).

## [1.0.0] — (unreleased)

Target: GA setelah gate production (payment live, pilot hosted).

### Added

- **Security Hardening & Mitigations**: Keamanan tingkat tinggi dengan mitigasi 19 celah keamanan (HMAC-SHA256 untuk token verifikasi bot, sliding window rate limit, strict CSP nonce-based, sanitasi log frontend, proteksi luapan halaman `per_page` cap, dan regenerasi baseline audit integritas sistem).
- **Optimasi Indeks Database**: Penambahan **15 indeks performa baru** di tabel-tabel modul inti (Operational, Billing, Forms, Publishing, Library, Member, AI & Analytics) untuk memotong *sequential scan* kueri.
- **Smart Settings Caching**: Caching memori permanen (`Cache::rememberForever`) pada kueri settings global guna mengurangi beban kueri database publik ke 0 kueri, lengkap dengan invalidasi otomatis via Eloquent boot observers (`saved`, `deleted`).
- **Pencegahan Kueri N+1 Sistematis**: Penegakan `Model::preventLazyLoading` secara global pada non-produksi untuk menangkap loop kueri tidak efisien sejak fase lokal/testing.
- **Optimasi PGSQL Maintenance**: Rutin pemeliharaan native `VACUUM ANALYZE` untuk PostgreSQL pada `SysMaintenanceService`.
- **CI/CD Caching**: Penambahan Actions caching Composer dependencies pada alur integrasi `.github/workflows/ci.yml`.
- **Dokumentasi Audit**: Laporan audit keamanan (`security_audit_report.md`), walkthrough detail performa (`walkthrough.md`), dan checklist tugas (`task.md`) dipusatkan langsung di `docs/`.
- **Platform** (`platform_*`): packages, subscriptions, transactions, reconciliation; webhooks Midtrans/Xendit/internal
- **Member** portal: `api/v1/public/member/*` dengan header **`X-Subscription-Domain`**; model `subscription_id` + `ScopedBySubscription`
- Middleware **`IdentifyMemberSubscription`**, **`HubSubscriptionScope`**; quota storage via `SubscriptionStorageQuotaService`
- Deploy role: **`ops`** mengizinkan member API; path konsol **`/dash/platform`** (redirect dari `/dash/saas`)
- Public: `api/v1/public/subscription/features`, `api/v1/license/verify` (instansi **ja-platform**)
- Scheduled content publish cron; CI dengan composer/npm audit
- Features JSON v2 mapping (Operational) + unit tests (CR-0.1, CR-1.2)
- **Frontend — modul terpisah**: `Core/Security` (Security Journal, alerts, store) dan `Intelligence/Analytics` (dashboard analitik) diekstrak dari `Core/System` / `Intelligence/Search`; router, navigasi, dan locale bundle per modul
- **Frontend — tema di Layout**: seluruh kode tema (Janari, customizer, bindings, manifest) dipindah dari `Content/Publishing` ke `Content/Layout`; route konsol `/dash/themes` dan `/dash/themes/:slug/customizer` di modul Layout (selaras API `/manage/layout/themes/*`)
- **Janari theme**: struktur `pages/`, `components/`, `customizer/`, locale per-tema (`themeLocales`), composable `useLocalizedThemeSetting` / `usePublicPageContent` / `useThemeI18n`; halaman Pricing terhubung katalog platform
- **Public API**: `GET api/v1/public/platform/catalog` (`PlatformCatalogController`) + feature test; locale-aware konten publik + `PublicContentLocaleTest`
- **Dokumentasi refactor FE**: `docs/frontend-architecture-refactor-2026-05-22/` (plan, tasks, walkthrough)

### Changed

- **Hub single-DB** — satu PostgreSQL (`ja_control_plane`); identitas hosted = `platform_subscriptions`, bukan partisi CMS `organization_id` / `workspace_id`
- Auth users: tabel **`srv_auth_users`**; validasi `Rule::unique` / `exists` pada model `User`
- CMS migrasi & model tanpa kolom tenant/workspace; Intelligence quota mengacu subscription hub
- FE: modul Operational **Platform** + **Member**; konsol platform & member auth stores
- PHPUnit baseline: **430** passed (`php artisan test`) — lihat [docs/architectural-status.md](docs/architectural-status.md)
- Docs/README diselaraskan ke arsitektur hub (bukan multi-tenant SaaS DB)
- **Frontend arsitektur**: impor tema `@/modules/Content/Publishing/...` → `@/modules/Content/Layout/...`; Security Journal & Analytics keluar dari `Core/System`; language switcher Janari memakai `DropdownMenu` + `useLanguage` (desktop & mobile)
- **Janari theme**: migrasi ikon ke `lucide-vue-next`; CSS tema dipusatkan di modul Layout (hapus `frontend/css/themes/janari.css` terpisah); skema customizer Janari dipecah/digabung via skrip maintainer

### Removed

- Modul **`SaaS`** (`saas_*` routes, migrasi, seeders, tests, middleware `ResolveLocalHostedTenant`, `IdentifyMemberTenant`)
- Provisioning multi-DB tenant, `WorkspaceStorageQuotaService`, `AiWorkspaceQuotaService`
- API & UI legacy `/dash/saas`, tenant feature gate berbasis DB terpisah
- Entri navigasi & route tema dari modul **Publishing** (diganti Layout)
- Blok locale `security_alerts` di `Core/System/locales` (dipindah ke `Core/Security/locales`)

### Fixed

- Build SPA: restore **`ContentPreviewModal.vue`** (masih direferensikan `Publishing/contents/Edit.vue` setelah refactor)
- Build: hapus dynamic import tidak efektif `api/client` di `main.ts` (warning Rolldown `INEFFECTIVE_DYNAMIC_IMPORT`)
- Toggle bahasa inline `ID | EN` di header Janari yang tidak merespons klik

### Breaking

- Klien yang mengandalkan **`saas_*`** API atau header tenant lama harus migrasi ke **platform** + **`X-Subscription-Domain`**
- Greenfield DB: `php artisan migrate:fresh --seed` — tidak ada rollback ke skema `saas_*`
- Path konsol: **`/dash/saas`** → **`/dash/platform`** (redirect FE tetap ada sementara)
- Impor/extend tema: gunakan `Content/Layout` (bukan `Content/Publishing`); Security & Analytics punya path modul baru (`Core/Security`, `Intelligence/Analytics`)

## [1.0.0-beta.1]

Engineering foundation R2026.1–R2026.24 (baseline sebelum konsolidasi hub di atas).

### Added

- Modular monolith (Core, Content, Intelligence, Operational)
- SPA console + public Janari theme; Sanctum session auth
- Installer script & dokumentasi operasional

# Changelog

Semua perubahan penting pada **Jejakawan Core Engine (`ja-core_engine`)**.

> Fork dari **`ja-cms`** → master kernel untuk aplikasi downstream. Branch `develop` (line CMS) dihapus Aug 2026; Content/member/themes = downstream, bukan scope `main`.

Format: [Keep a Changelog](https://keepachangelog.com/id/1.0.0/) · [Semantic Versioning](https://semver.org/spec/v2.0.0.html)

---

## [Unreleased]

### Added
- JA-Mail: honest AI gating vs Settings → AI; Core `AI_DISABLED` on generate; `mail-ai-governance` rule
- JA-Mail: in-app notification bridge (send failure / vacation); per-module README + CHANGELOG; agent rule `module-documentation.mdc`
- `docs/product/bootstrap-downstream-app.md` + `scripts/bootstrap-downstream-app.sh` — scaffold modul produk downstream.
- `Modules/Core/app/System/Services/Ai/AiHttpResponse.php` — typed HTTP helpers untuk provider AI.

### Changed
- JA-Mail: IMAP/SPF/DKIM documented as mail-server/DNS (out of kernel backlog)
- Identitas produk diselaraskan ke **Core Engine** (docs, UI, artisan, OAuth copy).
- PHPStan: perbaiki 138 error di luar baseline; baseline diregenerasi (~85 entri FileManager).
- E2E: hanya smoke kernel (auth, onboarding, console a11y); hapus theme/content/member specs.
- Router guards & error pages: hapus sisa `member-*` routes.
- CI: trigger hanya `main`; hapus payment-env-check & security audit commands orphan.

### Removed
- Branch **`develop`** (remote + local) — line CMS tidak lagi di repo ini.
- E2E legacy: `theme-*.spec.ts`, `member-register`, `member-a11y`, `console-content-studio`.
- Frontend scripts: `test:e2e:theme`, `build:theme:janari`, Janari theme schema merge/split.

---

## [1.0.0-beta.2] — 2026-08-19

Rilis stabilisasi kernel pasca-fork: i18n 3 bahasa, performance settings cache, dan pembersihan DNA control-plane/CRM dari tree.

### Added
- **Isolated i18n architecture** untuk modul berat (builder pattern — kini downstream CMS).
- **Paritas i18n `id` / `en` / `su`** dengan validator otomatis (`i18n-check-keys.mjs`).
- **Dialog z-index layering** — modal konfirmasi di atas fullscreen overlay.
- **Settings caching** — `Cache::rememberForever` + observer invalidation; indeks DB performa; `VACUUM ANALYZE` di maintenance.

### Changed
- **Identitas repo** disepakati sebagai fondasi modular (transisi ke `ja-core_engine`).
- **Module governance** disederhanakan ke tier Core (System, Infra, Security).
- Environment template: database schema `core_engine` (bukan `ja_cms_db`).

### Removed
- Sisa **control-plane operational** dari kernel: SME/Aksara/Exambro service keys, `Modules/Operational` scan, accounting/platform scheduled tasks, CRM/accounting frontend paths & E2E billing specs.

---

## [1.0.0-beta.1] — 2026-05-25

Baseline **Modular Monolith kernel** (upstream: ja-cms monolith).

### Added — Core tier (tetap di `main`)
- **System:** IAM (RBAC Spatie, ABAC, Passkeys, 2FA), settings, extensions marketplace, languages.
- **Infra:** Data Model Studio, backups, webhooks, Redis explorer, scheduled tasks, file manager.
- **Security:** IP management, rate limiting, audit/activity/system journals, CSP/SIEM hooks.
- **Frontend:** Vue 3 unified console SPA, engine router, Pinia, i18n.

### Added — Content/Intelligence tier *(dipindah ke downstream / ex-develop)*
- Publishing, Layout/Builder, Media, Forms, Library, AI, Analytics, Newsletter — **tidak lagi bagian `main`**.

---

## Catatan fork (Aug 2026)

| Keputusan | Detail |
|---|---|
| **`main`** | Canonical **Core Engine** kernel |
| **`develop`** | **Dihapus** — CMS line hidup di repo produk terpisah |
| **JA-CP** | Hub lisensi eksternal; bukan identitas engine |
| **Downstream** | Fork `main` + `bootstrap-downstream-app.sh` + modul produk |

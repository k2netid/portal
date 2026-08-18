# Changelog

Semua perubahan penting pada **Jejakawan CMS (`ja-cms`)**.

Format changelog ini mengacu pada [Keep a Changelog](https://keepachangelog.com/id/1.0.0/) dan mematuhi [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0-beta.2] — 2026-08-19

Rilis penyempurnaan menyeluruh: isolasi modul visual builder, pembersihan total artefak kontrol-plane, peningkatan arsitektur i18n 3 bahasa, dan stabilisasi UI/UX.

### Added
- **JA-Builder Isolated Localization Architecture**:
  - Struktur modul translasi mandiri di `frontend/src/modules/Content/Layout/locales/builder/` (`en.json`, `id.json`, `su.json`, `index.ts`).
  - Kamus builder lengkap (>350 entri) mencakup seluruh kanvas, sidebar, toolbar, popover variabel global, menu konteks, dan modal penyisipan blok.
  - Integrasi dynamic loader builder di `engine/i18n/loaders/content.ts` dan validator kunci di `scripts/i18n-check-keys.mjs`.
- **Dukungan Penuh 3 Bahasa Simetris**: Paritas 100% tanpa kunci hilang pada seluruh modul (English, Bahasa Indonesia, dan Basa Sunda).
- **Z-Index Layering Fix pada Dialog Global**:
  - `DialogOverlay` ditingkatkan ke `z-[100050]` dan `DialogContent` ke `z-[100060]` untuk mencegah tertutupnya modal konfirmasi saat builder dalam mode fullscreen atau modal overlay.
  - Dukungan prop `overlayClass` pada komponen `DialogContent.vue`.
- **Smart Settings Caching & Database Performance**:
  - Memory caching permanen (`Cache::rememberForever`) pada kueri settings global dengan auto-invalidation via Eloquent boot observers (`saved`, `deleted`).
  - Indeks komposit performa pada tabel `srv_auth_users`, `pub_contents`, `lay_themes`, `forms`, dan `med_media`.
  - Rutin pemeliharaan native `VACUUM ANALYZE` PostgreSQL pada `SysMaintenanceService`.

### Changed
- **Penyelarasan Identitas Proyek ke `ja-cms`**:
  - Memperbarui seluruh konfigurasi, metadata (`package.json`), dan dokumentasi arsitektur (`frontend/README.md`, `backend/README.md`) ke repositori mandiri **Jejakawan CMS (`ja-cms`)**.
  - Standardisasi konfigurasi environment (`.env.example`, `backend/.env.example`) untuk penggunaan CMS murni (`ja_cms_db`).
- **Page Settings Panel Refactoring**:
  - Migrasi seluruh kunci template di `PageSettingsPanel.vue` dari prefix legacy `features.content.*` / `features.menus.*` ke `builder.panels.pageSettings.*`.
  - Penambahan tipe parameter eksplisit `(media: { url?: string } | string | null)` pada event handler `MediaPicker`.
- **Module Governance**:
  - Memperbarui daftar modul terkelola di `ModuleAccessController` ke CMS: `['publishing', 'layout', 'library', 'forms', 'media', 'intelligence']`.

### Removed
- **Pembersihan Total Sisa Control-Plane**:
  - **Backend**: Menghapus konfigurasi `sme`, `aksara`, dan `exambro` dari `config/services.php`; menghapus `Modules/Operational/*` dari scan paths `config/modules.php`; menghapus scheduled tasks `accounting:*` dan `platform:*` dari `routes/console.php` dan `ScheduledTask.php`.
  - **Database & Permissions**: Menghapus permission `view/manage crm`, `accounting`, role `finance`, dan membersihkan permission `view crm` dari `security-officer` di `FoundationSeeder.php` serta database live.
  - **Frontend**: Menghapus komponen `FormCrmLeadSettings.vue` dan membersihkan form `Create.vue` / `Edit.vue`; menghapus filter tag `crm` di modul Library; menghapus export `crmPaths`, `accountingPaths`, dan `memberPaths` dari `paths.ts`.
  - **Build & Tests**: Menghapus manual chunks `mod-crm` dan `mod-accounting` dari `vite.config.ts`; menghapus seluruh spec file dan snapshot E2E CRM / Billing dari `tests/e2e/`; menghapus file `.cursorignore` usang.

---

## [1.0.0-beta.1] — 2026-05-25

Baseline fondasi arsitektur Modular Monolith Jejakawan CMS.

### Added
- **Core Tier**: Sistem IAM (RBAC Spatie, ABAC Policies, Passkeys WebAuthn, 2FA TOTP), Infrastructure (Backups, URL Redirects, Scheduled Tasks, Webhooks, CCK Content Types), dan Security (IP Management, Rate Limiting, Audit Activity Logs).
- **Content Tier**:
  - **Publishing**: Manajemen konten, kategori hierarkis, sistem taksonomi, editorial workflow, revisi konten, dan sistem komentar anti-spam.
  - **Layout**: JA-Builder Visual Editor, manajemen tema (Janari Theme), customizer, blok tata letak, dan navigasi menu hierarkis.
  - **Media**: Pengelola file & folder, upload chunked, konversi WebP otomatis, sanitasi SVG, dan image thumbnail generator.
  - **Forms**: Visual form builder, validasi dinamis, reCAPTCHA v3 / Cloudflare Turnstile, dan submission viewer.
  - **Library**: Tagging system, custom field manager, dan komponen template reusable.
- **Intelligence Tier**:
  - **AI Integration**: Multi-provider LLM (DeepSeek, OpenAI, Google Gemini) untuk content drafting, SEO metadata generation, dan taxonomy suggestions.
  - **Unified Search**: Indeks pencarian terpusat dengan auto-sync listener event.
  - **Analytics**: Privacy-friendly visitor & pageview analytics.
  - **Newsletter**: Manajemen subscriber dan email blast.
- **Frontend Architecture**: Vue 3 SPA + Vite + Tailwind CSS + Lucide Icons + Vue I18n.
- **Production Verification**: 446 test suite PHPUnit backend dan 182 unit test Vitest frontend.

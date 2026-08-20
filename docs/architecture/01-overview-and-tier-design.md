# 01. Overview & Tier Architecture — Jejakawan CMS

## 🌟 1. Ikhtisar Sistem
**Jejakawan CMS (`ja-cms`)** adalah platform Content Management System modern berbasis arsitektur **Modular Monolith** yang dirancang untuk performa tinggi, keamanan kelas enterprise, dan fleksibilitas penerbitan konten.

Sistem ini menggabungkan:
- **Backend**: Laravel 12 (PHP 8.3+) dengan arsitektur domain modular di bawah namespace `Modules\`.
- **Frontend**: Vue 3 SPA + TypeScript + Vite 8 + Tailwind CSS.
- **Database**: Single-Database PostgreSQL 16+ (atau SQLite untuk testing) dengan Redis untuk caching, session, dan queuing.

---

## 🏛️ 2. Pembagian Tier & Modul

Arsitektur dibagi menjadi 3 Tier hierarkis yang independen dan saling melengkapi:

```
                          ┌───────────────────────────┐
                          │       Jejakawan CMS       │
                          └─────────────┬─────────────┘
                                        │
         ┌──────────────────────────────┼──────────────────────────────┐
         │                              │                              │
         ▼                              ▼                              ▼
┌──────────────────┐          ┌───────────────────┐          ┌───────────────────┐
│     Core Tier    │          │    Content Tier   │          │ Intelligence Tier │
├──────────────────┤          ├───────────────────┤          ├───────────────────┤
│ • System         │          │ • Publishing      │          │ • AI Integration  │
│ • Infra          │          │ • Layout (Builder)│          │ • Unified Search  │
│ • Security       │          │ • Forms           │          │ • Analytics       │
│                  │          │ • Media           │          │ • Newsletter      │
│                  │          │ • Library         │          │                   │
└──────────────────┘          └───────────────────┘          └───────────────────┘
```

### A. Core Tier (`Modules/Core/`)
Menyediakan fondasi sistem operasi CMS, otentikasi, tata kelola keamanan, dan utilitas infrastruktur:
1. **System**:
   - IAM (Identity and Access Management): User, Role (Spatie RBAC), Permission Registry, KYC Profile.
   - Dynamic System Settings dengan cache memori otomatis (`Cache::rememberForever`).
   - Extension & Plugin system sandbox.
   - Activity log & System health metrics.
2. **Infra**:
   - Scheduled task orchestrator & automation runner.
   - Data Model Studio & Dynamic REST Entities.
   - URL Redirects manager & Automated Database Backup system.
   - Outbound webhooks delivery & audit.
3. **Security**:
   - Attribute-Based Access Control (ABAC) Policy engine.
   - Two-Factor Authentication (TOTP 2FA) & WebAuthn / Passkeys.
   - IP Firewall (Allowlist/Blocklist) & Dynamic Rate Limiter.
   - SIEM log exporter & Content Security Policy (CSP) engine.

### B. Content Tier (`Modules/Content/`)
Jantung dari kemampuan manajemen konten dan visual design CMS:
1. **Publishing**:
   - Konten artikel, berita, dan halaman kustom.
   - Kategori hierarkis, taksonomi tag, dan metadata SEO.
   - Editorial workflow (Draft, Scheduled, Published, Archived), versioning & revisi riwayat.
   - Sistem komentar interaktif dengan spam protection filter.
2. **Layout & JA-Builder**:
   - **JA-Builder**: Visual Canvas Builder dengan drag-and-drop, responsive preview (Desktop, Tablet, Mobile), layer tree, property panels, popover variabel global, dan isolated i18n localization.
   - **Theme Engine**: Sistem tema mandiri (misalnya **Janari Theme**) dengan slot dinamis, schema customizer, dan live preview.
   - **Navigation & Menus**: Pengelola menu hierarkis multi-lokasi.
3. **Media**:
   - Media storage explorer, folder hierarki, pencarian aset.
   - Otomasi konversi WebP, thumbnail generator multi-resolusi, dan sanitasi file SVG.
4. **Forms**:
   - Visual drag-and-drop form builder.
   - Validasi field dinamis, integrasi bot protection (reCAPTCHA v3 / Cloudflare Turnstile).
   - Pengelola respons form, ekspor CSV/Excel, dan analitik submisi.
5. **Library**:
   - Global reusable template blocks & snippet library.
   - Custom field definition system.

### C. Intelligence Tier (`Modules/Intelligence/`)
Menyediakan kecerdasan buatan, analitik, dan kapabilitas pencarian:
1. **AI Integration**:
   - Multi-provider gateway (DeepSeek, OpenAI, Google Gemini).
   - Asisten penulisan artikel (Content Drafting), SEO auto-generation, dan AI Taxonomy suggestions.
   - AI usage analytics & token rate monitor.
2. **Search**:
   - Unified Search Indexer yang mengindeks artikel, halaman, media, dan kategori secara terpusat melalui asynchronous database triggers / listeners.
   - Loose & strict matching, search auto-suggestions, and health indexing scanner.
3. **Analytics**:
   - Privacy-friendly visitor tracking tanpa cookie pihak ketiga.
   - Statistik kunjungan real-time, browser, perangkat, negara, dan top landing pages.
4. **Newsletter**:
   - Subscriber management, audience segmentation, dan broadcast campaign builder.

---

## 🔒 3. Prinsip Komunikasi Antar-Modul

1. **Explicit API Boundaries**:
   - Antar modul backend tidak boleh mengakses tabel modul lain secara *raw DB query* langsung di luar model resmi.
   - Gunakan Model Relations, Service Classes, atau Laravel Event/Listeners (`Event::dispatch`).
2. **Database Isolation**:
   - Setiap modul memiliki prefix tabel yang konsisten:
     - Core: `srv_*` (e.g. `srv_auth_users`, `srv_system_settings`)
     - Content: `pub_*` (e.g. `pub_contents`), `lay_*` (e.g. `lay_themes`), `med_*` (e.g. `med_media`), `forms_*`
     - Intelligence: `int_*` (e.g. `int_search_index`, `int_analytics_visits`)
3. **Lifecycle Events**:
   - Perubahan data (seperti `ContentPublished`, `CategoryDeleted`) mentrigger event yang secara otomatis ditangkap oleh modul Intelligence (`UnifiedSearchIndexer`) tanpa kopling ketat (*loose coupling*).

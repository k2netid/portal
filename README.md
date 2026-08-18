# Jejakawan CMS (`ja-cms`)

<p align="center">
  <img src="https://jejakawan.com/favicon.ico" alt="Jejakawan Logo" width="64" height="64" />
</p>

<p align="center">
  <strong>Modern, Ultra-Fast, Modular Monolith Content Management System & Visual Site Engine</strong>
</p>

<p align="center">
  <a href="https://jejakawan.com">Website Resmi</a> •
  <a href="#-fitur-utama">Fitur Utama</a> •
  <a href="#-arsitektur-sistem">Arsitektur</a> •
  <a href="#-prasyarat-sistem">Prasyarat</a> •
  <a href="#-instalasi--menjalankan">Instalasi</a> •
  <a href="#-quality-gates--pengujian">Pengujian</a>
</p>

---

## 📌 Ringkasan Sistem

| Komponen | Spesifikasi |
| :--- | :--- |
| **Produk** | **Jejakawan CMS** — [jejakawan.com](https://jejakawan.com) |
| **Repositori** | `ja-cms` (`ja-cmspro`) |
| **Arsitektur** | Modular Monolith (3 Macro-Tiers) + Dual SPA + Visual Site Editor |
| **Versi** | `1.0.0-beta.1` |
| **Database Engine** | PostgreSQL 18.x (Native AI & Vector Ready, JSONB Indexing) |
| **Runtime Backend** | PHP 8.3+ (Laravel 13 Monolith Framework) |
| **Runtime Frontend** | Node.js 26.x (Vue 3.5, Vite 8, Tailwind CSS v4, TypeScript) |
| **Cache & Queue** | Redis (phpredis driver, dedicated cache/session DB) |
| **Lisensi** | Proprietary / MIT |

---

## 🌟 Fitur Utama

### 1. 🏗️ Arsitektur 3 Macro-Tier Domain
Aplikasi dibangun di atas arsitektur modular monolith yang rapi, terisolasi, dan mudah dipelihara:
- **`Core` Tier:**
  - **System:** User Management, RBAC (Role-Based Access Control) + ABAC Interceptor, Personal Access Tokens, Extension Engine, System Settings.
  - **Security:** Autentikasi 2FA, WebAuthn/Passkeys, CSP Header Security, Activity Audit Trails, Security Logs, Brute Force & Rate Limit Protection.
  - **Infra:** Task Scheduler, Automated Backups, Webhook Dispatcher & Delivery Retries, URL Redirects, CCK & Dynamic Entities.
- **`Content` Tier:**
  - **Publishing:** Post, Page, Templates, Kategori Hierarkis, Tagging, Komentar Anti-Spam, Revision History, SEO Meta & OpenGraph.
  - **Layout & Visual Site Editor:** 50+ Blok Builder Visual, Drag & Drop responsive designer, Preset Management, Dynamic Tag Resolver, Theme Engine (Janari & Custom Themes), Menu Management, Widget Locations.
  - **Media Library:** Media Storage terisolasi, Image Optimizer, WebP Converter, Folder Hierarchy, Storage Quota Manager.
  - **Forms & Reach:** Form Builder dinamis, Submission Collector, Email Template Manager.
  - **Library:** Custom Field Definitions, Taxonomy Search Port, Extension Plugins.
- **`Intelligence` Tier:**
  - **AI Studio:** Integrasi multi-provider (OpenAI, DeepSeek, Gemini), Automated Content Drafting, AI Taxonomy Suggestion, Batch Processing.
  - **Unified Search Indexer:** Real-time search engine synchronizer dengan kalkulasi skor relevansi otomatis.
  - **Analytics & Pulse:** Privacy-friendly real-time traffic statistics, top pages, referrer sources, device breakdowns, and event tracking.
  - **Newsletter & Audience:** Subscriber Management, Campaign Tracker, and Audience Segments.

### 2. 🎨 Visual Site Editor & Theme Engine
- **50+ Blok Builder:** Hero, Grid, Features, Testimonial, Pricing, FAQ, Accordion, Contact, Gallery, Media Player, dsb.
- **Dynamic Content Binding:** Blok dapat dihubungkan ke sumber data dinamis (Postingan, Kategori, Custom Fields, System Settings).
- **Responsive Preview:** Desain presisi pixel-perfect untuk Desktop, Tablet, dan Mobile secara instan.
- **Dynamic Theme Resolver:** Mendukung pemilihan tema publik (Janari, E2E Demo, Hub Stub) secara dinamis tanpa perlu redeploy kode.

### 3. ⚡ High-Performance Dual-SPA Frontend
- **Dual Entrypoint:** Memisahkan bundle SPA Publik (`index.html`) dan SPA Admin Console (`console.html`) untuk ukuran bundle yang sangat kecil dan load-time instan.
- **Deferred Locales & Dynamic Imports:** Bahasa (Indonesia, English, Sunda) dan modul hanya dimuat saat rute terkait diakses (*lazy-loaded*).

---

## 🛠️ Arsitektur Direktori

```
ja-cms/
├── backend/                    # Laravel 13 Modular Monolith
│   ├── app/
│   │   ├── Http/Controllers/   # SpaController, Installer API
│   │   └── Models/
│   ├── Modules/
│   │   ├── Core/               # System, Security, Infra
│   │   ├── Content/            # Publishing, Layout/Builder, Media, Forms, Library
│   │   └── Intelligence/       # AI, Analytics, Newsletter, Search
│   ├── database/               # Migrations & Seeders
│   └── routes/                 # Web & API Route Registrars
├── frontend/                   # Vue 3.5 + Vite 8 SPA
│   ├── src/
│   │   ├── engine/             # Router, State Store, API Clients, I18n
│   │   ├── modules/            # Domain Components (Core, Content, Intelligence)
│   │   │   └── Content/Layout/ # Visual Site Editor & Themes
│   │   └── shared/             # UI Components, Composables, Types, Utils
│   └── tests/                  # Vitest Component & Unit Tests
└── scripts/                    # Build, Deployment & Quality Gate Utilities
```

---

## 📋 Prasyarat Sistem

Sebelum menjalankan aplikasi, pastikan server atau lingkungan lokal Anda telah terpasang:
- **PHP:** `8.3` atau lebih baru (dengan ekstensi `pdo_pgsql`, `pgsql`, `redis`, `gd`, `intl`, `mbstring`, `zip`, `xml`)
- **Node.js:** `26.x` atau lebih baru (`npm 11+`)
- **Database:** PostgreSQL `18.x`
- **In-Memory Cache:** Redis `7.x`

---

## 🚀 Instalasi & Menjalankan

### 1. Clone & Konfigurasi Backend
```bash
cd backend
composer install --no-interaction

# Siapkan file environment
cp .env.example .env
php artisan key:generate

# Sesuaikan koneksi PostgreSQL & Redis pada file .env:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=ja_cms
# DB_USERNAME=ja_cms
# DB_PASSWORD=your_secure_password
# APP_INSTALLED=true

# Jalankan migrasi dan seeding database
php artisan migrate:fresh --seed
php artisan storage:link
```

### 2. Setup & Jalankan Frontend (Development)
```bash
cd ../frontend
npm install
npm run dev
```

### 3. Build Aset Produksi
```bash
cd frontend
# Build aset teroptimasi ke dist/ dan sinkronkan ke backend/public/
npm run build
npm run deploy:assets:full
```

---

## 🧪 Quality Gates & Pengujian

Aplikasi dilengkapi dengan suite pengujian otomatis yang komprehensif:

```bash
# 1. Jalankan Unit & Feature Test Backend (PHPUnit)
cd backend
php artisan test

# 2. Jalankan Pengujian Frontend (Vitest)
cd ../frontend
npm run test:unit

# 3. Jalankan Audit Keamanan (0 Vulnerability Target)
cd ../backend && composer audit
cd ../frontend && npm audit
```

---

## 🔒 Keamanan & Kebijakan

- **CSP (Content Security Policy):** Dilengkapi nonce generator dan proteksi strict origin.
- **Sinkhole Reconnaissance:** Mengisolasi dan memblokir upaya probing umum (`/wp-admin`, `/phpmyadmin`, dsb.).
- **Data Protection:** Sanitasi input SVG, escaping HTML parser, dan hashing kata sandi tingkat tinggi (Bcrypt cost 12 / Argon2id).

---

<p align="center">
  Dikembangkan dengan standar rekayasa perangkat lunak modern oleh <strong>Jejakawan Team</strong>.
</p>

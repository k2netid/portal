# Jejakawan Core Engine (`ja-core_engine`)

<p align="center">
  <strong>High-Performance Headless Kernel, Data Model Engine & Admin Console Platform</strong>
</p>

<p align="center">
  <a href="#-ringkasan-sistem">Ringkasan</a> •
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
| **Produk** | **Jejakawan Core Engine** |
| **Repositori** | `ja-core_engine` |
| **Arsitektur** | Modular Monolith Kernel + Single Unified Console SPA |
| **Versi** | `1.0.0-beta.1` |
| **Database Engine** | PostgreSQL 16+ / 18.x (Schema terisolasi `core_engine`) |
| **Runtime Backend** | PHP 8.3+ (Laravel 12/13 Modular Kernel) |
| **Runtime Frontend** | Node.js 20+ / 22+ (Vue 3.5, Vite 8, Tailwind CSS v4, TypeScript) |
| **Cache & Queue** | Redis (Dedicated session & cache DB) |
| **Lisensi** | MIT / Proprietary |

---

## 🌟 Fitur Utama

### 1. 🏗️ Domain Modul Core Kernel
- **System Platform:**
  - Manajemen Akun, Profil, dan Autentikasi Modern (Email / Username, 2FA Google Authenticator, WebAuthn Passkeys).
  - RBAC (Role-Based Access Control) granular + ABAC Policy Interceptor.
  - Multi-language support (Bahasa Indonesia, English, Basa Sunda) dengan dynamic runtime switcher.
  - Extension & Plugin Marketplace Engine dengan AST Security Scanner.
- **Data Studio & Infrastructure (`Infra`):**
  - **Data Model Studio:** Pembuatan entity schema kustom, field types, validasi, dan dynamic relations.
  - **Dynamic Entity Records:** Instant CRUD API & UI untuk semua data model yang didefinisikan secara runtime.
  - **Automated Crons & Task Scheduler:** Pemantauan scheduled tasks real-time dan log eksekusi.
  - **Backup Manager:** Backup database terisolasi dan recovery snapshot.
  - **Redis Cache Explorer:** Key search, TTL management, dan real-time memory monitor.
  - **Webhook Engine:** Multi-event dispatching dengan automatic exponential backoff retry.
- **Security & Observability:**
  - **Activity Journals:** Full audit trail untuk mutasi entitas, event lifecycle, dan administrative actions.
  - **System Journals:** Error & exception logging real-time dengan log streaming.
  - **Security Logs:** Deteksi brute-force, IP rate-limiting, IP blocking progressive, dan probe sinkhole.
  - **KYC & Identity Verification:** Document review flow terstruktur untuk compliance.

### 2. ⚡ Single Unified Console SPA
- **Clean Architecture:** Menggunakan satu entrypoint `index.html` dan router terpadu tanpa dual-shell overhead.
- **Instant Auth Routing:** Akses root `/` langsung mengarahkan ke form `/login` untuk guest dan `/ja-dash/dashboard` untuk authenticated admin.
- **Zero 404 Onboarding:** Alur login langsung mendukung username `super` maupun email `super@jejakawan.com`.

---

## 🛠️ Struktur Direktori

```
ja-core_engine/
├── backend/                    # Laravel Modular Kernel
│   ├── app/
│   │   ├── Http/Controllers/   # SpaController, Install API
│   │   └── Models/
│   ├── Modules/
│   │   └── Core/               # System, Security, Infra
│   ├── database/               # Core Migrations & Seeders
│   └── routes/                 # Kernel Web & API Route Registrars
├── frontend/                   # Vue 3.5 + Vite 8 Console SPA
│   ├── src/
│   │   ├── main.ts             # Single SPA Entry Point
│   │   ├── engine/             # Router, State Store, API Clients, I18n
│   │   ├── modules/
│   │   │   └── Core/           # System, Infra, Security Views & Stores
│   │   └── shared/             # UI Components, Composables, Utils
│   └── tests/                  # Vitest Component & Unit Tests
└── docs/                       # Dokumentasi Arsitektur & Standar Teknis
```

---

## 🚀 Panduan Menjalankan Dev Server

### 1. Backend (Laravel)
```bash
cd backend
composer install
php artisan migrate --force
php artisan db:seed --force
php artisan serve --host=0.0.0.0 --port=8000
```

### 2. Frontend (Vite)
```bash
cd frontend
npm install
npm run dev
```

Akses browser: **`http://localhost:5173/`** atau **`http://192.168.88.66:5173/`**

**Default Kredensial Super Admin:**
- **Username / Email:** `super` / `super@jejakawan.com`
- **Password:** `password`

---

## 🧪 Quality Gates & Pengujian

```bash
# Pengujian Backend (Pest / PHPUnit)
cd backend && php artisan test

# Pemeriksaan TypeScript Frontend
cd frontend && npx vue-tsc -b

# Pengujian Unit Frontend (Vitest)
cd frontend && npm run test:unit

# Production Build Test
cd frontend && npm run build
```

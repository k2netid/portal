# Jejakawan CMS (`ja-cms`)

| | |
| :--- | :--- |
| **Produk / Merek** | **Jejakawan CMS** — [jejakawan.com](https://jejakawan.com) |
| **Repo** | `ja-cms` (`ja-cmspro`) |
| **Arsitektur** | Modular Monolith + Dual SPA + Visual Site Editor |
| **Versi** | `1.0.0-beta.1` |
| **Tema Publik** | Janari / Dynamic Theme Engine |

---

## 🌟 Fitur Utama
1. **Macro-Tier Domain Monolith:**
   - **`Core`**: System, Security (2FA, WebAuthn/Passkeys, Activity & Security Journals), Infra (Tasks, Backups, Webhooks, Redirects).
   - **`Content`**: Publishing (Posts, Pages, Templates, Categories, Tags, Comments), Media Library & File Manager, Appearance & Layout (Visual Site Editor, Themes, Menus, Widgets), Forms & Reach (Submissions, Analytics, Email Templates, SEO Tools), Library (Custom Fields, Plugins).
   - **`Intelligence`**: Analytics & Pulse Insights, Newsletter & Audience, Search Engine Index.
2. **Visual Site Editor & Builder:**
   - 50+ block definitions, responsive visual designer, dynamic tag resolver, and preset management.
3. **Frontend Engine:**
   - Unified Module Registry, deferred locale loaders, Pinia stores, and Tailwind CSS design system.

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.2+ (with Redis extension)
- Node.js 22.12+ (`.nvmrc`)
- MySQL 8.0+ / MariaDB
- Redis

### Setup & Run
```bash
# Backend setup
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed

# Frontend setup & dev
cd ../frontend
npm install
npm run dev
```

### Quality Gate
```bash
# Run full verification (PHPUnit + Vitest + Linting)
npm run agent:verify
```

# ADR-002: Penyelarasan Panel Halaman Customizer & Pemisahan Tegas Site Branding vs Core Branding

**Status:** Accepted  
**Tanggal:** 2026-09-02  
**Author:** Jejakawan Engineering  
**Scope:** `frontend/src/modules/Layout/customizer/`, `frontend/src/modules/Publishing/locales/`, `frontend/src/modules/Layout/views/themes/layung/`

---

## Konteks

Sebelum refaktor ini, arsitektur Theme Customizer memiliki beberapa kelemahan struktural dan terminologi:

1. **Monolithic Page Dump**: Semua halaman publik (About, Services, Solusi, Pricing, Achievements, Tim, News, Contact) ditumpuk menjadi satu kategori raksasa `Public Pages` di dalam panel customizer, membuat navigasi editor penuh sesak dan sulit dikelola secara modular.
2. **Ambiguity Branding Naming**: Item identitas di dalam Theme Customizer diberi nama `"Core Branding"` / `"Branding Inti"`. Hal ini menimbulkan kebingungan arsitektural karena istilah *Core Branding* sesungguhnya mengacu pada System/Console Identity di menu **Pengaturan (Settings) > Identitas (Identity)** untuk konsol internal, bukan untuk situs tema publik.
3. **Legacy Dead Code**: Terdapat item `page-careers` / `enable_career` lama yang sudah tidak digunakan lagi di tema Layung.
4. **Mix of Languages**: Terjemahan properti halaman di customizer belum sepenuhnya simetris antara ID, EN, dan SU.

---

## Keputusan

### 1. Isolasi Halaman Per Panel (Page Isolation)
Setiap halaman publik di tema Layung didefinisikan secara modular di [sidebar.pages.json](../../frontend/src/modules/Layout/customizer/platform/sidebar.pages.json) dengan `manifestCategories` masing-masing:
- `About Page` → `identity-page-about`
- `Services Page` → `identity-page-services`
- `Solusi Page` → `identity-page-solusi`
- `Pricing Page` → `identity-page-pricing`
- `Achievements Page` → `identity-page-achievements`
- `Tim Page` → `identity-page-tim`
- `News Page` → `identity-page-news`
- `Contact Page` → `identity-page-contact`

Setiap komponen halaman Layung dihubungkan dengan atribut bridge:
`data-ja-customizer-target="<page-slug>"`

### 2. Pemisahan Tegas Definisi Branding (SoC)
- **System / Core Branding** (Domain: `Modules/Core/System`):
  - Mengatur branding kernel/konsol internal: nama aplikasi sistem, logo auth console login, favicon console, dan token tema konsol.
  - Berada di: **Pengaturan > Identitas**.
- **Site Branding** (Domain: `Modules/Layout / Publishing`):
  - Mengatur branding website publik tema: logo situs publik, nama situs publik, tagline, dan favicon website.
  - Berada di: **Theme Customizer > Brand & Navigasi > Branding Situs (Site Branding)**.

Pembaruan terjemahan i18n:
- **EN**: `"general": "Site Branding"`, `"general_desc": "Public site logos and identity."`
- **ID**: `"general": "Branding Situs"`, `"general_desc": "Logo dan identitas situs publik."`
- **SU**: `"general": "Branding Loka"`, `"general_desc": "Logo dasar sareng nami loka publik."`

### 3. Cleanup Legacy Settings
- Menghapus item `page-careers` dan key `enable_career` dari skema customizer.

---

## File yang Diubah

| File | Perubahan |
|------|-----------|
| `customizer/platform/sidebar.pages.json` | Pemecahan manifestCategories per halaman terisolasi & cleanup career |
| `Publishing/locales/en.json` | Update `Site Branding` + paritas i18n halaman |
| `Publishing/locales/id.json` | Update `Branding Situs` + paritas i18n halaman |
| `Publishing/locales/su.json` | Update `Branding Loka` + paritas i18n halaman |
| `themes/layung/pages/*.vue` | Penambahan atribut `data-ja-customizer-target` pada semua halaman Layung |

---

## Konsekuensi

### Positif
- Admin/Pengembang dapat langsung mengedit halaman spesifik tanpa terganggu oleh pengaturan halaman lain.
- Definisi branding sistem dan branding situs publik terpisah secara tegas tanpa ambiguitas.
- Kerapian i18n 100% simetris di 3 bahasa resmi (EN, ID, SU).

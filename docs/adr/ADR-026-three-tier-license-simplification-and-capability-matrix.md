---
status: accepted
scope: core
date: 2026-09-19
related:
  - ADR-011
  - ADR-012
  - ADR-014
  - ADR-017
  - ADR-021
  - ADR-023
---

# ADR-026: Standarisasi Lisensi 3-Tier (Community, Pro, Enterprise) dan Penegakan Matriks Kapabilitas

**Status:** Accepted  
**Tanggal:** 2026-09-19  
**Author:** Jejakawan Core Engineering  
**Scope:** `LicenseService`, `ExtensionHealthService`, Module Registry (`sys_extensions`), Console App Store, Theme System, downstream school portals (`smkn6-portal`, `smkn1cijulang-portal`), corporate/ISP portals (`ja-cms`, `k2net-portal`)  
**Related:** ADR-011 (Visual Builder), ADR-012 (Data Model Studio), ADR-014 (3-Tier Identity & White Label), ADR-017 (Console Appearance), ADR-021 (Capability Registry), ADR-023 (Theme Quotas)  

---

## 1. Konteks & Permasalahan

Sebelumnya, sistem lisensi Jejakawan Core Engine memiliki fragmentasi tingkatan (*tiers*):
- `community`: Gratis / open core.
- `starter`: Tier transisi minor (hanya membedakan `custom_css`).
- `pro`: Tier komersial institusi / sekolah / UKM.
- `enterprise`: Tier korporasi & multi-tenant.
- `white_label`: Tier reseller / MSP.
- Serta varian `pro_plus` pada internal rank `ExtensionHealthService`.

### Permasalahan Kritis:
1. **Lumping `$isPaid` yang Terlalu Luas**:
   Pada `LicenseService::getFeaturesMatrix()`, evaluasi kapabilitas dilakukan dengan:
   ```php
   $isPaid = in_array($tier, [self::TIER_PRO, self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true);
   ```
   Hal ini menyebabkan **tier `PRO` secara keliru memperoleh kapabilitas yang sama persis dengan `ENTERPRISE`**, kecuali `white_label` dan `multi_site`.
2. **Eksposur Fitur Developer Tingkat Tinggi ke Sekolah**:
   Portal sekolah (seperti SMKN 6 Bandung dan SMKN 1 Cijulang) yang berlisensi `pro` mendapatkan akses aktif ke **Data Model Studio** (`data-studio`). Administrator sekolah (guru TIK/staf administrasi) dapat membuat tabel database dinamis dan menghasilkan endpoint instant REST API (`/api/v1/dynamic/*`). Ini membingungkan persona pengguna institusi sekolah dan berisiko merusak integritas skema data.
3. **Risiko Keamanan Eksekusi Kode Jarak Jauh (Remote Code Execution / RCE)**:
   Tier `pro` mengizinkan upload paket ZIP mentah untuk tema (`theme_upload`) dan plugin (`plugin_upload`). Jika kredensial admin sekolah disusupi (phishing/weak password), penyerang dapat mengunggah file ZIP yang berisi webshell PHP berbahaya.
4. **Fragmentasi Nilai Bisnis**:
   Perbedaan antara `community` dan `starter` sangat tipis, sedangkan `white_label` secara esensial adalah hak kustomisasi branding di level enterprise, bukan tier platform yang terpisah secara fundamental.

---

## 2. Keputusan Arsitektur

### 2.1 Model Kanonikal 3-Tier (Community – Pro – Enterprise)

Sistem distandarisasi secara tegas menjadi **3 Tingkatan Lisensi**:

1. **`community` (Free Open Core)**:
   - Evaluasi, pengembang lokal, dan komunitas open-source.
   - Tema `janari` (always-on, 0 kuota premium).
   - Core CMS modules (Publishing, Media, basic Forms, Member, Layout, Library).
   - Editor Tiptap standar, branding resmi Jejakawan tetap terpasang (*watermark intact*).
2. **`pro` (Institutional & SME Commercial Production)**:
   - Sekolah (SMKN 6, SMKN 1 Cijulang), perguruan tinggi, UKM, dan institusi mandiri.
   - **1 Tema Pilihan dari Katalog Resmi** (misal: `sarangenge`).
   - **Visual Page & Site Builder** (`visual-builder`) dengan multi-device preview scaling.
   - **Bebas Watermark**: Menghapus footer branding Jejakawan pada situs publik.
   - **Injeksi Kode Kustom**: Header/footer snippets untuk analitik sekolah (GA4, GTM, Meta Pixel, WhatsApp widget).
   - **Official Curated Plugins**: Mengaktifkan modul resmi terkurasi (Social Dock, Cinematic Nav, Instagram Feed).
   - **CMS AI Assistant**: Kuota generasi konten AI standar.
   - 🛑 **Safety-by-Design**: Data Model Studio, ZIP Upload/Export, dan White Label Konsol dikunci.
3. **`enterprise` (Corporate, Multi-Tenant & Platform Partner)**:
   - Yayasan pendidikan multi-sekolah, ISP/MSP (K2Net), korporasi besar, dan tim engineer.
   - **Unlimited Catalog Themes**: Bebas mengaktifkan dan beralih di antara semua tema katalog first-party.
   - **Data Model Studio (`data-studio`)**: Pemodelan entitas dinamis, custom fields, relational validation, dan instant REST API.
   - **Upload & Export Paket ZIP**: `theme_upload`, `plugin_upload`, `theme_export`, `plugin_export`.
   - **White Label Konsol (`white_label`)**: Kustomisasi logo konsol (`app_logo_*`, `brand_logo`), nama aplikasi (`app_name`), dan login shell.
   - **Multi-Site Fleet Management (`multi_site`)**: Sentralisasi puluhan domain portal di bawah satu konsol kontrol.

### 2.2 Konsolidasi Tier Legacy
- `starter`: Diperlakukan sebagai alias transisi dari `community` (graceful fallback).
- `white_label`: Diperlakukan sebagai kapabilitas branding tingkat lanjut di dalam tier `enterprise` (misal via flag lisensi JA-CP `enterprise:white_label` atau key format `JACP-WL-*`).

### 2.3 Penataan Matriks Kapabilitas Resmi (`LicenseService.php`)

```php
$isProOrHigher = in_array($tier, [self::TIER_PRO, self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true);
$isEnterprise = in_array($tier, [self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true);

return [
    'custom_css'            => $isProOrHigher,
    'premium_themes'        => $isProOrHigher, // deprecated: use theme_quota
    'theme_quota'           => $this->getThemeQuota($tier),
    'visual_builder'        => $isProOrHigher,
    'pro_builder_modules'   => $isProOrHigher,
    'custom_code_injection' => $isProOrHigher,
    'remove_watermark'      => $isProOrHigher,
    'priority_updates'      => $isProOrHigher,
    'data_studio'           => $isEnterprise, // Strictly Enterprise
    'theme_upload'          => $isEnterprise, // Strictly Enterprise (Anti-RCE)
    'plugin_upload'         => $isEnterprise, // Strictly Enterprise (Anti-RCE)
    'theme_export'          => $isEnterprise, // Strictly Enterprise (Portability)
    'plugin_export'         => $isEnterprise, // Strictly Enterprise (Portability)
    'white_label'           => $isEnterprise,
    'multi_site'            => $isEnterprise,
];
```

### 2.4 Penataan Ulang Manifest Ekstensi Data Model Studio
Berkas `backend/extensions/data-studio/manifest.json`:
- `"license": "Commercial ENTERPRISE"`
- `"license_tier": "enterprise"`

### 2.5 Hirarki Rank pada `ExtensionHealthService.php`

```php
private const PACK_RANK = [
    'free' => 0,
    'community' => 0,
    'pro' => 1,
    'pro_plus' => 2,
    'enterprise' => 3,
];

private const SITE_RANK = [
    LicenseService::TIER_COMMUNITY => 0,
    LicenseService::TIER_STARTER => 0,
    LicenseService::TIER_PRO => 1,
    LicenseService::TIER_ENTERPRISE => 3,
    LicenseService::TIER_WHITE_LABEL => 3,
];
```

Dengan konfigurasi di atas:
- Ekstensi `data-studio` (rank 3) otomatis terblokir pada portal sekolah dengan lisensi `pro` (rank 1), menghasilkan pesan:
  `"Lisensi situs (pro) tidak mencukupi untuk paket 'data-studio' (butuh enterprise)."`
- Ekstensi `visual-builder` (rank 1) tetap dapat diaktifkan dan berstatus sehat pada tier `pro`.

---

## 3. Konsekuensi & Manfaat

### Manfaat Positif:
1. **Keamanan Maksimal bagi Sekolah**:
   Menghilangkan risiko RCE akibat upload file zip sembarang pada tier Pro sekolah.
2. **Pengurangan Beban Kognitif Pengguna**:
   Antarmuka konsol sekolah bersih dari menu database developer tingkat lanjut (Data Model Studio), sehingga operator sekolah fokus pada publikasi artikel, agenda, dan halaman sekolah via Visual Builder.
3. **Diferensiasi Komersial yang Tajam**:
   Tier Enterprise memiliki *selling point* teknis yang sangat bernilai: Headless API Generator, Dynamic Schema Modeler, White Label, dan Multi-Site.
4. **Zero Data Loss**:
   Skema tabel dinamis yang sudah pernah dibuat oleh instalasi tidak dihapus; middleware hanya memblokir akses ke rute studio dan dynamic endpoints selama lisensi belum ditingkatkan ke Enterprise.

---

## 4. Rencana Transisi & Propagasi

1. Implementasi upstream `ja-core_engine` pada `LicenseService`, `ExtensionHealthService`, `data-studio/manifest.json`, dan test suite.
2. Propagasi ke seluruh repositori downstream:
   - `smkn6-portal` & `smkn1cijulang-portal` (Pro).
   - `ja-cms` & `k2net-portal` (Enterprise).
3. Sinkronisasi dokumentasi multi-host `docs/` ke `ja-srv` dan `ja-aksara`.

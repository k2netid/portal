# ADR-002: Pemisahan Identitas Aplikasi (APP_NAME) dan Identitas Situs (SITE_NAME)

**Status:** Accepted — *Fallbacks generalized by [ADR-005](./ADR-005-complete-brand-generalization-smkn6-and-k2net.md)*  
**Tanggal:** 2026-09-04  
**Author:** Jejakawan  
**Scope:** `backend/.env`, `FoundationSeeder.php`, `LicenseService.php`, `system.ts`, `PublicSettingsController.php`, `GeneralTab.vue`, `SettingField.vue`, `LicenseTab.vue`

---

## Konteks

Saat proses adaptasi JA Core Engine untuk proyek sekolah pertama (SMKN 6 Bandung), ditemukan **pencampuran identitas (*identity bleeding*)** antara dua konsep yang seharusnya terpisah:

1. **`APP_NAME` / `app_name`** — Identitas **pengembang/engine** (brand "Jejakawan" / "JA Core Engine"). Digunakan di halaman login console, session cookie, "Powered by", email sender name, dan elemen UI admin.

2. **`site_name`** — Identitas **pemilik situs** (contoh: "SMKN 6 Bandung"). Digunakan di halaman publik, SEO, email template, tema, dan frontend publik.

### Masalah Spesifik

- `.env` `APP_NAME` diubah menjadi `"SMKN 6 Bandung"` padahal seharusnya tetap `"Jejakawan"`.
- `FoundationSeeder` menggunakan `config('app.name')` (= `APP_NAME`) untuk mengisi **kedua** setting `app_name` dan `site_name`, menyebabkan keduanya memiliki nilai yang sama.
- Session cookie otomatis bernama `smkn-6-bandung-session` karena mengikuti `Str::slug(APP_NAME)`, padahal seharusnya `jejakawan-session`.
- Fallback default di `FoundationSeeder` berubah ke `'Portal SMKN 6 Bandung'`, bukan `'Jejakawan'`.

---

## Keputusan

### 1. APP_NAME Tetap "Jejakawan" (Brand Pengembang)

`APP_NAME` di `.env` harus **selalu** bernilai `"Jejakawan"` kecuali pemilik situs memiliki lisensi **Enterprise** atau **White Label** (`JACP-WL-*` / `JACP-ENT-*`) yang mengizinkan rebrand.

Mekanisme proteksi ditegakkan di:
- `LicenseService::isProtectedKey()` — key `app_name`, `app_logo`, `brand_logo`, `app_favicon`, `brand_favicon`, `branding_display`, `app_identity`, `powered_by_link`, `admin_footer_text` termasuk *protected keys*.
- `LicenseService::hasWhiteLabel()` — hanya tier `enterprise` dan `white_label` yang boleh mengubah *protected keys*.
- `SettingController::bulkUpdate()` — men-skip perubahan *protected keys* jika lisensi tidak memenuhi syarat.
- `GeneralTab.vue` & `SettingField.vue` — field terkunci (`disabled` + icon gembok 🔒) dan memunculkan badge *White Label Required* jika lisensi tidak memenuhi syarat.

### 2. SITE_NAME Terpisah via Env Var Baru

Ditambahkan dua variabel lingkungan baru di `.env`:

```env
SITE_NAME="SMKN 6 Bandung"
SITE_DESCRIPTION="Portal Resmi SMK Negeri 6 Bandung — Sekolah Menengah Kejuruan Pusat Keunggulan"
```

`FoundationSeeder` dimodifikasi agar:
- `app_name` → selalu dari `config('app.name')` yang merujuk ke `APP_NAME` = `"Jejakawan"`.
- `site_name` → dari `env('SITE_NAME', $appName)`, sehingga jika `SITE_NAME` tidak diset, fallback ke `APP_NAME`.
- `site_description` → dari `env('SITE_DESCRIPTION', '')`.

### 3. Fallback Default Dikembalikan ke "Jejakawan"

Semua fallback di `FoundationSeeder.php` dikembalikan ke `'Jejakawan'` agar engine tetap netral dan tidak membawa hardcode institusi manapun.

### 4. Adopsi Tier Enterprise (Lifetime / Perpetual) untuk Deployment SMKN 6

Untuk deployment showcase pertama (SMKN 6 Bandung) dengan lisensi Lifetime/Perpetual:
- `license_type` diset ke **`enterprise`** di seeder dan database.
- Memberikan instansi sekolah seluruh kapabilitas tier tertinggi (modul builder pro, watermark removal, multi-site fleet, dan white-label console).
- Default `app_name` tetap bernilai **`Jejakawan`** (brand pengembang) namun memberikan fleksibilitas kustomisasi console penuh bagi instansi.

### 5. Resolusi dan Sinkronisasi Logo Publik (Theme Header, Navbar, dan Footer)

Logo yang diunggah oleh pengelola sekolah melalui menu **Console > Settings > General (`site_logo`)** harus secara otomatis tersinkronisasi dan tampil pada seluruh komponen publik (Header, Navbar, Footer, serta portal login member) tanpa memerlukan duplikasi konfigurasi manual.

Hirarki resolusi logo di frontend (`useSarangengeIdentity`):
1. **Theme Customizer Override**: `getSetting('brand_logo')` / `getSetting('site_logo')` (jika tema secara spesifik meng-override logo).
2. **Global Site Settings**: `systemStore.siteSettings.site_logo` / `systemStore.settings.site_logo` (nilai dari DB `general.site_logo`, contoh: `/storage/media/kVofJqAjgPI3HVcpYvoFHySz6xMdDsKRNXyy6O9d.webp`).
3. **App Identity Logo (Fallback)**: `systemStore.appIdentity.app_logo` (brand pengembang/engine).
4. **Initial Letter Badge (Fallback Terakhir)**: Huruf inisial nama instansi berdesain badge (contoh: huruf `"S"` untuk SMKN 6 Bandung).

Pengaturan visibilitas (`branding_display`) untuk tema institusi pendidikan (Sarangenge) diset default ke **`both`** (Logo + Nama Instansi) agar logo resmi dan akreditasi/NPSN tampil berdampingan secara proporsional.

---

## Peta Identitas (Identity Map)

| Setting Key | Group | Sumber | Contoh Nilai | Proteksi Lisensi |
| :--- | :--- | :--- | :--- | :--- |
| `app_name` | `system` | `APP_NAME` env | `Jejakawan` | ✅ Enterprise / White Label |
| `app_logo` | `brand` | DB Setting | `/logo.png` | ✅ Enterprise / White Label |
| `brand_logo` | `brand` | DB Setting | `/logo.png` | ✅ Enterprise / White Label |
| `app_favicon` | `brand` | DB Setting | `/favicon.ico` | ✅ Enterprise / White Label |
| `brand_favicon` | `brand` | DB Setting | `/favicon.ico` | ✅ Enterprise / White Label |
| `branding_display` | `brand` | DB Setting | `logo` | ✅ Enterprise / White Label |
| `site_name` | `general` | `SITE_NAME` env → DB | `SMKN 6 Bandung` | ❌ Bebas diubah |
| `site_description` | `general` | `SITE_DESCRIPTION` env → DB | `Portal Resmi ...` | ❌ Bebas diubah |
| `site_logo` | `general` | DB Setting | `/storage/media/kVofJqAjgPI3HVcpYvoFHySz6xMdDsKRNXyy6O9d.webp` | ❌ Bebas diubah |
| `site_favicon` | `general` | DB Setting | `/favicon.ico` | ❌ Bebas diubah |

### Alur Resolusi di Frontend (Tema Sarangenge)

```
displaySchoolName:
  1. Theme Customizer → getSetting('school_name') || getSetting('site_title')
  2. Site Settings    → siteSettings.site_name
  3. Fallback         → 'Portal Sekolah'

siteLogo:
  1. Theme Customizer → getSetting('brand_logo') || getSetting('site_logo')
  2. Site Settings    → siteSettings.site_logo
  3. App Identity     → appIdentity.app_logo
  4. Fallback         → Initial Letter Badge

Console/Auth Branding:
  1. App Identity     → appIdentity.app_name
  2. Fallback         → 'Jejakawan'
```

---

## Konsekuensi

### Positif
1. **Brand Terlindungi**: Identitas "Jejakawan" konsisten di seluruh console, login, dan email tanpa bocor ke nama sekolah.
2. **Reusability**: Engine siap di-deploy ke sekolah lain hanya dengan mengubah `SITE_NAME`, `SITE_DESCRIPTION`, logo institusi, dan Theme Customizer — tanpa perlu mengubah kode.
3. **White Label Path Jelas**: Jika klien ingin rebrand engine (menghilangkan "Jejakawan"), mekanisme lisensi sudah siap.
4. **Session Cookie Stabil**: Cookie selalu `jejakawan-session` di semua deployment, menghindari masalah cross-deployment.
5. **Sinkronisasi Otomatis**: Upload logo di Site Settings langsung mencerminkan perubahan di header, navbar, footer publik, dan member portal.

### Negatif
- Deployment yang sudah berjalan dengan `APP_NAME` salah (seperti seeding awal SMKN 6) perlu dikoreksi manual via `Setting::set()` atau re-seed.


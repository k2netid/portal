# ADR-001: Dinamisasi Identitas Sekolah pada Tema Sarangenge & Isolasi Port Staging (RFC 6335)

**Status:** Accepted  
**Tanggal:** 2026-09-04  
**Author:** Jejakawan Engineering  
**Scope:** `frontend/src/modules/Layout/views/themes/sarangenge/`, `/etc/nginx/sites-available/portal-staging`, `docs/feat_smkn6/`

---

## Konteks

Dalam proses adaptasi tema **Sarangenge** (tema resmi institusi pendidikan Jejakawan Core Engine) untuk website **SMK Negeri 6 Bandung**:

1. **Hardcoded Identitas Sekolah**:
   - Ditemukan beberapa teks nama sekolah ("Sarangenge") tertulis langsung (*hardcoded*) di dalam template Vue (About, Achievement, CareerCenter, Post, Pricing, Search, CTA, Testimonials, Announcements) serta di berkas i18n (`locales/{id,en,su}.json`).
   - Kunci pengaturan `school_name` dan `school_tagline` belum terdaftar dalam skema Theme Customizer (`schema.settings.json` dan `theme.json`). Akibatnya, administrator/operator tidak dapat mengubah nama resmi sekolah secara fleksibel dari panel antarmuka admin konsol.

2. **Kebutuhan Isolasi Port Staging**:
   - Secara default, Nginx staging sebelumnya mendengarkan port 80 dan 8080 sebagai `default_server`.
   - Untuk mempersiapkan rilis live/production website SMKN 6 Bandung di jalur `/home/jejakawan/portal/www/portal` yang akan menggunakan port 80 dan 443, port 80 pada staging harus dibebaskan sepenuhnya agar tidak terjadi bentrok (*port conflict*).
   - Server memerlukan alokasi port khusus staging yang mematuhi standar jaringan internasional.

---

## Keputusan

### 1. Dinamisasi Identitas Sekolah di Seluruh Komponen Tema
- **Penambahan Pengaturan di Customizer**:
  Menambahkan field `school_name` dan `school_tagline` di bawah kategori `"School Info"` pada `customizer/schema.settings.json` dan `theme.json`.
- **Sentralisasi Identitas pada `useSarangengeIdentity.ts`**:
  Composable `useSarangengeIdentity()` dijadikan satu-satunya *Single Source of Truth* (SSOT) untuk nama sekolah, tagline, alamat, kontak, akreditasi, NPSN, dan nama kepala sekolah.
  - Urutan resolusi `displaySchoolName`: `getSetting('school_name')` ➔ `siteSettings.site_name` ➔ fallback default `"SMK Negeri 6 Bandung"`.
- **Refaktor Komponen & Halaman**:
  Mengganti seluruh teks statis yang memuat nama sekolah dengan reaktif `displaySchoolName` atau `displayPrincipalName`.
- **Pembersihan String i18n**:
  Mengubah string terjemahan menjadi netral institusi (contoh: dari *"Keluarga Besar Sarangenge"* menjadi *"Keluarga Besar Kami"* atau disisipi variabel dinamis).

### 2. Penetapan Port Staging Berdasarkan Standar RFC 6335
- Mengikuti acuan **RFC 6335 Bagian 6 (*IANA Dynamic and/or Private Ports 49152–65535*)**:
  - Ditetapkan port utama staging: **`49280`** (`49` + `280` untuk HTTP), berpasangan serasi dengan port SSH kustom server (**`49222`**).
  - Port ini privat, aman dari scanning bot internet, dan dijamin *zero collision*.
  - Port sekunder: **`8080`** (IANA *Registered Port: http-alt*).
  - Port **`80`** dibebaskan sepenuhnya dari staging untuk Virtual Host produksi (`www/portal`).

---

## Konsekuensi

### Positif
1. **Fleksibilitas 100%**: Nama dan profil sekolah dapat diganti sewaktu-waktu melalui Theme Customizer tanpa perlu mengubah kode sumber Vue.
2. **Reusabilitas Tema**: Tema Sarangenge tetap bersih dan dapat diadopsi oleh institusi pendidikan lain tanpa residu hardcode.
3. **Isolasi Lingkungan Bersih**: Staging berjalan independen di port 49280 / 8080 tanpa mengganggu port 80 yang siap digunakan untuk production.

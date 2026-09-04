# Log & Rencana Tugas (Tasks Tracker) — SMKN 6 Bandung

Dokumen ini memantau status pengerjaan fitur, integrasi sistem, dan penyelesaian issue untuk website SMK Negeri 6 Bandung.

---

## 📋 Status & Milestone

### Milestone 1: Fondasi Git & Lingkungan Server (🟢 Selesai)
- [x] Pembuatan kunci SSH Ed25519 terdedikasi (`id_ed25519_git`) dan pendaftaran ke GitHub Deploy Key.
- [x] Pembentukan branch pengembangan terisolasi: `feat/smkn6-theme-sarangenge`.
- [x] Konfigurasi Virtual Host Nginx Staging dengan alokasi port terdedikasi **`49280`** (RFC 6335 Private Port) & **`8080`** (IANA http-alt).
- [x] Pembebasan port 80 untuk persiapan rilis publish (`www/portal`).

### Milestone 2: Dinamisasi Identitas Tema Sarangenge (🟢 Selesai)
- [x] Registrasi tema Sarangenge di database staging `portal_staging`.
- [x] Penyesuaian konfigurasi awal profil SMKN 6 Bandung di `sample-data/bundle.json`.
- [x] **Pembersihan hardcode nama sekolah**:
  - [x] Menambahkan `school_name` dan `school_tagline` ke skema Theme Customizer (`schema.settings.json` & `theme.json`).
  - [x] Memperbarui composable `useSarangengeIdentity.ts` agar mengambil nama sekolah secara dinamis dengan fallback yang bersih.
  - [x] Mengganti seluruh teks statis "Sarangenge" pada halaman (About, Achievement, CareerCenter, Post, Pricing, Search) dan komponen (CtaSection, AnnouncementsSection, AchievementsSection, TestimonialsSection) dengan `displaySchoolName`.
  - [x] Membersihkan teks bawaan di berkas terjemahan i18n (`id.json`, `en.json`, `su.json`) dengan parameter `{school}`.
- [x] Verifikasi paritas i18n (`npm run i18n:check`) dan kompilasi build frontend.
- [x] Deployment ke staging (`www/staging`) dan pengujian akses HTTP 200 di port 49280.

### Milestone 3: Penyesuaian Konten Spesifik Kejuruan (🟢 Selesai)
- [x] Pembuatan CMS Category `program-keahlian` dan injeksi konten dinamis untuk 6 Program Keahlian SMKN 6 Bandung (DPIB, TITL, TPM, TKRO, TAV, TFLM).
- [x] Pembuatan halaman `Programs.vue` (Kompetensi Kejuruan) dan `Facilities.vue` (Fasilitas & Bengkel Praktik) yang mendukung fitur Page Builder.
- [x] Menjaga kompatibilitas mundur dengan menjadikan `Solusi.vue` dan `Services.vue` sebagai *shim* / *redirect*.
- [x] Merombak `Pricing.vue` menjadi laman Informasi Bebas Biaya Pendidikan (BOS/BOPD) dan panduan PPDB terpusat Provinsi Jawa Barat.
- [x] Integrasi navigasi menu yang bersih tanpa hardcode komersial di `bundle.json`.

### Milestone 4: Theme Router Decoupling (🟢 Selesai)
- [x] Pembuatan file `routes.ts` di tema `sarangenge` untuk injeksi rute eksklusif (Programs, Facilities).
- [x] Pembuatan file `routes.ts` di tema `janari` untuk melacak ketergantungan rute tema.
- [x] Pembuatan file `routes.ts` di tema `layung` untuk merapikan rute layung (ISP, MSP).
- [x] Membersihkan `public.ts` di `engine/router` dari rute *hardcoded* tema.
- [x] Implementasi injeksi rute dinamis via `import.meta.glob` di *hook* `beforeEach` berbasis nilai `useTheme().activeTheme`.
- [x] Refactoring `themePageCatalog.ts` agar mengambil iterasi dari `router.getRoutes()` alih-alih array statis, demi stabilitas Theme Customizer.

### Milestone 5: Persiapan Rilis Production (Publish) (⚪ Akan Datang)
- [ ] Setup database production `portal_production` di PostgreSQL 18.
- [ ] Alokasi namespace Valkey/Redis production di CT 102.
- [ ] Konfigurasi Virtual Host Nginx port 80/443 di CT 101 untuk domain resmi `smkn6bandung.sch.id`.
- [ ] Integrasi SSL & Edge Proxy WAF di CT 104 (NPMplus).

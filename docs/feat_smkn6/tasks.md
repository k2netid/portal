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

### Milestone 3: Penyesuaian Konten Spesifik Kejuruan (⚪ Akan Datang)
- [ ] Galeri & informasi 7 Program Keahlian: Rekayasa Perangkat Lunak (RPL), Listrik (TITL), Otomotif (TKRO), Desain Bangunan (DPIB), Otomasi Industri (TOI), Fabrikasi Logam (TFLM).
- [ ] Integrasi modul BKK (Bursa Kerja Khusus) dan lowongan mitra industri.
- [ ] Formulir konsultasi & hotline WhatsApp resmi PPDB 2026/2027.

### Milestone 4: Persiapan Rilis Production (Publish) (⚪ Akan Datang)
- [ ] Setup database production `portal_production` di PostgreSQL 18.
- [ ] Alokasi namespace Valkey/Redis production di CT 102.
- [ ] Konfigurasi Virtual Host Nginx port 80/443 di CT 101 untuk domain resmi `smkn6bandung.sch.id`.
- [ ] Integrasi SSL & Edge Proxy WAF di CT 104 (NPMplus).

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

### Milestone 4: Theme Router Decoupling & Dynamic Public Routing (🟢 Selesai)
- [x] Pembuatan file `routes.ts` di tema `sarangenge` untuk injeksi rute eksklusif (Programs, Facilities, Solusi, Services, Pricing, Career, Achievement, Tim).
- [x] Pembuatan file `routes.ts` di tema `janari` untuk melacak rute eksklusif tema Janari.
- [x] Pembuatan file `routes.ts` di tema `layung` untuk merapikan rute layung (ISP, MSP, Solusi, Services).
- [x] Membersihkan `public.ts` di `engine/router` dari rute *hardcoded* tema yang bertabrakan.
- [x] Perbaikan arsitektural rute induk `name: 'public-layout'` pada router root agar `router.addRoute('public-layout', route)` bersarang sempurna di dalam `FrontendLayout`.
- [x] Penambahan rute baseline universal `/contact` di `publicRoutes` sehingga halaman kontak dapat diakses di seluruh tema tanpa risiko 404.
- [x] Implementasi injeksi rute dinamis reaktif via `import.meta.glob` di *hook* `beforeEach` berbasis `injectedThemeSlug` yang otomatis memuat ulang rute saat tema aktif berganti.
- [x] Refactoring `themePageCatalog.ts` agar mengambil iterasi dari `router.getRoutes()` alih-alih array statis, demi stabilitas Theme Customizer, lengkap dengan pemetaan judul `pages/Programs` dan `pages/Facilities`.

### Milestone 5: Penyelarasan Fitur Lintas Tema (Hero & Social Dock Symmetry) (🟢 Selesai)
- [x] **Penyetaraan & Standardisasi Hero Section di 3 Tema (`Janari`, `Layung`, `Sarangenge`)**:
  - [x] Fleksibilitas Background: Pilihan `slides`, `preset` gradien warna tema, atau `custom_image` dengan slider opasitas overlay kustom.
  - [x] Mode Bagian Bawah Hero (`hero_bottom_mode`): Pilihan fleksibel `'news'` (Strip Warta / Promo), `'stats'` (Bar Metrik & Statistik), atau `'both'` (Mode Ganda / Stacked).
  - [x] Metrik Statistik Dinamis 4-Kolom:
    - *Janari*: Metrik editorial minimalis (Uptime 100%, 50K+ Active Users, 99.9% Satisfaction, 24/7 Support).
    - *Layung*: Metrik telemetri infrastruktur (SLA 99.98%, Backbone 10 Gbps, Active NOC 24/7/365, Latensi Ring < 5ms).
    - *Sarangenge*: Metrik prestasi pendidikan kejuruan (Keterserapan DUDI 100%, 6 Program Keahlian, Rasio Guru 1:12, Akreditasi BAN-S/M A Unggul).
  - [x] Indikator Gulir Animasi (`hero_show_scroll`): Efek visual `.animate-scroll-line` sesuai tema warna masing-masing.
  - [x] Deteksi Tautan Otomatis: CTA utama mendeteksi link eksternal (`http(s)://`) vs rute navigasi internal router.
- [x] **Sistem Universal Floating Social Dock**:
  - [x] Pembuatan komponen dock melayang di tema Janari (`JanariFloatingSocialDock.vue`) dan Sarangenge (`SarangengeFloatingSocialDock.vue`), menyelaraskan komponen Layung (`FloatingSocialDock.vue`).
  - [x] Integrasi saluran komunikasi resmi SMKN 6 Bandung (Hotline WhatsApp PPDB, Instagram, YouTube, Facebook).
  - [x] Pendaftaran skema customizer simetris di seluruh `theme.json` (`enable_floating_social`, `floating_social_position`, `floating_social_default_collapsed`, `floating_social_show_on_mobile`).
- [x] **Paritas Bahasa (i18n) & Standar Kualitas Penuh**:
  - [x] 9.002 entri terjemahan 100% simetris di bahasa Indonesia (`id`), Inggris (`en`), dan Sunda (`su`).
  - [x] Verifikasi mutlak `npm run i18n:check` &rarr; 0 error, 0 disparitas.
  - [x] Verifikasi kompilasi TypeScript `npm run type-check` (`vue-tsc -b`) &rarr; 0 error.
  - [x] Verifikasi unit testing `npm run test:unit` &rarr; 282 / 282 tests passed (100%).
  - [x] Verifikasi build produksi `npm run build` &rarr; sukses terkompilasi dalam 10s.
  - [x] **Penyetaraan & Perbaikan Halaman Kontak Lintas Tema (`/contact`)**:
  - [x] Resolusi Root Cause 404 & Blank: Menyediakan rute universal baseline `/contact` di `public.ts` dan rute eksplisit `contact` di `routes.ts` masing-masing tema (`janari-contact`, `layung-contact`, `sarangenge-contact`).
  - [x] Resolusi `SyntaxError: 10` Vue-I18n: Memperbaiki karakter `@` mentah pada placeholder email (`budi@example.com` &rarr; `budi{'@'}example.com`) yang terdeteksi sebagai linked message parsing error di compiler intlify.
  - [x] Penyelarasan Fitur Tema Sarangenge dengan Janari & Layung:
    - Integrasi `<PluginSlot name="after_hero" class="w-full" />`.
    - Dukungan Google Maps Embed responsif via pengaturan tema `contact_maps_embed_url`.
    - Integrasi alur formulir PPDB online berstandar endpoint `/public/forms/contact/submit` dengan fallback anggun.
  - [x] Verifikasi Kompilasi & Aset: Sinkronisasi rsync aset terkompilasi ke `backend/public/` dan eksekusi `php artisan optimize:clear`.

### Milestone 6: Plugin Integrasi Media Sosial (Instagram Feed) & Sistem Slot Dinamis (🟢 Selesai)
- [x] **Generic & White-Label Architecture**:
  - [x] Konfigurasi awal generic dan kosong (`""`), tanpa keterikatan akun tertentu.
  - [x] Status awal terpasang secara default nonaktif (`inactive` / `is_active: false`).
- [x] **Pencegahan Kerusakan UI (Fail-Safe & Circuit Breaker)**:
  - [x] **Activation Gatekeeper**: Memblokir aktivasi jika parameter wajib (`access_token` dan `instagram_username`) belum diisi dengan respon 422 terstruktur.
  - [x] **Zero Broken UI**: Saat tidak aktif atau kredensial kosong, komponen publik melakukan graceful unmount (`v-if="feedData.enabled && feedData.items.length > 0"`) tanpa kotak kosong, layout shift, atau error console.
  - [x] **Builder Mode Support**: Menampilkan *Notice Placeholder* bersahabat di canvas Page Builder admin saat feed belum aktif/terkonfigurasi.
- [x] **Server-Side Proxy & Caching Engine**:
  - [x] Pembuatan [InstagramFeedService.php](file:///home/jejakawan/dev/smkn6-portal/backend/Modules/Core/app/System/Services/InstagramFeedService.php) dengan caching server-side ber-TTL (default 60 menit) untuk menghindari rate limit Meta API.
  - [x] Endpoint pengujian koneksi `POST /api/v1/manage/infra/extensions/instagram/test-connection` untuk memverifikasi token sebelum aktivasi.
  - [x] Endpoint publik `GET /api/v1/public/social-feed/instagram`.
  - [x] Endpoint manifest blok tema publik `GET /api/v1/public/layout/plugin-blocks`.
- [x] **Komponen Universal & Penempatan Multi-Jalur**:
  - [x] Pembuatan [InstagramFeedBlock.vue](file:///home/jejakawan/dev/smkn6-portal/frontend/src/engine/plugins/blocks/InstagramFeedBlock.vue) dengan opsi tata letak: Bento Grid modern, Classic Grid responsif, dan Carousel slider dengan tombol navigasi geser.
  - [x] Fitur Lightbox interaktif resolusi tinggi dengan navigasi antar-postingan, caption lengkap, metrik likes/komentar, dan cuplikan komentar teratas.
  - [x] Pendaftaran loader [instagramFeed.ts](file:///home/jejakawan/dev/smkn6-portal/frontend/src/engine/plugins/loaders/instagramFeed.ts) pada slot dinamis `after_hero` dan `before_footer`.
  - [x] Pendaftaran blok `instagram_feed` ke dalam Visual Page Builder [BlockRenderer.vue](file:///home/jejakawan/dev/smkn6-portal/frontend/src/modules/Layout/components/content-renderer/BlockRenderer.vue).
  - [x] Pendaftaran skema Theme Customizer simetris di seluruh tema (`Sarangenge`, `Janari`, `Layung`).
- [x] **UI Pengaturan Plugin di Admin Console**:
  - [x] Pembaruan [ConfigureModal.vue](file:///home/jejakawan/dev/smkn6-portal/frontend/src/modules/Core/System/views/settings/extensions/components/ConfigureModal.vue) dengan form konfigurasi visual ramah operator, toggle visibilitas token, selektor slot tema, dan tombol "Uji Koneksi" real-time.
  - [x] Otomatis membuka modal pengaturan saat aktivasi terblokir karena parameter belum lengkap di `Index.vue`.
- [x] **Translasi & Paritas Kualitas Penuh (i18n)**:
  - [x] Penambahan file terjemahan simetris `plugin.json` di bahasa Indonesia (`id`), Inggris (`en`), dan Sunda (`su`) dengan format escape `@` (`{'@'}`).
  - [x] Seluruh pengujian backend `InstagramFeedServiceTest` (4/4 tests, 20 assertions) lolos 100%.
  - [x] Seluruh 282 pengujian frontend unit lolos 100%.
  - [x] Type-check `vue-tsc -b` dan build `npm run deploy:assets:full` lolos tanpa error.
- [x] **Hardening Customizer Dropdown Controls & Schema Normalization**:
  - [x] Memperbaiki dropdown kosong pada kontrol "Gaya Tampilan Instagram Feed" dan "Posisi Penempatan Instagram Feed" di Theme Customizer.
  - [x] Menambahkan normalisasi opsi string dan objek `{ label, value }` pada `resolvedOptions` di [SettingControl.vue](file:///home/jejakawan/dev/smkn6-portal/frontend/src/modules/Layout/components/themes/customizer/sidebar/SettingControl.vue).
  - [x] Menambahkan komputasi `effectiveSelectValue` dengan fallback otomatis ke `setting.default` jika nilai form kosong/belum tersimpan.
  - [x] Menyelaraskan skema tema `schema.settings.json` dan `theme.json` lintas tema (`Sarangenge`, `Janari`, `Layung`) dengan label deskriptif berbahasa Indonesia.
  - [x] Kompilasi build dan deployment aset via `npm run deploy:assets:full` serta pembersihan cache artisan `php artisan optimize:clear`.

### Milestone 7: Persiapan Rilis Production (Publish) (⚪ Akan Datang)
- [ ] Setup database production `portal_production` di PostgreSQL 18.
- [ ] Alokasi namespace Valkey/Redis production di CT 102.
- [ ] Konfigurasi Virtual Host Nginx port 80/443 di CT 101 untuk domain resmi `smkn6bandung.sch.id`.
- [ ] Integrasi SSL & Edge Proxy WAF di CT 104 (NPMplus).

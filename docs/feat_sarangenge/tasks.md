# Log & Rencana Tugas (Tasks Tracker) — Adaptasi Portal Pendidikan

Dokumen ini memantau status pengerjaan fitur, integrasi sistem, dan penyelesaian issue untuk adaptasi platform JA Core Engine ke institusi pendidikan.

---

## 📋 Status & Milestone

### Milestone 1: Fondasi Git & Lingkungan Server (🟢 Selesai)
- [x] Pembuatan kunci SSH Ed25519 terdedikasi (`id_ed25519_git`) dan pendaftaran ke GitHub Deploy Key.
- [x] Pembentukan branch pengembangan terisolasi: `feat/smkn6-theme-sarangenge`.
- [x] Konfigurasi Virtual Host Nginx Staging dengan alokasi port terdedikasi **`49280`** (RFC 6335 Private Port) & **`8080`** (IANA http-alt).
- [x] Pembebasan port 80 untuk persiapan rilis publish (`www/portal`).

### Milestone 2: Dinamisasi Identitas Tema Sarangenge (🟢 Selesai)
- [x] Registrasi tema Sarangenge di database staging `portal_staging`.
- [x] Penyesuaian konfigurasi awal profil sekolah di `sample-data/bundle.json` (sekarang generik).
- [x] **Pembersihan hardcode nama sekolah**:
  - [x] Menambahkan `school_name` dan `school_tagline` ke skema Theme Customizer (`schema.settings.json` & `theme.json`).
  - [x] Memperbarui composable `useSarangengeIdentity.ts` agar mengambil nama sekolah secara dinamis dengan fallback yang bersih.
  - [x] Mengganti seluruh teks statis "Sarangenge" pada halaman (About, Achievement, CareerCenter, Post, Pricing, Search) dan komponen (CtaSection, AnnouncementsSection, AchievementsSection, TestimonialsSection) dengan `displaySchoolName`.
  - [x] Membersihkan teks bawaan di berkas terjemahan i18n (`id.json`, `en.json`, `su.json`) dengan parameter `{school}`.
- [x] Verifikasi paritas i18n (`npm run i18n:check`) dan kompilasi build frontend.
- [x] Deployment ke staging (`www/staging`) dan pengujian akses HTTP 200 di port 49280.

### Milestone 3: Penyesuaian Konten Spesifik Kejuruan (🟢 Selesai)
- [x] Pembuatan CMS Category `program-keahlian` dan injeksi konten dinamis untuk program keahlian kejuruan.
- [x] **Dinamisasi Fasilitas Vokasi & Bengkel Praktik**:
  - [x] Pembuatan CMS Category `fasilitas` ("Fasilitas & Sarana") via [VocationalFacilitiesSeeder.php](file:///home/jejakawan/dev/smkn6-portal/backend/Modules/Publishing/app/Database/Seeders/VocationalFacilitiesSeeder.php).
  - [x] Injeksi 8 konten fasilitas berstandar industri (Studio Desain BIM, Bengkel CNC TPM, Lab Listrik TITL, Bengkel Otomotif TKRO, Lab Mikroelektronik TAV, Bengkel Las TFLM, Gedung CoE Smart Classroom, Perpustakaan Digital).
  - [x] Pembaruan [Facilities.vue](file:///home/jejakawan/dev/smkn6-portal/frontend/src/modules/Layout/views/themes/sarangenge/pages/Facilities.vue) memanggil API publik dinamis (`category: 'fasilitas'`) dengan spinner loading, pemetaan icon adaptif, dan navigasi detail ke `/blog/:slug`.
  - [x] Pemastian dukungan Visual Page Builder (`theme_page: 'pages/Facilities'`, `useThemePageOverride`, dan `BlockRenderer`).
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
  - [x] Integrasi saluran komunikasi resmi sekolah (Hotline WhatsApp PPDB, Instagram, YouTube, Facebook).
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

### Milestone 7: Multi-Theme SoC, Cross-Theme Isolation, & Generalisasi Naming (🟢 Selesai)
- [x] **Eliminasi Kebocoran Tampilan Silang (Cross-Theme Leakage)**:
  - [x] Membatasi kandidat resolving tampilan tema di `themeViewResolver.ts` dan `ThemePageResolver.vue` murni pada `[theme.slug, theme.parent_theme]`.
  - [x] Menghapus fallback silang global ke seluruh sibling themes (`BUNDLED_FRONTEND_THEME_SLUGS`) dan fuzzy resolver lintas tema.
  - [x] Implementasi pembersihan dynamic routes (`activeThemeRouteRemovers`) saat pergantian tema di `public.ts`.
  - [x] Menghapus pemetaan statis `/pricing/isp` dan `/pricing/msp` dari `CORE_PUBLIC_PATH_TO_PAGE` di `FrontendLayout.vue`.
  - [x] Pembuatan unit test `themeViewResolver.spec.ts` untuk memastikan isolasi antar-tema.
- [x] **Standarisasi Arsitektur Multi-Theme SoC**:
  - [x] `janari`: Universal reference theme (`archetype: "universal"`).
  - [x] `sarangenge`: Institutional / Vocational school theme (`archetype: "education_school"`, `parent_theme: "janari"`).
  - [x] `layung`: Corporate & Industry theme (`archetype: "corporate_industry"`, `parent_theme: "janari"`).
  - [x] Sinkronisasi skema kustomisasi terisolasi: Janari (582 keys), Layung (118 keys), Sarangenge (97 keys).
- [x] **Generalisasi Identitas Dinamis & Pembersihan Hardcode**:
  - [x] Resolusi dinamis `siteName` dari `systemStore.siteSettings?.site_name` di `useSarangengeIdentity.ts`, `useLayungIdentity.ts`, dan SEO composables.
  - [x] Pembersihan hardcode title di `main.ts` ('Console') dan `main-public.ts` ('Portal' dengan update dinamis setelah fetch).
  - [x] Dinamisasi preview address bar di `PreviewArea.vue` mengikuti `window.location.host`.
  - [x] Generalisasi footer Janari (`Footer.vue`) dan dock media sosial (`SarangengeFloatingSocialDock.vue`).
- [x] **Generalisasi Penamaan File & Komponen**:
  - [x] Komponen frontend: `SchoolBentoSection.vue` $\rightarrow$ `BentoSection.vue`.
  - [x] Seeder backend: `Smkn6ContentSeeder.php` $\rightarrow$ `VocationalProgramsSeeder.php` dan `Smkn6FacilitiesSeeder.php` $\rightarrow$ `VocationalFacilitiesSeeder.php` dengan alias backward compatibility.
  - [x] Pembersihan meta keywords dan mail default di `FoundationSeeder.php`.
- [x] **Penyelarasan Translasi i18n & Quality Gates**:
  - [x] Menyelaraskan seluruh teks translasi di 3 bahasa (`id.json`, `en.json`, `su.json`) tanpa menyisakan key sampah.
  - [x] 100% Quality Gates: `npm run i18n:check:full` (symmetric), `npm run type-check` (0 error), `npm run test:unit` (286/286 tests passed), `npm run deploy:assets:full` (sukses), `php artisan optimize:clear` (sukses).

### Milestone 7.5: Generalisasi Menyeluruh Brand Tenant & Platform (🟢 Selesai)
- [x] **Penghapusan Total Hardcode SMKN 6 Bandung** (Sarangenge Theme):
  - [x] P0: `useSarangengeIdentity.ts` email fallback → resolve dinamis dari `systemStore` + fallback `info@portal.sch.id`.
  - [x] P0: `bundle.json` → data generik "Portal Sekolah Vokasi" (alamat/telepon/WA dikosongkan).
  - [x] P1: `sidebar.navigation.json` → `"Pricing Page"` → `"Admissions Page"`.
  - [x] P1: `plugin.json` (3 bahasa) → contoh Instagram username generik.
  - [x] P2: `Hero.vue` comment → "Sarangenge Theme (Education Archetype)".
  - [x] P2: `findThemeViewKey` defensive gap → fallback hanya ke `['janari']`.
- [x] **Penghapusan Total Hardcode K2NET** (Layung Theme & Backend):
  - [x] `useLayungIdentity.ts` → legal name, AS name, logo fallback generik.
  - [x] `resolveLayungLocalizedCopy.ts` → email defaults `*@portal.net`.
  - [x] `layungStoreUrls.ts` → empty strings (configurable via customizer).
  - [x] Locale files (id/en/su) → `K2NET` → `Kami` (~44 refs per file).
  - [x] `schema.settings.json` + `theme.json` → defaults generik.
  - [x] 24 Vue components (13 sections + 11 pages) → bulk generalized.
  - [x] Core System locales (id/en/su) → generalized.
  - [x] Backend `challenge.blade.php`, `SpaHtmlFaviconTest.php`, `composer.json` → generalized.
  - [x] Backend Layung theme mirror → synced with frontend.
  - [x] Test fixtures (8 files) → assertions updated.
- [x] **Verifikasi**:
  - [x] `vue-tsc --noEmit` → 0 errors.
  - [x] `vitest run` → 45/45 files, 286/286 tests passed.
  - [x] `grep K2NET|k2net` → 0 matches in source.
  - [x] `grep smkn6|SMKN` → 0 matches in source.
- [x] **Dokumentasi**: ADR-005 diterbitkan.

### Milestone 7.6: Arsitektur Official Plugin Floating Social Dock & Pemisahan Kategori Ekstensi (🟢 Selesai)
- [x] **Transformasi Floating Social Dock Menjadi Official Plugin**:
  - [x] Registrasi migration `2026_09_05_000002_register_floating_social_dock_extension.php` (`slug: floating-social-dock`, `type: plugin`, `family: plugin`).
  - [x] Registrasi slot global `floating_overlay` di `backend/config/layout.php` dengan default binding plugin.
  - [x] Pemasangan `<PluginSlot v-if="!useMemberShell" name="floating_overlay" />` di `FrontendLayout.vue`, menghapus hardcode komponen dock lama di tema.
  - [x] Pembuatan canonical block `FloatingSocialDockBlock.vue` dengan GSAP animations, 7 posisi adaptif, 3 orientasi, 3 style kapsul, ARIA a11y, dan i18n.
  - [x] Penataan Stacking Context di `PluginSlot.vue` (`fixed inset-0 z-[9990] pointer-events-none overflow-visible`) agar interaksi tombol tidak pernah terblokir oleh elemen hero SVG.
- [x] **Integrasi Dua Arah & Conditional Logic dengan Theme Customizer**:
  - [x] Implementasi conditional logic di `useCustomizerNavigation.ts` (`getVisibleSettings`) untuk menyembunyikan opsi subordinate dock saat `enable_floating_social` false.
- [x] **Penyelarasan Kategori di Halaman Manajemen Ekstensi**:
  - [x] Perbaikan fallback kolom `family` di database dan migration menjadi `'plugin'`.
  - [x] Pembaruan `resolveFamily` di `Index.vue` agar `type === 'plugin' || family === 'plugin'` masuk ke tab **Plugin**.
  - [x] Verifikasi tampilan tab Plugin merender `Floating Social Dock & Hotline` dan `Instagram Feed Integration`.
- [x] **Resolusi Keamanan & Bypass E2E Staging**:
  - [x] Penambahan environment `'staging'` ke `CaptchaService::isE2eBypassed()` untuk kelancaran pengujian Playwright tanpa mengorbankan keamanan production.
  - [x] Pendaftaran workstation dev (`192.168.88.4`) dan `127.0.0.1` ke tabel whitelist `sys_ip_lists`.
- [x] **Verifikasi & E2E Testing**:
  - [x] `tests/e2e/floating-social-dock.spec.ts` → Passed di SMKN6 (`49280`) dan K2NET (`8083`).
  - [x] `tests/e2e/extensions-integration.spec.ts` → Passed di SMKN6 (`49280`) dan K2NET (`8083`).
  - [x] `npm run i18n:check` → 27 gate keys, 9098 definisi per bahasa (simetris).
  - [x] Build frontend dan sinkronisasi aset ke `backend/public/` di kedua portal.
- [x] **Dokumentasi**: ADR-006 diterbitkan.

### Milestone 7.7: Arsitektur Siklus Hidup Paket (Ekspor/Impor) Tema & Ekstensi serta Kontrol Lisensi dan Keamanan (🟢 Selesai)
- [x] **Skema Basis Data & Konfigurasi Pengaturan Sistem (`sys_settings`)**:
  - [x] Migration `2026_09_05_000003_add_package_upload_export_settings.php` menambahkan 4 kunci pengaturan: `enable_theme_upload`, `enable_plugin_upload`, `enable_theme_export`, `enable_plugin_export` di bawah grup `security`.
  - [x] Registrasi seeder default di `FoundationSeeder.php`.
- [x] **Integrasi Licensing Service & Entitlements**:
  - [x] Penambahan 4 kapabilitas paket kustom (`theme_upload`, `plugin_upload`, `theme_export`, `plugin_export`) di `LicenseService::getFeaturesMatrix()` dengan pemetaan tier Pro/Enterprise/White-Label.
  - [x] Dukungan developer pass-through untuk environment non-produksi dengan proteksi mode uji isolasi `community`.
- [x] **Backend Package Lifecycle**:
  - [x] Penambahan validasi ganda (setting toggle + license check) pada `ThemePackageInstallService::isEnabled()`.
  - [x] Endpoint `uploadStatus` di `ThemeController` diperluas dengan flag `export_enabled`.
  - [x] Implementasi streaming download file ZIP paket tema di `ThemeController::export` (`GET /api/v1/themes/{theme}/export`).
  - [x] Penambahan helper `isUploadAllowed()` dan `isExportAllowed()` serta endpoint `capabilities` (`GET /manage/system/extensions/capabilities`) di `ExtensionController`.
  - [x] Implementasi streaming download arsip ZIP ekstensi di `ExtensionController::export` (`GET /manage/system/extensions/{slug}/export`) dengan pelindung modul inti via `can_export`.
  - [x] Penyediaan manifest kanonikal `backend/extensions/floating-social-dock/manifest.json`.
- [x] **Harmonisasi UI & i18n Multilingual**:
  - [x] Tombol "Unggah ZIP" dan aksi "Ekspor ZIP" per tema di `frontend/src/modules/Layout/views/themes/Index.vue`.
  - [x] Tombol "Unggah ZIP" dan aksi "Ekspor" per plugin di `frontend/src/modules/Core/System/views/settings/extensions/Index.vue`.
  - [x] Grup akordion baru "Paket Tema & Ekstensi" dengan 4 sakelar toggle di `SecurityTab.vue`.
  - [x] Visualisasi 4 kapabilitas di kartu "Current Tier Capabilities" di `LicenseTab.vue`.
  - [x] Reactive query parameter switching (`?tab=...`) di `General/Index.vue`.
  - [x] Penyelarasan terjemahan `id.json`, `en.json`, `su.json` (9.117 symmetric keys di `npm run i18n:check`).
- [x] **Pengujian & Verifikasi**:
  - [x] Backend tests: `ExtensionControllerTest` (48/48 passed), `ThemePackageLifecycleTest` (16/16 passed).
  - [x] Frontend Playwright E2E: `theme-package-lifecycle.spec.ts` (4/4 passed).
- [x] **Dokumentasi**: ADR-007 diterbitkan.

### Milestone 7.8: Arsitektur Universal Widget Catalog & Smart Fallback WidgetArea (🟢 Selesai)
- [x] **Universal Widget Catalog (`frontend/src/modules/Layout/components/widgets/`)**:
  - [x] `SearchWidget.vue`: Input pencarian interaktif dengan real-time debounced suggestions, keyboard a11y navigation, dan clear button.
  - [x] `CategoriesWidget.vue`: Render kategori dinamis, count badge, subcategory accordion toggle, dan deteksi kategori aktif.
  - [x] `RecentPostsWidget.vue`: Daftar artikel terbaru dengan thumbnail, date formatting, badge kategori, dan filtering current article.
  - [x] `NewsletterWidget.vue`: Formulir langganan buletin warta sekolah dengan validasi email dan status umpan balik interaktif.
  - [x] `SocialShareWidget.vue`: Tombol berbagi artikel instan (WhatsApp, Telegram, X, Facebook) dan copy link to clipboard dengan toast feedback.
  - [x] `index.ts`: Barrel export seluruh widget katalog universal.
- [x] **Peningkatan Smart Fallback pada `WidgetArea.vue`**:
  - [x] Resolusi dinamis komponen widget berdasarkan atribut `widget.type` (`search`, `categories`, `recent_posts`, `newsletter`, `social_share`, `html`, `text`).
  - [x] Smart Fallback Slot: Merender slot cadangan `<slot :context="context">` atau default universal widget stack jika tabel `lay_widgets` belum memiliki konfigurasi kustom, mencegah area sidebar kosong.
- [x] **Integrasi Halaman Detail Artikel (`Post.vue`) & Refaktorisasi `BlogSidebar.vue`**:
  - [x] Pemasangan `<WidgetArea location="sidebar" :context="{ post }">` pada halaman detail artikel `Post.vue` tema Sarangenge dan Janari.
  - [x] Refaktorisasi `BlogSidebar.vue` tema Sarangenge menggunakan `SearchWidget` dan `CategoriesWidget`, mengeliminasi duplikasi kode.
  - [x] Penambahan tipe `search`, `newsletter`, dan `social_share` pada antarmuka dropdown modal pembuatan widget (`WidgetModal.vue`).
- [x] **Pemeriksaan Kualitas & Pengujian**:
  - [x] `npm run i18n:check` → 27 gate keys, 9.145 kunci simetris per bahasa (`id`, `en`, `su`).
  - [x] `npm run build` → Selesai dalam 11.8s (0 type/syntax errors).
  - [x] `sync-frontend-assets-to-backend.sh` → Aset tersinkronisasi ke `backend/public/`.
  - [x] `php artisan test Modules/Layout/tests` → 16/16 tests passed.
  - [x] `tests/e2e/universal-widgets-lifecycle.spec.ts` → 2/2 E2E tests passed.
  - [x] `tests/e2e/theme-package-lifecycle.spec.ts` → 4/4 E2E tests passed.
- [x] **Dokumentasi**: ADR-008 diterbitkan.

### Milestone 8: Persiapan Rilis Production (Publish) (⚪ Akan Datang)
- [ ] Setup database production `portal_production` di PostgreSQL 18.
- [ ] Alokasi namespace Valkey/Redis production di CT 102.
- [ ] Konfigurasi Virtual Host Nginx port 80/443 di CT 101 untuk domain target deployment.
- [ ] Integrasi SSL & Edge Proxy WAF di CT 104 (NPMplus).
- [ ] Konfigurasi `SITE_NAME`, `SITE_DESCRIPTION`, dan `contact_*` via `.env` dan Theme Customizer per deployment.


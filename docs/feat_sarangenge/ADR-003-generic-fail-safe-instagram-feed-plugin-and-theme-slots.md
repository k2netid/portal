# ADR-003: Integrasi Plugin Instagram Feed Generik, Fail-Safe, dan Sistem Slot Tema Dinamis

**Status:** Accepted  
**Tanggal:** 2026-09-05  
**Author:** Jejakawan  
**Scope:** `backend/extensions/instagram-feed`, `InstagramFeedService.php`, `ExtensionController.php`, `PublicInstagramFeedController.php`, `InstagramFeedBlock.vue`, `SettingControl.vue`, `schema.settings.json`, `slot-manifest.json`

---

## Konteks

Website SMK Negeri 6 Bandung (dan instalasi klien lain di masa depan) membutuhkan integrasi umpan media sosial (khususnya Instagram Feed: postingan gambar, reel, jumlah suka, komentar, caption, dan popup detail) yang dapat ditampilkan di beranda maupun halaman lainnya.

Namun, terdapat sejumlah tantangan arsitektural dan reliabilitas penting:
1. **Pencegahan Kerusakan UI (Fail-Safe & Zero Broken UI):**
   Jika token Instagram kedaluwarsa, kuota API Meta Graph habis, akun belum dikonfigurasi, atau plugin dalam keadaan nonaktif, situs publik **tidak boleh** mengalami error 500, broken image/icon, kotak layout kosong yang merusak tata visual (*layout shift*), ataupun kebocoran log API ke pengguna umum.
2. **Generic & White-Label by Default:**
   Plugin ini harus bersifat universal, netral, dan tidak terikat (*hardcoded*) ke akun SMKN 6 Bandung. Plugin terpasang default dalam keadaan nonaktif (`inactive`) dengan nilai awal kosong (`""`).
3. **Activation Gatekeeper (Gerbang Keamanan Aktivasi):**
   Plugin tidak boleh diizinkan untuk diaktifkan dari console admin jika parameter kredensial wajib (`access_token` dan `instagram_username`) belum diisi. Jika diaktifkan tanpa parameter, sistem harus memblokir dengan status `422 Unprocessable Entity` dan memandu operator langsung ke modal konfigurasi.
4. **Penempatan Dinamis Multi-Jalur:**
   Feed harus dapat diletakkan di berbagai posisi secara fleksibel:
   - Menggunakan **Plugin Slot** dinamis lintas tema (`after_header`, `after_hero`, `before_footer`, dll.).
   - Menggunakan **Theme Customizer** (panel kontrol sidebar desain).
   - Menggunakan **Visual Page Builder** sebagai blok independen (`instagram_feed`).
5. **Paritas Penuh Lintas Tema & Bahasa:**
   Dukungan konsisten di seluruh tema (`Sarangenge`, `Janari`, `Layung`) serta paritas terjemahan bahasa Indonesia (`id`), Inggris (`en`), dan Sunda (`su`).

---

## Keputusan Arsitektur

### 1. Arsitektur Plugin Generik & Default Nonaktif
- Manifest plugin di [manifest.json](file:///home/jejakawan/dev/smkn6-portal/backend/extensions/instagram-feed/manifest.json) dan migrasi database [2026_09_05_000001_register_instagram_feed_extension.php](file:///home/jejakawan/dev/smkn6-portal/backend/Modules/Core/database/migrations/2026_09_05_000001_register_instagram_feed_extension.php) mendaftarkan ekstensi `instagram-feed` dengan `is_active: false`.
- Parameter `instagram_username` dan `access_token` default adalah string kosong `""`.

### 2. Activation Gatekeeper & Endpoint Verifikasi
- Pada [ExtensionController.php](file:///home/jejakawan/dev/smkn6-portal/backend/Modules/Core/app/System/Http/Controllers/Console/ExtensionController.php):
  - Saat request aktivasi (`status = 'active'`) masuk, dilakukan validasi keberadaan kredensial. Jika kosong, permintaan ditolak dengan kode respon `422` dan pesan informatif berbahasa lokal.
  - Disediakan endpoint `POST /api/v1/manage/infra/extensions/instagram/test-connection` yang memanfaatkan [InstagramFeedService.php](file:///home/jejakawan/dev/smkn6-portal/backend/Modules/Core/app/System/Services/InstagramFeedService.php) untuk menguji keabsahan token secara real-time dari panel admin sebelum disimpan.

### 3. Server-Side Proxy & Caching Engine
- Kredensial `access_token` dan pemanggilan Meta Graph API diproses secara eksklusif di sisi server (backend proxy) melalui [InstagramFeedService.php](file:///home/jejakawan/dev/smkn6-portal/backend/Modules/Core/app/System/Services/InstagramFeedService.php).
- Token tidak pernah dibocorkan ke frontend klien publik.
- Hasil kueri di-cache di layer memori/Valkey dengan TTL bawaan 60 menit (`cache_ttl_minutes`) untuk menjaga performa dan mematuhi batas *rate-limit* Meta Graph API.
- Endpoint publik: `GET /api/v1/public/social-feed/instagram`. Jika plugin tidak aktif atau unconfigured, endpoint secara konsisten mengembalikan `{ enabled: false, reason: "inactive", items: [] }` dengan HTTP status 200 (aman bagi frontend).

### 4. Komponen Universal [InstagramFeedBlock.vue](file:///home/jejakawan/dev/smkn6-portal/frontend/src/engine/plugins/blocks/InstagramFeedBlock.vue)
- **3 Opsi Tata Letak Visual Mewah:**
  - `bento`: Bento Grid modern berjenjang dengan kartu utama 2x2.
  - `grid`: Classic Responsive Grid (4 kolom desktop, 2 kolom tablet, 1 kolom mobile).
  - `carousel`: Slider horizontal geser halus dengan tombol panah kiri-kanan.
- **Fail-Safe Rendering:**
  - Di situs publik: melakukan *graceful unmount* (`v-if="feedData.enabled && feedData.items.length > 0"`). Jika tidak ada data/nonaktif, tidak ada elemen kosong atau layout shift yang tertinggal di DOM.
  - Di canvas Page Builder admin: menampilkan *Notice Placeholder* rapi dengan tautan instruksi aktivasi.
- **Lightbox Interaktif:** Modal pop-up resolusi tinggi menampilkan gambar/video, caption lengkap, metrik suka dan komentar, cuplikan komentar teratas, dan tombol link ke postingan asli.

### 5. Hardening Theme Customizer & Normalisasi Skema Opsi
- Pada [SettingControl.vue](file:///home/jejakawan/dev/smkn6-portal/frontend/src/modules/Layout/components/themes/customizer/sidebar/SettingControl.vue):
  - Normalisasi `resolvedOptions` untuk mendukung format array string (`['a', 'b']`) dan format objek `{ label, value }` secara simultan.
  - Menambahkan komputasi `effectiveSelectValue` yang memprioritaskan nilai tersimpan dan otomatis jatuh ke `setting.default` jika nilai form masih kosong/belum ditentukan.
  - Menyelaraskan seluruh file skema `schema.settings.json` dan `theme.json` pada tema `Sarangenge`, `Janari`, dan `Layung`.

### 6. Paritas i18n & Keamanan Simbol '@'
- Seluruh teks antarmuka disediakan secara simetris dalam file `plugin.json` untuk `id` (Indonesia), `en` (Inggris), dan `su` (Sunda).
- Simbol `@` yang digunakan pada teks (misalnya username `@username`) di-escape dengan format `{'@'}` untuk menghindari kegagalan kompilasi intlify Vue-i18n (`SyntaxError: 10`).

---

## Dampak & Konsekuensi

- **Keamanan:** Token akses Meta API terlindungi 100% di backend.
- **Stabilitas UI:** Nol risiko tampilan berantakan akibat kegagalan API eksternal pihak ketiga.
- **Kenyamanan Pengguna:** Operator sekolah mendapatkan wizard konfigurasi yang intuitif dengan fitur uji koneksi instan.
- **Dukungan Masa Depan:** Kerangka kerja slot tema dinamis (`PluginSlot`) siap digunakan kembali untuk modul pihak ketiga lainnya (misal: TikTok Feed, Google Reviews, YouTube Channel).

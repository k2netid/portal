# ADR-018: Presets Navigasi Samping Mengambang, GSAP Spring Motion, dan Viewport Scroll Snap Tema Sarangenge

**Status:** Accepted / Implemented  
**Tanggal:** 2026-09-07  
**Author:** Jejakawan Engineering  
**Scope:** `frontend/src/modules/Layout/views/themes/sarangenge/components/SarangengeSideNav.vue`, `frontend/src/modules/Layout/views/themes/sarangenge/pages/Home.vue`, `frontend/src/modules/Layout/views/themes/sarangenge/theme.json`, `frontend/src/modules/Layout/views/themes/sarangenge/schema.settings.json`, `locales/*.json`  
**Supersedes / Extends:** ADR-006 (Master Layout Modes & Homepage Sections), Theme Customizer Schema System  

---

## 1. Konteks & Permasalahan

Tema **Sarangenge** dirancang khusus untuk portal institusi pendidikan kejuruan (seperti SMKN 6 Bandung) dengan visual bento-grid, seksi interaktif bertingkat, dan pengalaman penjelajahan berstandar industri modern:

1. **Eksplorasi Halaman Panjang (Long-form Landing Navigation)**:
   - Beranda memiliki banyak seksi penting (Hero, Pengumuman, Sambutan Kepala Sekolah, Program Keahlian, Fasilitas Bengkel, Prestasi Siswa, Testimoni Industri, Informasi PPDB, dan Kontak).
   - Pengunjung membutuhkan indikator progres posisi halaman yang jelas serta navigasi cepat (*quick jump*) tanpa harus selalu menggulir manual ke atas untuk mencari header navbar.
2. **Kebutuhan Fleksibilitas Desain (Multiple Preset Styles)**:
   - Institusi dan desainer memerlukan variasi visual indikator navigasi samping yang sesuai dengan kepribadian situs (mulai dari minimalis profesional hingga modern berkilau).
3. **Kendala CSS Stacking & Z-Index Clipping**:
   - Jika komponen navigasi samping dirender di dalam kontainer seksi beranda biasa, posisinya rentan terkena `overflow: hidden`, `backdrop-filter`, atau z-index dari seksi induk yang menyebabkan navigasi terpotong (*clipped*) atau posisinya bergeser saat scrolling.
4. **Isolasi Preview Theme Customizer**:
   - Navigasi samping harus dapat berjalan mulus baik saat dirender di situs publik mandiri maupun di dalam kanvas `iframe` Theme Customizer (`SiteEditor.vue`).

---

## 2. Keputusan Arsitektur

### A. Komponen `SarangengeSideNav.vue` & 4 Presets Visual

Dibuat komponen navigasi titik melayang (*floating side dot navigation*) dengan 4 kurasi preset desain:

1. **`glass` (Default - Glassmorphism Bento)**:
   - Panel kapsul mengambang dengan latar semi-transparan (`backdrop-blur-md bg-white/40 dark:bg-slate-900/40`), border halus (`border-slate-200/50 dark:border-slate-700/50`), dan shadow lembut.
2. **`minimal` (Clean Dots Only)**:
   - Titik-titik navigasi mandiri tanpa kapsul kontainer pembungkus untuk estetika ultra-bersih dan tidak mengganggu konten.
3. **`glow` (Cyber / Modern Glow)**:
   - Titik aktif memiliki efek pancaran cahaya (*radial glow ring*) dengan aksen warna primer tema (`ring-4 ring-primary-500/20 shadow-lg shadow-primary-500/30`).
4. **`bars` (Vertical Progress Line & Indicator Tabs)**:
   - Garis progres vertikal dengan tab indikator persegi panjang rounded yang memanjang ketika seksi sedang aktif.

### B. Isolasi DOM Melalui `<Teleport to="body">`

Untuk memastikan navigasi samping bebas dari pembatasan kontainer induk:
- Komponen menggunakan `<Teleport to="body">` secara langsung.
- Penempatan menggunakan koordinat viewport murni:
  ```css
  position: fixed;
  right: 1.5rem;
  top: 50%;
  transform: translateY(-50%);
  z-index: 9999;
  ```
- Pendekatan ini menjamin navigasi selalu berada tepat di tengah sumbu vertikal viewport pengguna pada seluruh resolusi layar (desktop `lg`, `xl`, dan tablet `md`).

### C. GSAP Spring Transitions & Micro-Interactions

- Animasi transisi aktif antar titik menggunakan library **GSAP** (`gsap.to`):
  - Skala dan perubahan tinggi/lebar berlangsung dengan kurva spring elastis (`ease: "back.out(1.7)"`).
  - Label tooltip nama seksi muncul secara halus (`opacity: 0 -> 1`, `x: 10 -> 0`) saat hover titik navigasi.
- Seluruh teks nama seksi pada tooltip terhubung ke i18n (`theme.sarangenge.nav.*`) sehingga mendukung bahasa Indonesia, Inggris, dan Sunda secara simetris.

### D. Scroll Snap Proporsional & Integrasi Customizer

1. **Viewport Scroll Snap**:
   - Dukungan mode gulir `yMandatory` yang dapat diaktifkan/dinonaktifkan melalui Theme Customizer (`enable_scroll_snap: true/false`).
   - Setiap seksi utama beranda diberi atribut `scroll-snap-align: start`.
2. **Skema Pengaturan Theme Customizer (`schema.settings.json` & `theme.json`)**:
   - `enable_side_nav` (Boolean): Menampilkan/menyembunyikan navigasi samping.
   - `side_nav_preset` (Select): Pilihan preset `'glass'`, `'minimal'`, `'glow'`, `'bars'`.
   - `side_nav_show_tooltips` (Boolean): Menampilkan label tooltip nama seksi saat kursor mendekati titik.
   - `side_nav_scroll_snap` (Boolean): Pengaktifan scroll snap presisi.

---

## 3. Konsekuensi & Keuntungan

1. **Pengalaman Pengguna Interaktif (Enhanced UX)**: Memberikan kesan portal pendidikan modern berkelas dunia (*award-winning aesthetic*) dengan navigasi yang responsif dan intuitif.
2. **Bebas Masalah CSS Overflow**: Penggunaan Teleport ke `body` menjamin elemen navigasi tidak pernah terpotong oleh seksi yang memiliki `overflow-hidden`.
3. **Penuh Paritas i18n**: Tooltip multibahasa otomatis menyesuaikan dengan preferensi bahasa pengguna (ID, EN, SU).
4. **Resilience di Iframe Customizer**: Berjalan sempurna di kanvas live preview tanpa benturan event bus.

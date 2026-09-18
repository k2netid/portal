# ADR-018: Platform-Wide Plugin SoC & SoT Architecture — Cinematic Side Navigation (`cinematic-nav`), Floating Extensions, and Slot Contracts

**Status:** Accepted / Implemented  
**Tanggal:** 2026-09-08  
**Author:** Jejakawan Core Engineering  
**Scope:** Platform Engine (`frontend/src/engine/plugins/`), All Themes (`sarangenge`, `sareupna`, `layung`, `janari`), Backend Extension Manifests (`backend/extensions/`), Global Layout Slots (`FrontendLayout.vue`, `layout.php`)  
**Supersedes / Extends:** ADR-006 (Master Layout Modes & Homepage Sections), Theme Customizer Schema System  

---

## 1. Konteks & Permasalahan Arsitektur

Sebelumnya, beberapa fitur interaktif lintas-tema seperti navigasi samping titik melayang (*side navigation dots*) dan bilah media sosial mengambang (*floating social dock*) diimplementasikan secara terisolasi atau bahkan diduplikasi di dalam direktori masing-masing tema:

1. **Pelanggaran Separation of Concerns (SoC) & Single Source of Truth (SoT)**:
   - Tema bertanggung jawab atas identitas visual (CSS variables, tipografi, warna), struktur markup semantik, dan titik penempatan slot plugin.
   - Mengimplementasikan fitur interaktif kompleks (seperti GSAP spring animation, observasi viewport IntersectionObserver, dock physics, atau integrasi API feed pihak ketiga) langsung di dalam tema menyebabkan duplikasi ribuan baris kode, inkonsistensi perilaku antar tema, dan overhead pemeliharaan tinggi.
2. **Duplikasi Komponen & Masalah Double-Rendering**:
   - Di tema `janari`, komponen `JanariFloatingSocialDock.vue` di-hardcode di dalam `Footer.vue`, sementara di saat yang sama `FrontendLayout.vue` juga merender `FloatingSocialDockBlock.vue` melalui slot plugin `floating_overlay`.
   - Di tema `sarangenge` dan `layung`, terdapat file-file yatim (*orphaned files*) seperti `SarangengeFloatingSocialDock.vue` dan `FloatingSocialDock.vue`.
   - Di tema `sarangenge` dan `sareupna`, masing-masing membuat salinan komponen `SectionNavDots.vue`.
3. **Inkonsistensi Dukungan Plugin Antar Tema**:
   - Fitur seperti `instagram-feed` yang terdaftar pada slot `after_hero` tidak dapat tampil di tema `sareupna` atau `layung` karena halaman beranda kedua tema tersebut belum menyediakan komponen `<PluginSlot name="after_hero" />`.

---

## 2. Keputusan Arsitektur: Kontrak Tema vs Kontrak Plugin

### A. Kontrak Tema (Theme Responsibility)

Setiap tema di platform Jejakawan kini menerapkan prinsip minimalis dan deklaratif:
1. **Design Tokens**: Tema hanya mendefinisikan variabel CSS semantik (`--primary`, `--background`, `--foreground`, `--muted-foreground`, `--border`, dll.).
2. **Semantic Section Anchors**: Setiap seksi pada beranda wajib memiliki atribut `id` semantik (misalnya `id="section-hero"`, `id="section-bento"`, `id="section-terminal"`, `id="section-products"`, `id="section-cta"`) dan dapat menyertakan atribut opsional `data-nav-label="Kustom Label"`.
3. **Standard Plugin Slots**: Tema wajib menempatkan slot standar layout platform:
   - Layout Level (`FrontendLayout.vue`): `<PluginSlot name="after_header" />`, `<PluginSlot name="before_footer" />`, `<PluginSlot name="floating_overlay" />`.
   - Page Level (`Home.vue`): `<PluginSlot name="after_hero" class="w-full" />` segera setelah komponen Hero.
   - Post Level (`Post.vue`): `<PluginSlot name="after_post_content" />`, `<PluginSlot name="sidebar_article" />`.
4. **Anti-Pattern (DILARANG)**: Tema **dilarang keras** meng-hardcode, menduplikasi, atau membungkus komponen floating overlay (seperti floating dock sosial, floating navigation dots, back-to-top duplikat, dll.) di dalam kode komponen tema.

### B. Kontrak Plugin (Plugin Responsibility - Single Source of Truth)

Seluruh fitur interaktif mengambang, integrasi konten eksternal, dan blok layout modular dikelola 100% sebagai **Engine Plugin**:
1. **Penyimpanan Kode**: Terpusat di `frontend/src/engine/plugins/blocks/` dan didaftarkan melalui `frontend/src/engine/plugins/loaders/`.
2. **Backend Manifest & Registrasi**: Memiliki manifes resmi di `backend/extensions/{plugin-slug}/manifest.json`, terdaftar di `backend/config/layout.php`, serta memiliki migration basis data pada `backend/Modules/Core/database/migrations/`.
3. **Isolasi Tampilan**: Komponen plugin beradaptasi secara dinamis terhadap token CSS tema yang sedang aktif melalui utility class Tailwind (`bg-primary`, `text-primary-foreground`, `border-border`, dll.).

---

## 3. Spesifikasi Engine Plugin `cinematic-nav`

Plugin `cinematic-nav` bertindak sebagai Single Source of Truth untuk navigasi titik mengambang interaktif pada seluruh tema:

1. **Auto-Discovery Seksi Halaman (Zero Config)**:
   - Komponen secara otomatis memindai DOM pada halaman aktif untuk menemukan kontainer seksi menggunakan selektor: `[data-nav-section]`, `[data-section-id]`, `section[id]`, dan `div[id^="section-"]`.
   - Nama label seksi di-resolve secara dinamis berdasarkan kamus multibahasa terintegrasi (`id`, `en`, `su`) atau atribut `data-nav-label`.
2. **4 Kurasi Preset Visual**:
   - `glass`: Kapsul glassmorphism mengambang dengan latar `backdrop-blur-md bg-background/50 border border-border/40`.
   - `minimal`: Indikator titik bersih tanpa latar belakang kapsul.
   - `glow`: Efek cincin neon (*radial glow ring*) dengan aksen warna primer tema (`ring-4 ring-primary/20 shadow-lg shadow-primary/30`).
   - `bars`: Garis progres vertikal dengan tab indikator persegi panjang rounded yang memanjang elastis saat seksi aktif.
3. **Motion Physics GSAP**:
   - Transisi pergerakan indikator titik dan pelebaran tab menggunakan kurva spring GSAP (`ease: "back.out(1.7)"`, durasi `0.4s - 0.6s`).
   - Tooltip label muncul dengan animasi fade & slide lembut saat hover.
4. **Perhitungan Smooth Scroll Cerdas**:
   - Klik pada titik navigasi menghitung offset tinggi header navbar dinamis (termasuk floating header dock pada tema modern seperti `sareupna` dan `sarangenge`) sehingga judul seksi tidak tertutup navbar.
5. **Viewport Scroll Snap**:
   - Menyediakan opsi gulir presisi berbasis `scroll-snap-type: y mandatory` yang dapat diaktifkan/dinonaktifkan melalui konfigurasi customizer.
6. **Slot Coexistence**:
   - Terpasang pada slot `floating_overlay`. Navigasi ditempatkan secara terisolasi pada koordinat `fixed right-6 top-1/2 -translate-y-1/2 z-[9990]` sehingga berdampingan sempurna tanpa benturan fisik dengan `floating-social-dock` maupun tombol `back-to-top`.

---

## 4. Rangkuman Pembersihan Legacy (Cleanup Checklist)

| Fitur / Komponen | Tindakan | Lokasi Target | Status |
| :--- | :--- | :--- | :--- |
| `JanariFloatingSocialDock.vue` | **DELETED** | `themes/janari/components/layout/` | Digantikan penuh oleh `FloatingSocialDockBlock.vue` |
| Pemanggilan di `Footer.vue` | **REMOVED** | `themes/janari/components/layout/Footer.vue` | Bersih dari hardcode plugin |
| `SarangengeFloatingSocialDock.vue` | **DELETED** | `themes/sarangenge/components/layout/` | Hapus file yatim |
| `FloatingSocialDock.vue` | **DELETED** | `themes/layung/components/layout/` | Hapus file yatim |
| `SectionNavDots.vue` | **DELETED** | `themes/sarangenge/components/shared/` | Digantikan penuh oleh `CinematicNavBlock.vue` |
| `SectionNavDots.vue` | **DELETED** | `themes/sareupna/components/shared/` | Digantikan penuh oleh `CinematicNavBlock.vue` |
| Slot `after_hero` | **ADDED** | `themes/sareupna/pages/Home.vue` & `themes/layung/pages/Home.vue` | Dukungan universal `instagram-feed` |
| ID Seksi Semantik | **STANDARDIZED** | Seluruh seksi di `Home.vue` semua tema | Kompatibilitas `cinematic-nav` |

---

## 5. Keuntungan & Dampak Arsitektur

1. **Zero Code Redundancy**: Menghilangkan lebih dari 1.500 baris duplikasi kode di tema-tema platform.
2. **Universal Theme Compatibility**: Fitur baru pada plugin (misal preset navigasi baru atau optimasi performa) otomatis dapat dinikmati oleh tema `Sareupna`, `Sarangenge`, `Layung`, `Janari`, maupun tema kustom masa depan tanpa perlu memodifikasi file tema.
3. **Penyelarasan Standar Industri**: Memenuhi standar arsitektur extensible modern (Shopify app blocks, WordPress block hooks, Shopware plugins).

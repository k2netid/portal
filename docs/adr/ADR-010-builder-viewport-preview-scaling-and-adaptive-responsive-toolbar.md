# ADR-010: Visual Builder Viewport Preview Scaling, Adaptive Multi-Breakpoint Toolbar, & Auto-Fit Device Mode

**Status:** Accepted  
**Tanggal:** 2026-09-06  
**Author:** Jejakawan Engineering  
**Scope:** `frontend/src/modules/Layout/components/builder/`  

---

## Konteks

Sebelumnya, area visual editor dan toolbar pada Visual Page Builder (`JA-Builder`) memiliki beberapa kelemahan struktural dan visual:

1. **Disparitas Fitur dengan Theme Customizer**:
   - Theme Customizer sudah mengadopsi penskalaan proporsional dua-layer (`PreviewArea.vue`) dan mockup hardware perangkat modern (ADR-003).
   - Di sisi lain, Visual Page Builder (`CanvasFrame.vue`) masih menggunakan kontainer statis tanpa frame hardware realistis, tanpa kontrol zoom proporsional, dan tanpa indikator resolusi aktif.

2. **Kerusakan Layout Toolbar pada Layar Sempit / Split-Screen**:
   - `.top-toolbar__center` diposisikan secara mutlak (`position: absolute; left: 50%; transform: translate(-50%, -50%)`). Ketika jendela browser dipersempit (misalnya mode split-screen 500px–900px), toolbar tengah bertabrakan dengan bagian kiri (Logo/Menu) dan bagian kanan (Draft, Publish, Close).
   - Selector scoped CSS `.zoom-slider-container { display: flex; }` meng-override utility class Tailwind `hidden xl:flex` karena specificitas scoped CSS, menyebabkan slider rentang zoom selalu tampil dan memakan ruang horizontal ~90px di layar kecil.
   - Sisi kanan toolbar (`.top-toolbar__right`) yang memuat aksi paling vital (**Publish** dan tombol **Close `X`**) terdorong keluar dari batas layar kanan (*horizontal clipping / hidden overflow*), sehingga pengguna tidak bisa menyimpan atau menutup builder.

3. **Kanvas Meluap Secara Horizontal (*Horizontal Scroll Spill*)**:
   - Pada mode Desktop dengan zoom (misalnya 90%), base width 1280px menghasilkan 1152px (`1280 × 0.9`). Ketika dibuka di kontainer builder yang lebih kecil dari 1152px, kanvas memicu horizontal scrollbar yang mengganggu alur desain.
   - Tidak tersedia opsi *Fit to Screen* yang secara otomatis menyesuaikan rasio skala terhadap lebar kontainer yang tersedia.

---

## Keputusan

### 1. Penskalaan Proporsional Dua-Layer & Hardware Mockup Frames (`CanvasFrame.vue`)
- **Struktur Dua-Layer (Stage Wrapper & Viewport)**:
  - Layer luar (`stageWrapperStyle`): Mengatur bounding box dimensi terkalkulasi `(baseWidth * scale)` × `(baseHeight * scale)` sehingga perataan kanvas tetap terpusat (*centered*) tanpa ruang kosong (*dead space*) berlebih.
  - Layer dalam (`previewStyles`): Mengaplikasikan transformasi hardware-accelerated:
    `transform: scale(effectiveScale) translateZ(0)`
    `transform-origin: top center`
- **Tiga Frame Mockup Perangkat**:
  - **Desktop (macOS Studio Window)**:
    - Ditampilkan saat zoom $\ne 100\%$ atau menggunakan custom width.
    - Dilengkapi header frosted glass / blur, traffic light controls (`#ff5f56`, `#ffbd2e`, `#27c93f`), address bar pill dengan ikon SSL lock `Lock`, URL aktif, dan badge resolusi dinamis.
    - Pada zoom $100\%$ tanpa custom width, frame otomatis beralih ke kanvas tepi-ke-tepi (*edge-to-edge*, `items-stretch p-0`) untuk performa maksimal.
  - **Tablet (iPad Pro Titanium Bezel)**:
    - Bezel titanium gelap, ambient camera dot, status bar `9:41`, ikon wifi/baterai, dan home indicator bar bawah. Base width: $768\text{px} + 28\text{px}$ bezel ($796\text{px}$).
  - **Mobile (iPhone 16 Pro Dynamic Island)**:
    - Bezel hardware melengkung, fitur **Dynamic Island** dengan sensor kamera, status bar `9:41`, dan home indicator bar bawah. Base width: $390\text{px} + 28\text{px}$ bezel ($418\text{px}$).
- **Scroll Internal Mandiri**:
  - Konten halaman di dalam kanvas (`canvas-frame__screen`) memiliki scrollbar sendiri (`overflow-y: auto; overflow-x: hidden; custom-scrollbar`), menjaga frame perangkat tetap stabil saat mendesain halaman panjang.

### 2. Mode Skala Otomatis "Fit to Screen" (Auto)
- Menambahkan opsi **`Fit to Screen (Auto)`** (`zoom = 0` atau `zoom <= 0`) pada dropdown skala.
- Menggunakan `useElementSize(containerRef)` dari `@vueuse/core` untuk mengukur lebar kontainer kanvas secara reaktif (`containerWidth`).
- Algoritma perhitungan skala fit:
  $$\text{availW} = \max(260, \text{containerWidth} - \text{padding})$$
  $$\text{maxFitScale} = \min\left(1, \max\left(0.15, \left\lfloor \frac{\text{availW}}{\text{baseW}} \times 100 \right\rfloor \div 100\right)\right)$$
- Hasil:
  - Pada layar sempit 500px, preview Desktop (1280px) otomatis diskalakan menjadi $\approx 35\%$, Tablet (796px) menjadi $\approx 56\%$, dan Mobile (418px) tetap $100\%$.
  - Header macOS Studio secara dinamis menampilkan tag resolusi adaptif seperti `Fit 35%`.

### 3. Refactor Tata Letak Flex & Breakpoint Toolbar (`TopToolbar.vue`)
- **Transisi Layout Tengah Dinamis**:
  - Layar lebar ($\ge 1200\text{px}$): Menggunakan `position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%)` untuk simetri visual sempurna.
  - Layar sedang & kecil ($< 1200\text{px}$): Menggunakan flexbox murni (`display: flex; flex: 1 1 auto; min-width: 0; justify-content: center;`). Elemen tengah menyusut dengan aman tanpa mendesak sisi luar.
- **Proteksi Tombol Aksi Kritis**:
  - `.top-toolbar__right` diberi aturan wajib:
    ```css
    flex-shrink: 0;
    margin-left: auto;
    z-index: 2;
    ```
  - Memastikan tombol **Save Draft**, **Publish**, dan **Close `(X)`** **selalu terlihat dan dapat diklik** pada semua resolusi layar (dari 320px mobile hingga 4K).
- **Aturan Breakpoint Responsif**:
  - **$\ge 1280\text{px}$**: Tampilan penuh desktop. Slider zoom aktif (`min="25"`, `max="150"`, `step="5"`).
  - **$< 1280\text{px}$**: Slider zoom disembunyikan via scoped CSS media query.
  - **$\le 1024\text{px}$**:
    - 4 tombol device mode diciutkan menjadi **1 tombol dropdown device ringkas**.
    - Nama tema disembunyikan (hanya ikon palette).
    - Tombol sekunder (fullscreen toggle & live preview luar) serta AdminLogo disembunyikan.
    - Tombol hamburger menu aktif di kiri untuk membuka drawer navigasi/layer.
  - **$\le 768\text{px}$**:
    - Tombol zoom in/out (`+` / `-`) disembunyikan, digantikan dropdown `{{ zoomDisplay }}` yang sangat ringkas.
    - Tombol AI Sparkles dan Save Draft disembunyikan untuk memprioritaskan tombol Publish & Close.
  - **$\le 480\text{px}$**:
    - Tombol Publish beralih menjadi icon-only (`Save` icon).
    - Undo/Redo dan divider disembunyikan sementara agar tidak ada overflow sama sekali pada layar $\le 360\text{px}$.

### 4. Optimalisasi Kontainer Kanvas (`builder.css`)
- `.ja-builder__canvas-area`:
  - Diubah menjadi `align-items: stretch; padding: 0; overflow: hidden; min-height: 0; min-width: 0;`.
  - Mencegah margin ganda atau double-scrollbar antara kontainer pembungkus dan `CanvasFrame`.

---

## File yang Diubah

| File | Perubahan |
|---|---|
| `frontend/src/modules/Layout/components/builder/layout/CanvasFrame.vue` | Implementasi two-layer stage wrapper, macOS Studio Window, iPad Pro & iPhone 16 Pro mockup frames, reactive `useElementSize` auto-fit calculation, dan resolution badge. |
| `frontend/src/modules/Layout/components/builder/layout/TopToolbar.vue` | Refactor flex layout, proteksi sisi kanan (`flex-shrink: 0`), breakpoint cascading ($\ge 1280\text{px}$, $\le 1024\text{px}$, $\le 768\text{px}$, $\le 480\text{px}$), dukungan mode Fit (`zoomDisplay = 'Fit'`), dan granular zoom presets (`[25, 33, 50, 75, 90, 100, 110, 125, 150]`). |
| `frontend/src/modules/Layout/components/builder/styles/builder.css` | Penyesuaian fleksibilitas area kanvas builder (`align-items: stretch`, `padding: 0`, `overflow: hidden`). |
| `frontend/tests/unit/modules/Layout/builderToolbarZoom.spec.ts` | Unit test suite pengujian zoom in, zoom out, slider input, dan tampilan mode Fit (`zoom <= 0`). |
| `frontend/tests/unit/modules/Layout/builderViewportScaling.spec.ts` | Unit test suite pengujian scaling desktop (100% fluid vs 75% scaled), tablet mockup, mobile mockup, dan auto-fit calculation. |

---

## Pengujian & Verifikasi

1. **Unit Tests**:
   - `npm run test:unit tests/unit/modules/Layout/builderToolbarZoom.spec.ts tests/unit/modules/Layout/builderViewportScaling.spec.ts` $\rightarrow$ 9/9 passed.
   - Full test suite: 47 files / 295 tests lulus (100% green).
2. **Type Check & Lint**:
   - `npm run type-check` $\rightarrow$ 0 error (vue-tsc & i18n symmetric keys valid).
3. **Build & Staging Deploy**:
   - Bundle production Vite berhasil dicompile.
   - Aset tersinkronisasi ke `backend/public/` dan cache Laravel dibersihkan via `php8.5 artisan optimize:clear`.
   - Endpoint staging terverifikasi responsif (`HTTP 200 OK`).

---

## Konsekuensi

### Positif
- **Pengalaman Desain Konsisten**: Pengguna builder mendapatkan fidelitas visual yang identik dengan Theme Customizer.
- **Kenyamanan di Segala Layar**: Visual editor dapat digunakan secara mulus pada layar monitor ultra-wide, laptop 13 inci, tablet, hingga mode split-screen tanpa tombol yang terpotong atau hilang.
- **Auto-Fit Bebas Repot**: Pengguna di layar kecil tidak perlu manual mengecilkan zoom; opsi "Fit to Screen" secara otomatis mengompensasi dimensi kanvas.

### Netral / Mitigasi
- Pada layar ultra-kecil ($\le 480\text{px}$), beberapa tombol sekunder (Undo/Redo, Draft) disembunyikan dari toolbar utama untuk menjaga tombol Publish dan Close tetap terlihat; fungsi ini tetap dapat diakses melalui keyboard shortcuts (`Ctrl+Z` / `Ctrl+Y`) atau auto-save.

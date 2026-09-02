# ADR-003: Proportional Canvas Zoom Scaling, Viewport Device Mockup Frames, & Compact Responsive Toolbars

**Status:** Accepted  
**Tanggal:** 2026-09-02  
**Author:** Jejakawan Engineering  
**Scope:** `frontend/src/modules/Layout/components/themes/customizer/`

---

## Konteks

Sebelumnya, area pratinjau (*Preview Area*) pada Theme Customizer memiliki beberapa keterbatasan:
1. **Fixed Static Viewport**: Kanvas hanya dapat berganti antara resolusi statis kaku tanpa kemampuan zoom in / zoom out proporsional seperti fitur browser zoom.
2. **Unstyled Heavy Borders**: Frame perangkat mobile/tablet hanya berupa border hitam tebal 14px (`border-[14px] border-slate-900`) tanpa aksen hardware modern.
3. **Redundant Controls**: Terdapat tombol ganda untuk *hide nav* / *hide properties* di header utama yang menduplikasi tombol chevron yang sudah ada di masing-masing panel samping.
4. **Header & Toolbar Overflow**: Pada viewport menengah atau layar sempit, label teks panjang pada tombol aksi (*Terbitkan Perubahan*, *Kembalikan*, mode switcher) saling bertabrakan atau terpotong (*clipped*).

---

## Keputusan

### 1. Penskalaan Proporsional Kanvas (CSS Transform Scale)
- Menerapkan rendering iframe pada resolusi kanvas standar perangkat asli:
  - **Desktop**: `1280px` (atau `100%` pada zoom 100%)
  - **Tablet**: `768px × 1024px` (iPad Pro standard)
  - **Mobile**: `390px × 844px` (iPhone modern standard)
- Transformasi skala dijalankan melalui:
  `transform: scale(zoomLevel / 100) translateZ(0)`
  `transform-origin: top center`
- Bounding box pembungkus (`stageWrapperStyle`) dinamis mengikuti dimensi `(baseWidth * scale)` × `(baseHeight * scale)` sehingga scroll stage kanvas terpusat (*centered*) sempurna dan scrollbar hanya muncul bila skala melebihi batas area kerja.

### 2. Kontrol Skala Interaktif pada Toolbar
- Slider persentase interaktif rentang **`50%` hingga `150%`** (step 5%).
- Tombol cepat **Zoom Out (`-10%`)** dan **Zoom In (`+10%`)** dengan icon `ZoomOut` / `ZoomIn`.
- Dropdown preset persentase (`50%`, `75%`, `90%`, `100%`, `110%`, `125%`, `150%`) dan opsi *Reset ke 100%*.

### 3. Mockup Frame Perangkat Modern & Realistis
- **Desktop Browser Chrome (macOS Studio Window)**:
  - Mockup jendela browser dengan header macOS frosted glass: 3 titik lampu jendela (`#ff5f56`, `#ffbd2e`, `#27c93f`), address bar pill dengan ikon gembok SSL `Lock`, alamat situs, dan badge resolusi.
- **Tablet Frame (iPad Pro Titanium Bezel)**:
  - Chassis ramping bergradasi titanium (`from-slate-800 via-slate-900 to-slate-950`), ambient sensor camera dot, status bar `9:41`, dan bottom home indicator.
- **Mobile Frame (iPhone 16 Pro Dynamic Island)**:
  - Chassis bergradasi titanium hardware dengan fitur **Dynamic Island** di bagian atas, status bar `9:41`, dan home indicator bar.

### 4. Toolbar Compact Icon-First & Peningkatan Responsif
- **[CustomizerHeader.vue](file:///home/jejakawan/dev/k2net-portal/frontend/src/modules/Layout/components/themes/customizer/header/CustomizerHeader.vue)**:
  - Menghapus tombol toggle panel duplikasi.
  - Tombol *Kembalikan* (Revert) diubah menjadi icon-only button (`RotateCcw`) dengan tooltip deskriptif dan dropdown opsi.
  - Tombol *Terbitkan* (Publish) responsif (icon `Save`/spinner di mobile, label teks di desktop).
  - Mode Switcher menggunakan icon `Paintbrush`, `Database`, `Code` dengan tooltips.
- **[PreviewArea.vue](file:///home/jejakawan/dev/k2net-portal/frontend/src/modules/Layout/components/themes/customizer/preview/PreviewArea.vue)**:
  - Toolbar berukuran compact `h-10 sm:h-11` dengan scrollbar pelindung overflow.
  - Slider otomatis responsif di mobile (`w-16` hingga `w-28`) dengan tetap mempertahankan kontrol `[-]`, `[100% ▾]`, `[+]`.

---

## File yang Diubah

| File | Perubahan |
|------|-----------|
| `components/themes/customizer/preview/PreviewArea.vue` | Implementasi zoom slider, presets, proportional transform scaling, dan mockup frames modern |
| `components/themes/customizer/header/CustomizerHeader.vue` | Redesain header compact icon-first, pembersihan duplikasi tombol, dan tooltips |
| `customizer/shell/ThemeCustomizerView.vue` | Penghapusan binding props toggle pane yang redundan |
| `router.ts` & `ConsoleLayout.vue` | Penyeimbangan gutter full-width (`fullWidth: true`) untuk area kerja customizer yang lebih lapang |

---

## Konsekuensi

### Positif
- Pengalaman pratinjau tema terasa sangat halus, proporsional, dan menyerupai viewport perangkat fisik sesungguhnya.
- Tampilan frame perangkat terasa premium dan state-of-the-art.
- Navigasi toolbar tidak lagi mengalami clipping atau overflow di berbagai resolusi layar.

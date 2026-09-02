# ADR-005: Theme Visual Styles Architecture, Real-Time Cross-Frame Sync, Dynamic Typography, & Smart Popper Positioning

**Status:** Accepted  
**Tanggal:** 2026-09-02  
**Author:** Jejakawan Engineering  
**Scope:** `frontend/src/modules/Layout/`, `frontend/src/shared/components/ui/`, `frontend/src/styles/`

---

## Konteks

Dalam pengembangan fitur Theme Customizer dan Theme Layung, ditemukan beberapa isu arsitektural:
1. **Theme Visual Style Kurang Berdampak**: Pilihan *Theme Visual Style* (*Clean*, *Flat*, *Soft*, *Glassmorphism*) di Customizer tidak mengubah tampilan kartu/bento di Theme Layung karena data attribute belum dibinding ke root layout dan belum ada implementasi CSS untuk masing-masing varian.
2. **Lag / Kehilangan Sinkronisasi Antar-Frame**: Saat opsi diganti di sidebar customizer, canvas preview tidak langsung memperbarui tampilan karena listener `postMessage` di `useTheme.ts` mengabaikan event `JA_THEME_CUSTOMIZER_SYNC` dan tidak memicu ulang kalkulasi CSS variables (`applyThemeStyles()`).
3. **Dropdown Popper Terpotong di Batas Bawah Layar**: Pada resolusi layar tertentu atau saat scroll mendekati taskbar bawah, komponen dropdown `SelectContent.vue` selalu membuka ke bawah dan terpotong tanpa mendeteksi batas viewport.
4. **Kebocoran CSS Transparan pada Dropdown**: Efek *glass* pada shell console menyebabkan background dropdown form menjadi transparan dan teks di bawahnya tembus pandang (*bleeding*).

---

## Keputusan

### 1. 4 Paradigma Arsitektur Visual Style (`theme_style`)
Menerapkan 4 varian visual yang jelas dan tegas di seluruh komponen Layung:
- **Clean & Minimalist (Default)**: Desain korporat modern, border tajam presisi 1px, sudut standar 8px–12px, dan bayangan lembut.
- **Flat Modern (Swiss / Neo-Flat)**: Menghapus seluruh efek bayangan (`box-shadow: none !important`), border solid 1.5px, kontras tinggi.
- **Soft & Pastel (Playful Modern)**: Sudut melengkung ekstra lebar (`--radius: 1.25rem - 1.5rem`), bayangan menyebar luas (*diffused glow*), tampilan ramah dan modern.
- **Premium Glassmorphism**: Latar kartu *frosted glass* semi-transparan (`rgba(255,255,255,0.72)` / dark `rgba(11,17,26,0.68)`), `backdrop-filter: blur(16px-20px) saturate(180%)`, dan border berpendar neon Cyan K2NET.

### 2. Sinkronisasi Reaktif Cross-Frame (`useTheme.ts` & `FrontendLayout.vue`)
- Mengintegrasikan penanganan pesan `JA_THEME_CUSTOMIZER_SYNC` dan `THEME_UPDATE` di `useTheme.ts`.
- Menerapkan penggabungan reaktif langsung ke `themeSettings.value` dan `activeTheme.value.settings`, serta mengeksekusi `applyThemeStyles()` dan `applyCustomCss()` seketika.
- Menginjeksi attribute DOM `data-theme-style`, `data-theme-nav`, `data-button-radius`, dan `data-button-shadow` pada root element `FrontendLayout.vue`.

### 3. Otomasi Google Fonts & Token CSS Dinamis
- `applyThemeStyles()` diperluas untuk mendeteksi seluruh key tipografi (`font_heading`, `font_body`, `font_mono`) dan secara dinamis memanggil `injectGoogleFont()` serta membuat CSS variables `--theme-font-*` dan format HSL `--theme-*-hsl`.
- Token warna utama (`color_primary`), sekunder (`color_secondary`), dan aksen (`color_accent`) tersinkronisasi langsung ke variabel `--primary`, `--secondary`, dan `--accent` pada theme Layung.

### 4. Smart Collision Detection & Auto-Flipping Dropdown ([SelectContent.vue](../../frontend/src/shared/components/ui/SelectContent.vue))
- Mengaktifkan `avoidCollisions: true`, `collisionPadding: 16`, dan `sideOffset: 6` dengan `useForwardPropsEmits` pada Radix Popper.
- Jika ruang di bawah trigger tidak mencukupi, dropdown secara cerdas **membalik arah ke atas (*flip to top*)**.
- Menambahkan scrollbar internal adaptif `max-h-[min(var(--radix-select-content-available-height), 16rem)]` dan memperluas padding scroll container sidebar (`pb-36`).

### 5. Isolasi Kontras & Solid Fallback Popper ([console.css](../../frontend/src/styles/console.css) & [useConsoleTheme.ts](../../frontend/src/modules/Core/System/composables/useConsoleTheme.ts))
- Menetapkan opasitas background popper pada level tinggi/pekat (`opacity >= 0.96` - `0.98`) dan fallback solid `#ffffff` (light) / `#0f172a` (dark) untuk mencegah teks form di bawahnya tembus pandang.

---

## File yang Diubah

| File | Perubahan |
|------|-----------|
| `themes/layung/assets/styles/layung.css` | Implementasi CSS selector 4 Visual Styles, dynamic fonts, button radius, dan shadow tokens |
| `layouts/FrontendLayout.vue` | Binding `themeRootDataAttrs` global ke root layout element |
| `composables/useTheme.ts` | Reaktif cross-frame listener, dynamic token generation, dan font injector |
| `shared/components/ui/SelectContent.vue` | Radix Popper collision detection, auto-flip to top, dan max-height constraint |
| `styles/console.css` & `useConsoleTheme.ts` | High-contrast opaque fallback untuk popper menu & select dropdown |
| `components/themes/customizer/editor/CustomizerEditorCanvas.vue` | Penambahan padding scrollable container `pb-36` |

---

## Konsekuensi

### Positif
- Seluruh perubahan desain (warna, tipografi, gaya visual, tombol) langsung terlihat secara *real-time* di canvas tanpa delay atau reload.
- Dropdown form tidak akan pernah lagi terpotong oleh batas layar atau tertutup oleh taskbar OS.
- Menu dropdown selalu terbaca dengan jelas tanpa kebocoran transparansi pada teks di bawahnya.

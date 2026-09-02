# ADR-007: Hero Section Telemetry Animations, Slider Engine, Dynamic Backgrounds, and Customizer Manifest Isolation

**Status:** Accepted  
**Tanggal:** 2026-09-02  
**Author:** Jejakawan Engineering & K2NET Team  
**Scope:** `frontend/src/modules/Layout/views/themes/layung/` (Hero, HeroVisualAnimation, Customizer schema, bindings)

---

## Konteks

Hero section merupakan pintu masuk utama pengunjung portal K2NET. Terdapat beberapa kebutuhan transformasi dan penyempurnaan penting:
1. **Representasi Core Bisnis K2NET**: Sebelumnya visual hero statis dan kurang merefleksikan identitas K2NET sebagai Internet Service Provider (ISP) dan Managed Service Provider (MSP). Dibutuhkan visual animasi interaktif berteknologi tinggi yang merepresentasikan jaringan backbone, data center, Wi-Fi enterprise, dan peering global.
2. **Kustomisasi Animasi & Background**: Administrator membutuhkan kendali untuk mengaktifkan/menonaktifkan animasi secara live, memilih jenis animasi, mengganti background (preset atau kustom gambar), dan opsi hero slider multi-slide.
3. **Pembersihan Properti Legacy Customizer**: Pada panel kustomisasi Hero di sidebar customizer, sebelumnya muncul field informasi ISP tingkat lanjut (nomor ASN, kapasitas backbone, garansi SLA, alamat kantor Bandung/Garut, dan daftar nomor telepon CS/NOC/Sales). Field ini bukan properti hero dan mengacaukan hierarki kustomisasi.
4. **Ketinggian Proporsional Desktop**: Sebelumnya hero terpotong oleh batasan ketinggian statis `min(64vh, 560px)` sehingga terasa sempit di layar desktop standar 1080p/1440p.

---

## Keputusan

### 1. Komponen Animasi Telemetri Berteknologi Tinggi ([HeroVisualAnimation.vue](../../frontend/src/modules/Layout/views/themes/layung/components/sections/HeroVisualAnimation.vue))
- Mengimplementasikan visual grafis berbasis SVG murni dan akselerasi hardware CSS dengan 4 preset sesuai lini bisnis K2NET:
  - **`network`**: Core BGP Peering Mesh dengan floating telemetry badge `AS153992 · BGP UP`, latensi `1.8ms`, dan `SLA 99.98%`.
  - **`datacenter`**: Tier-3 Server Rack dengan indikator status PSU, pendingin dinamis, dan barisan LED blinker aktif.
  - **`wireless`**: Enterprise Wi-Fi 6/7 Access Point (Omnidirectional beam) dengan telemetri `128 Users | 2.4 Gbps | Roaming`.
  - **`cyber_globe`**: Global IXP Peering Globe dengan lintasan orbit transmisi fiber optik.
- Dikontrol melalui setting `hero_visual_animation_enabled` (boolean) dan `hero_visual_animation_type` (select preset).

### 2. Multi-Slide Hero Carousel & Dynamic Backgrounds ([Hero.vue](../../frontend/src/modules/Layout/views/themes/layung/components/sections/Hero.vue))
- **Fitur Slider**: Didukung setting `hero_slider_enabled` dengan rotasi slide manual atau integrasi artikel warta CMS, durasi interval otomatis (`hero_slider_interval`), serta indikator pagination titik.
- **Preset Background**: Menyediakan 4 preset visual (`cyber_grid`, `mesh_glow`, `datacenter_dark`, `fiber_circuit`) dan dukungan gambar latar kustom (`hero_bg_image`) dengan overlay gradien adaptif.

### 3. Isolasi Manifest Categories di Customizer ([bindings.registry.json](../../frontend/src/modules/Layout/views/themes/layung/customizer/bindings.registry.json))
- Mengubah `manifestCategories` komponen `hero` dari `["Hero Section", "ISP Info"]` menjadi hanya `["Hero Section"]`.
- Hal ini secara bersih mengeliminasi properti alamat kantor, nomor telepon CS/NOC/Sales, ASN, kapasitas backbone, dan SLA dari panel kustomisasi Hero tanpa menghapus field tersebut dari kategori aslinya di tema.
- Menjaga seluruh preset translasi bahasa lokal (`hero_badge_text_id/en/su`, `hero_title_id/en/su`, `hero_subtitle_id/en/su`, `hero_primary_cta_text_id/en/su`, `hero_secondary_cta_text_id/en/su`, dll.) tetap utuh di [schema.settings.json](../../frontend/src/modules/Layout/views/themes/layung/customizer/schema.settings.json) dan [theme.json](../../frontend/src/modules/Layout/views/themes/layung/theme.json).

### 4. Ketinggian Layar Penuh Proporsional Desktop ([layung.css](../../frontend/src/modules/Layout/views/themes/layung/assets/styles/layung.css))
- Mengganti pembatas statis dengan `min-height: max(660px, calc(100dvh - 4.5rem))` dengan `flex-col justify-between`.
- Panggung utama (Headline, Subjudul, Tombol CTA, dan Animasi Grafis) berpusat vertikal (`my-auto py-2`), dan strip berita/promo bersandar rapi di batas bawah viewport desktop tanpa scroll.

---

## File yang Diubah

| File | Perubahan |
|------|-----------|
| `components/sections/HeroVisualAnimation.vue` | [NEW] Komponen animasi telemetri 4 preset (Network, Datacenter, Wireless AP, Cyber Globe) |
| `components/sections/Hero.vue` | Integrasi animasi, slider carousel, dynamic background preset, dan layout full-height proporsional |
| `customizer/bindings.registry.json` | Isolasi `manifestCategories` komponen hero menjadi murni `["Hero Section"]` |
| `customizer/schema.settings.json` | Penambahan skema animasi visual, slider, background preset, dan preservasi preset locale i18n |
| `theme.json` | Default values untuk animasi, slider, background preset, dan preservasi multilingual keys |
| `assets/styles/layung.css` | Styling CSS animasi keyframes, glow filters, dan layout viewport full-height |

---

## Konsekuensi

### Positif
- Tampilan Hero K2NET terlihat modern, berkelas, dan relevan dengan bisnis ISP/MSP.
- Kustomisasi Hero di sidebar customizer terfokus, rapi, dan mudah dikonfigurasi oleh administrator.
- Ketinggian desktop proporsional dan tidak terpotong pada berbagai resolusi layar.

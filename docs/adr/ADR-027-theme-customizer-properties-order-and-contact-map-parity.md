---
status: accepted
scope: layout
date: 2026-09-19
related:
  - ADR-002
  - ADR-006
  - ADR-008
  - ADR-023
  - ADR-024
---

# ADR-027: Theme Customizer Properties Ordering Hierarchy and Contact Map Parity

**Status:** Accepted ✅ implemented (2026-09-19)  
**Tanggal:** 2026-09-19  
**Author:** Jejakawan Core Engineering  
**Scope:** `frontend/src/modules/Layout/customizer/`, `frontend/src/modules/Layout/views/themes/sarangenge/`, `frontend/src/modules/Layout/types/`  
**Related:** ADR-002 (Customizer Page Isolation), ADR-006 (Master Layout Modes), ADR-008 (Navigation Hierarchy), ADR-023 (Theme Packs), ADR-024 (Header Menu Alignment)

---

## 1. Konteks & Latar Belakang

1. **Urutan Properties Panel Customizer Acak**:
   Sebelumnya, urutan form input di panel editor Theme Customizer mengikuti *insertion order* dictionary JavaScript/JSON hasil merge manifest. Hal ini menyebabkan properti tampil tidak beraturan (misal: pengaturan warna atau footer tampil sebelum layout, tombol aksi mendahului navigasi, atau opsi embed mendahului toggle aktif).
2. **Ketiadaan Popup Peta Lokasi di Tema Sarangenge**:
   Pada tema **`layung`** dan **`sareupna`**, ketika pengunjung mengetuk kartu alamat kontak pada halaman Kontak, sistem memunculkan popup modal peta interaktif (`ContactMapModal.vue`) lengkap dengan iframe peta, rute petunjuk arah (*directions*), dan tombol buka di Google Maps. Sementara pada tema **`sarangenge`**, interaksi tersebut sebelumnya hanya memicu pembukaan link tab baru (`window.open`), serta pengaturan `contact_map_*` belum terdaftar lengkap di skema customizer.

---

## 2. Keputusan Arsitektur

### 2.1 Standardisasi Urutan Properti & Bobot Semantik
1. **Atribut `order` pada `ThemeSetting`**:
   Ditambahkan properti opsional `order?: number` dan `group?: string` pada `frontend/src/modules/Layout/types/theme.ts`.
2. **Sorting Deterministik (`useCustomizerNavigation.ts`)**:
   Implementasi helper `sortSettings()` pada composable navigasi customizer:
   - Jika field memiliki atribut `order` numerik, diurutkan menaik (*ascending*).
   - Jika tidak memiliki atribut `order`, sistem menggunakan fallback bobot semantik (*semantic weight*):
     - Bobot 10: Toggle master fitur / halaman (`enable_*`, `is_*`, `show_*`, `active_*`)
     - Bobot 20: Judul & identitas utama (`*_title`, `*_heading`, `*_tagline`)
     - Bobot 30: Deskripsi & subjudul (`*_description`, `*_subtitle`, `*_badge`)
     - Bobot 40: Gaya tata letak & preset (`*_style`, `*_layout`, `*_mode`, `*_alignment`)
     - Bobot 50: Dimensi & ukuran (`*_width`, `*_padding`, `*_radius`, `*_shadow`)
     - Bobot 60: Warna & latar belakang (`*_color`, `*_bg`, type: `color`)
     - Bobot 70: Data konten & integrasi formulir (`*_slug`, `*_form`, `*_hours`, `*_hotline`)
     - Bobot 80: Bagian berulang / daftar bagian (`*_sections`, `home_sections`, repeater)
     - Bobot 90: Tombol aksi & tautan (`*_cta_*`, `*_url`, `*_link`)
     - Bobot 100: Peta & embed media (`*_map_*`, `*_embed`, `*_zoom`)
3. **Pemberian `order` Eksplisit pada Skema Platform (`global.settings.schema.json`)**:
   Kategori `Layout` ditata secara hierarkis:
   - `layout_style` (10) $\rightarrow$ `container_max_width` (20) $\rightarrow$ `boxed_bg_color` (30) $\rightarrow$ `boxed_shadow` (40) $\rightarrow$ `framed_padding` (50) $\rightarrow$ `header_style` (60) $\rightarrow$ `header_sticky` (70) $\rightarrow$ `header_menu_alignment` (80) $\rightarrow$ `nav_style` (90) $\rightarrow$ `header_cta_text` (100) $\rightarrow$ `header_cta_url` (110) $\rightarrow$ `breadcrumb_sticky` (120) $\rightarrow$ `home_sections` (130).
   Hal serupa diterapkan pada kategori `General`, `Appearance`, `Colors`, `Typography`, `Buttons`, `Public Pages`, `Footer`, `Social Media`, dan `Animations`.
4. **Pewarisan Metadata Platform (`mergeThemeSettingsSchema.ts`)**:
   Penggabungan skema platform dan tema disempurnakan dengan shallow-merge per setting (`{ ...pDef, ...theme[key] }`), sehingga bila tema meng-override opsi atau default suatu setting (seperti `home_sections`), atribut `order`, `category`, dan `scope` bawaan platform tetap diwariskan secara aman.
5. **Sub-heading Badge pada Multi-Section Manifest (`CustomizerEditorCanvas.vue`)**:
   Ketika suatu nav item memuat lebih dari satu kategori manifest (misal: gabungan *Appearance* dan *Buttons*), editor canvas menampilkan sub-heading pemisah yang memuat nama kategori dan badge jumlah setting aktif.

### 2.2 Parity Fitur Halaman Kontak Sarangenge (`Contact.vue` & `ContactMapModal.vue`)
1. **Komponen `ContactMapModal.vue`**:
   Dibuat komponen modal khusus tema Sarangenge yang di-*teleport* ke `<body>` dengan standar:
   - Menggunakan token desain khas Sarangenge (`sarangenge-panel`, radius variabel, badge warna teal/amber).
   - Menampilkan alamat dan nama institusi sekolah.
   - Embed iframe Google Maps dengan lazy-loading dan state spinner pemuatan.
   - Tombol aksi: "Buka di Google Maps" dan "Petunjuk arah" (*directions*).
   - Aksesibilitas: `role="dialog"`, `aria-modal="true"`, penutupan via tombol ESC dan klik backdrop, serta pencegahan scrolling latar belakang (*body scroll lock*).
2. **Integrasi di `Contact.vue`**:
   - Menghubungkan alamat institusi ke pembukaan `mapModalOpen = true`.
   - Menambahkan indikator visual "Lihat peta lokasi" dengan ikon `MapPin`.
   - Mengintegrasikan composable `useThemeContactMap(displayAddress)`.
3. **Penyelarasan Skema Customizer Sarangenge**:
   - Menyatukan definisi field `Contact Page` pada `schema.settings.json` dan `theme.json` dengan penomoran urutan bertingkat (`order: 10` hingga `100`).
   - Menyediakan setting: `enable_contact`, `contact_form_slug`, `contact_whatsapp`, `contact_admission_hotline`, `contact_operating_hours`, `contact_map_enabled`, `contact_map_source`, `contact_map_link`, `contact_map_zoom`, `contact_maps_embed_url`.
   - Menambahkan lokalisasi terkait peta kontak pada `id.json`, `en.json`, dan `su.json`.

---

## 3. Konsekuensi & Dampak

1. **Pengalaman Pengguna Panel Customizer**: Properti kini tersusun secara konsisten, intuitif, dan logis dari level layout makro hingga detail mikro komponen di seluruh tema.
2. **Paritas Fungsional Lintas Tema**: Pengunjung tema Sarangenge kini mendapatkan interaktivitas peta lokasi yang setara dengan Layung dan Sareupna tanpa merusak karakteristik desain khas vokasi Sarangenge (SoC terjaga).
3. **Peningkatan Kualitas Kode & Dokumentasi**: Tersedia unit test otomatis di `mergeThemeSettingsSchema.spec.ts` yang memvalidasi pewarisan `order` dan keberadaan setting peta.

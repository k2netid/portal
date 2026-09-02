# ADR-001: Refactor Halaman Tentang Kami (About.vue) — Layung Theme

**Status:** Accepted  
**Tanggal:** 2026-09-02  
**Author:** Jejakawan Engineering  
**Scope:** `frontend/src/modules/Layout/views/themes/layung/`

---

## Konteks

Halaman Tentang Kami (`About.vue`) di Layung theme sebelumnya sangat minimal — hanya menampilkan fallback plain teks tanpa visual hierarchy, tanpa animasi, tanpa konsistensi dengan design system yang sudah dibangun di section lain (Hero, IspBentoSection, SlaGuaranteeSection).

Halaman ini juga tidak mengikuti pola standar yang sudah ada di halaman lain Layung (Services.vue, Pricing.vue, Contact.vue, Tim.vue) dalam hal:
- Integrasi Theme Customizer
- Integrasi Site Builder (BlockRenderer)
- Integrasi CMS Content (cmsBody / ThemeSafeHtml)
- Plugin slot untuk extensibility
- Guard halaman disabled

---

## Keputusan

### 1. Konsistensi Pola Halaman Layung

Setiap halaman publik di Layung theme wajib mengikuti struktur priority layer berikut:

```
1. isEnabled guard  → tampilkan PageDisabled jika page dinonaktifkan
2. hasBuilderBlocks → tampilkan BlockRenderer (Site Builder override)
3. cmsBody          → tampilkan ThemeSafeHtml (CMS content override)  
4. default template → tampilkan static template yang dirancang khusus
```

Ini konsisten dengan Contact.vue, Pricing.vue, Services.vue, Tim.vue.

### 2. Theme Customizer Integration

Setiap halaman publik wajib memiliki:
- `data-ja-customizer-target="<page-slug>"` pada root element
- Setting `enable_<page>` dapat ditambahkan di `schema.settings.json` untuk toggle halaman

Halaman About.vue menggunakan target `"about"` yang sudah terdaftar di `preview.targets.json`.

### 3. Plugin Slot Points

Dua slot extensibility ditambahkan:
- `about-after-hero` — setelah hero section, sebelum konten utama
- `about-before-cta` — sebelum CTA section di akhir halaman

Ini memungkinkan plugin/addon menyuntikkan konten tanpa mengubah file core.

### 4. CSS — Separation of Concerns (SoC)

**Keputusan:** Semua CSS halaman About dipindah ke `layung.css`, **bukan** disimpan dalam `<style scoped>` di komponen Vue.

**Alasan:**
- `layung.css` adalah single source of truth untuk semua visual styling di theme Layung
- `<style scoped>` di page component melanggar SoC — komponen Vue seharusnya hanya menangani template dan logic
- CSS di `layung.css` menggunakan CSS custom properties (token) yang otomatis merespons dark/light mode
- Mudah di-audit, di-override, dan dikelola satu tempat

**Naming convention:** Semua class spesifik halaman About menggunakan prefix `about-` untuk namespace yang jelas dan menghindari konflik. Menggunakan BEM (Block__Element--Modifier) konsisten dengan pola layung yang sudah ada.

### 5. GSAP Animation — Guard Pattern

GSAP animations hanya dijalankan ketika:
1. Halaman aktif (`isEnabled.value === true`)
2. Tidak dalam mode builder override (`hasBuilderBlocks.value === false`)
3. Tidak ada CMS body override (`cmsBody.value` falsy)

Ini mencegah GSAP mencoba menganimasikan elemen yang tidak ada di DOM.

---

## Struktur Halaman Baru

```
About.vue
├── [guard] isEnabled → PageDisabled
├── layung-hero section
│   ├── Breadcrumb
│   ├── badge (layung-status-dot)
│   ├── h1 dengan LayungSplitText (GSAP)
│   ├── subtitle
│   └── hero-stats strip (ASN, IPv4, Wilayah)
│
├── PluginSlot: about-after-hero
│
├── [priority 1] BlockRenderer (Site Builder)
├── [priority 2] ThemeSafeHtml (CMS Body)
├── [priority 3] Default template:
│   ├── Section 2: Identitas Perusahaan
│   │   ├── Narasi sejarah + timeline visual
│   │   └── Entity card (badan usaha, alamat)
│   │
│   ├── Section 3: Visi & Misi
│   │   ├── Vision card (dengan glow radial)
│   │   └── Mission card (numbered list)
│   │
│   ├── Section 4: Identitas Jaringan ASN
│   │   └── 4 stat cards (dark: ASN, IPv4 | light: Wilayah, Lookup)
│   │
│   ├── Section 5: Izin & Keanggotaan
│   │   └── 4 compliance cards (ISP License, APJII, IDNIC, APNIC)
│   │
│   ├── PluginSlot: about-before-cta
│   └── CtaSection
```

---

## File yang Diubah

| File | Perubahan |
|------|-----------|
| `pages/About.vue` | Rewrite lengkap — template 5 section, integrasi customizer/builder/cms/plugins, hapus `<style scoped>` |
| `assets/styles/layung.css` | Tambah section `About Page` di akhir file dengan semua CSS halaman About |
| `locales/id.json` | Tambah keys baru: `foundedHeading`, `timeline[1-3]Year/Text`, `vmBadge`, `vmHeading`, `networkHeading`, `complianceHeading` |
| `locales/en.json` | Sama seperti id.json (English) |
| `locales/su.json` | Sama seperti id.json (Sundanese) |

---

## Konsekuensi

### Positif
- Halaman About konsisten dengan pola semua halaman Layung lainnya
- CSS dapat di-override/dikustomisasi dari layung.css tanpa menyentuh Vue file
- Site Builder dapat menggantikan seluruh konten melalui BlockRenderer
- Plugin/addon dapat menyuntikkan konten melalui PluginSlot
- Animasi GSAP menggunakan tokens yang sama dengan section homepage

### Risiko / Perlu Diperhatikan
- Setting `enable_about` belum ditambahkan ke `schema.settings.json` — halaman akan selalu enabled (default `true`). Dapat ditambahkan jika dibutuhkan.
- CSS di `layung.css` tidak ter-scope — class `about-*` harus selalu menggunakan prefix yang unik untuk menghindari konflik global

---

## Referensi

- [Contact.vue](../frontend/src/modules/Layout/views/themes/layung/pages/Contact.vue) — referensi pola isEnabled, PluginSlot, PageDisabled
- [Services.vue](../frontend/src/modules/Layout/views/themes/layung/pages/Services.vue) — referensi pola 3-priority layer (builder/cms/default)
- [layung.css](../frontend/src/modules/Layout/views/themes/layung/assets/styles/layung.css) — SoC untuk semua styling Layung theme
- [useThemeMotion.ts](../frontend/src/modules/Layout/composables/useThemeMotion.ts) — GSAP wrapper yang respects animation settings
- [preview.targets.json](../frontend/src/modules/Layout/views/themes/layung/customizer/preview.targets.json) — mapping halaman ke customizer nav item
- Commit: `435e605` (first refactor), `<next-commit>` (SoC fix + integrations)

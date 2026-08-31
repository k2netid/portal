# Sarangenge Theme Package

**Sarangenge** (bahasa Sunda: *kembang sarangenge / bunga matahari* sarta *panonpoé isuk-isuk / haneut moyan*) adalah tema resmi institusi pendidikan dan sekolah modern untuk Jejakawan Core Engine — saudara filosofis **Janari** (*fajar*).

## Filosofi & Positioning (Standar Sekolah Modern 2026)

| Dimensi | Janari | Sarangenge |
|---|---|---|
| Makna Sunda | Fajar / awal hari (dawn) | Bunga Matahari & Kehangatan Fajar Pagi (Sunflower & Morning Sun) |
| Filosofi Belajar | Inisiasi, fondasi, studio kreasi | Mekar bersama cahaya, menyerap ilmu, semangat baru tiap pagi |
| Fokus | CMS reference, studio, enterprise brand | Kampus, PPDB / Admissions, kurikulum, prestasi, warta sekolah |
| Palet Visual | Canvas Janari (monochrome/clean) | Scholastic Navy (`#0F172A`), Morning Gold (`#F59E0B`), Laurel Emerald (`#059669`) |
| Layout Utama | Editorial / feature stream | Bento Grid akses cepat 2026, kartu modul, timeline prestasi |
| Tipografi | Inter / Plus Jakarta Sans | Bricolage Grotesque (Heading) + Figtree (Body) |

## Arsitektur Sistem (Setara dengan Standar Janari)

```
sarangenge/
├── theme.json                                  # Manifest + merged settings schema
├── theme.bundle-entry.ts                       # Dynamic theme bundle loader
├── tsconfig.json                               # Local TypeScript config
├── readme.md                                   # Theme documentation
│
├── assets/styles/
│   └── sarangenge.css                          # Theme CSS tokens, Scholastic Dawn palette, Bento styling
│
├── composables/
│   └── useSarangengeIdentity.ts                # Theme identity, hotline, admissions helper
│
├── customizer/                                 # Full Customizer Extension
│   ├── index.ts                                # ThemeCustomizerExtension export
│   ├── schema.settings.json                    # Theme-scoped settings (PPDB, Hero, Bento, dsb.)
│   ├── bindings.registry.json                  # Content bindings (Hero, PPDB, Programs, dsb.)
│   ├── sidebar.navigation.json                 # Kategori reserved sidebar customizer
│   ├── sidebar.pages.json                      # Halaman navigasi khusus customizer
│   ├── preview.targets.json                    # Click-to-edit targets pada live preview
│   └── composables/                            # Filter & setting change handlers
│
├── ui/                                         # Self-contained UI Kit (Theme Host Contract safe)
│   ├── Button.vue, Card.vue, Badge.vue, Input.vue, Textarea.vue
│   ├── Label.vue, Select.vue, Alert.vue, Checkbox.vue, ThemeToggle.vue
│   └── index.ts
│
├── components/
│   ├── layout/                                 # Header (stateful dropdowns, cursor bridge), Footer (akreditasi, warta)
│   ├── sections/                               # Hero, Bento, Programs, Achievements, Facilities, Ekskul, Testimonials, FAQ, CTA
│   ├── blog/                                   # BlogSidebar, PostCard
│   └── shared/                                 # Breadcrumb, PageDisabled, SarangengeSplitText
│
├── pages/                                      # Route Views (ThemePageResolver)
│   ├── Home.vue, About.vue, Blog.vue, Post.vue, Contact.vue, Search.vue
│   ├── Achievement.vue, Solusi.vue, Services.vue, CareerCenter.vue, Pricing.vue, Tim.vue, Page.vue
│
└── locales/                                    # 100% Symmetric 3-Language Bundles
    ├── en.json, id.json, su.json
```

## Fitur Unggulan Website Sekolah 2026

1. **Alur PPDB & Penerimaan Online**:
   - Status pendaftaran buka/tutup dinamis via customizer.
   - Pilihan Jalur Reguler, Jalur Prestasi & Olimpiade, serta Jalur Beasiswa Tahfidz Quran.
   - WhatsApp Admissions Hotline langsung terintegrasi.
2. **Bento Grid Kampus & Navigasi Silky-Smooth**:
   - Menu dropdown dengan *invisible cursor bridge* dan stateful grace period (anti-race condition).
   - Tata letak interaktif untuk akses cepat jadwal, fasilitas, ekskul, dan direktori guru.
3. **Kurikulum Adaptif & Program Unggulan**:
   - Kurikulum Merdeka Riset (P5), Kelas Bilingual & Cambridge IGCSE, serta Laboratorium AI & Robotika.
4. **Galeri Prestasi & Jejak Alumni**:
   - Sorotan medali olimpiade internasional/nasional dan rekam jejak kelulusan PTN/Luar Negeri.
5. **Direktori Guru & Tenaga Kependidikan**:
   - Profil dewan guru bersertifikasi pendidik nasional & Cambridge.
6. **Aksesibilitas & Dual Mode Rendering**:
   - Kontras tinggi ramah pengguna mobile/orang tua siswa.
   - Dukungan penuh `ThemeSafeHtml` dan Visual Page Builder (`BlockRenderer`).

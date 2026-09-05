# ADR-004: Multi-Theme Separation of Concerns (SoC), Eliminasi Cross-Theme Leakage, dan Generalisasi Identitas

- **Status:** Accepted / Implemented — *Extended by [ADR-005](./ADR-005-complete-brand-generalization-smkn6-and-k2net.md)*
- **Tanggal:** 2026-09-05
- **Penulis:** Platform & Theme Architecture Team
- **Konteks Proyek:** Fork SMKN 6 Portal (`ja-core_engine`) — Multi-Theme & Public Layout

---

## 1. Konteks & Permasalahan

Dalam sistem multi-tema portal (`janari`, `sarangenge`, `layung`), ditemukan beberapa titik percampuran (*cross-theme leakage/bleed*) dan ketergantungan statis:

1. **Kebocoran Tampilan Silang (Cross-Theme View Bleed):**
   Pada [themeViewResolver.ts](file:///home/jejakawan/dev/smkn6-portal/frontend/src/modules/Layout/utils/themeViewResolver.ts) dan [ThemePageResolver.vue](file:///home/jejakawan/dev/smkn6-portal/frontend/src/modules/Layout/components/themes/ThemePageResolver.vue), resolver menyertakan seluruh daftar slug tema bawaan `BUNDLED_FRONTEND_THEME_SLUGS` (`['janari', 'layung', 'sarangenge']`) sebagai fallback kandidat pencarian view. Ditambah adanya mekanisme pencocokan fuzzy nama file di seluruh direktori tema manapun. Akibatnya, saat tema Sarangenge aktif, permintaan halaman yang hanya ada di tema Layung (seperti `/pricing/isp`) bocor dan merender tampilan tema Layung alih-alih menampilkan halaman disabled atau 404.

2. **Residual Dynamic Routes Saat Pergantian Tema:**
   Pada router publik ([public.ts](file:///home/jejakawan/dev/smkn6-portal/frontend/src/engine/router/public.ts)), saat tema berganti di sesi browser yang sama, rute dinamis tema lama yang didaftarkan via `router.addRoute()` tetap tersisa di memori tanpa pernah dihapus, menyebabkan rute tema sebelumnya tetap bisa diakses.

3. **Hardcoded Identitas Instansi & Penamaan Spesifik:**
   Beberapa file komponen, seeder database, dan template layout masih memuat nama instansi/organisasi spesifik secara statis (misal `SchoolBentoSection.vue`, `Smkn6ContentSeeder.php`, `Smkn6FacilitiesSeeder.php`, dan string hardcode nama sekolah/perusahaan di shell app, footer, hero, pricing, serta translasi i18n). Hal ini menyulitkan sistem saat digunakan sebagai platform multi-tenant atau di-deploy di berbagai profil situs.

---

## 2. Keputusan Arsitektur

### A. Standarisasi Multi-Theme SoC Berbasis Archetype & Hierarki Parent-Theme
Setiap tema diklasifikasikan berdasarkan peran dan arketipe independen di `theme.json`:
- **`janari` (Universal):** `archetype: "universal"`, `category: "Universal CMS & Editorial"`. Berfungsi sebagai referensi global default instalasi awal yang adaptif terhadap berbagai jenis konten.
- **`sarangenge` (Education):** `archetype: "education_school"`, `category: "Education & Institution"`, `parent_theme: "janari"`. Khusus untuk institusi pendidikan kejuruan dan sekolah.
- **`layung` (Corporate):** `archetype: "corporate_industry"`, `category: "Corporate & Industry (ISP/MSP)"`, `parent_theme: "janari"`. Khusus untuk korporat teknologi, ISP, dan penyedia Managed IT.

### B. Isolasi Mutlak Resolving Tampilan (Strict Hierarchy Resolving)
1. Kandidat slug resolver tema di [themeViewResolver.ts](file:///home/jejakawan/dev/smkn6-portal/frontend/src/modules/Layout/utils/themeViewResolver.ts) dibatasi secara ketat hanya pada:
   $$\text{Candidates} = [\text{activeTheme.slug}, \text{activeTheme.parent\_theme}]$$
2. Menghapus fallback global ke sibling themes (`BUNDLED_FRONTEND_THEME_SLUGS`) dan menghapus pencari fuzzy nama file lintas direktori tema.
3. Pada [public.ts](file:///home/jejakawan/dev/smkn6-portal/frontend/src/engine/router/public.ts), menyimpan remover callback dari `router.addRoute()` (`activeThemeRouteRemovers`) dan mengeksekusinya setiap kali tema aktif berganti sebelum mendaftarkan rute tema baru.
4. Menghapus pemetaan statis `/pricing/isp` dan `/pricing/msp` dari `CORE_PUBLIC_PATH_TO_PAGE` di [FrontendLayout.vue](file:///home/jejakawan/dev/smkn6-portal/frontend/src/modules/Layout/layouts/FrontendLayout.vue).

### C. Generalisasi Penamaan File & Komponen
1. **Frontend:** `SchoolBentoSection.vue` distandardisasi menjadi [BentoSection.vue](file:///home/jejakawan/dev/smkn6-portal/frontend/src/modules/Layout/views/themes/sarangenge/components/sections/BentoSection.vue).
2. **Backend Database Seeders:**
   - `Smkn6ContentSeeder.php` $\rightarrow$ [VocationalProgramsSeeder.php](file:///home/jejakawan/dev/smkn6-portal/backend/Modules/Publishing/app/Database/Seeders/VocationalProgramsSeeder.php).
   - `Smkn6FacilitiesSeeder.php` $\rightarrow$ [VocationalFacilitiesSeeder.php](file:///home/jejakawan/dev/smkn6-portal/backend/Modules/Publishing/app/Database/Seeders/VocationalFacilitiesSeeder.php).
   - Menambahkan `class_alias` di kedua file seeder baru demi backward-compatibility terhadap runner seeder lama.

### D. Dinamisasi Identitas Sistem & Penyelarasan Translasi i18n
1. Seluruh resolusi nama situs mengutamakan cascade dinamis:
   $$\text{Site Name} = \text{Theme Setting} \rightarrow \text{System Store (site\_settings.site\_name)} \rightarrow \text{Generic Fallback}$$
2. Menghapus hardcode brand di [main.ts](file:///home/jejakawan/dev/smkn6-portal/frontend/src/main.ts), [main-public.ts](file:///home/jejakawan/dev/smkn6-portal/frontend/src/main-public.ts), [PreviewArea.vue](file:///home/jejakawan/dev/smkn6-portal/frontend/src/modules/Layout/components/themes/customizer/preview/PreviewArea.vue), [Footer.vue (Janari)](file:///home/jejakawan/dev/smkn6-portal/frontend/src/modules/Layout/views/themes/janari/components/layout/Footer.vue), dan [FoundationSeeder.php](file:///home/jejakawan/dev/smkn6-portal/backend/Modules/Core/database/seeders/System/FoundationSeeder.php).
3. Menyelaraskan seluruh kunci translasi di 3 bahasa (`id.json`, `en.json`, `su.json`) tanpa menyisakan key spesifik institusi atau key sampah.

---

## 3. Konsekuensi & Hasil

1. **Isolasi Tema Terjamin:** Tidak ada lagi kebocoran tampilan, rute, atau komponen antar-tema. Tema Sarangenge hanya memuat komponen miliknya dan fallback Janari jika didefinisikan sebagai parent.
2. **Portabilitas Konten:** Tema dapat dipasang di portal sekolah atau institusi manapun tanpa perlu modifikasi hardcoded string pada level kode sumber.
3. **Kualitas & Stabilitas Kode:**
   - TypeScript type-check (`vue-tsc -b`): 0 error.
   - Unit tests: 45 test files passed, 286 test cases passed (100%).
   - i18n parity check: 4.318 gate keys, 9.038 definitions simetris di 3 bahasa.

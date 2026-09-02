# ADR-006: Master Layout Architecture, 5 Viewport Layout Modes, & Dynamic Homepage Section Toggle

**Status:** Accepted  
**Tanggal:** 2026-09-02  
**Author:** Jejakawan Engineering  
**Scope:** `frontend/src/modules/Layout/`, `frontend/src/modules/Layout/views/themes/layung/`

---

## Konteks

Pada panel **Master Layout** Theme Customizer terdapat opsi tata letak (*Global Layout Style*), navigasi sticky (*Sticky Header* & *Sticky Breadcrumbs*), serta visibilitas section beranda (*Homepage Sections*). Ditemukan beberapa aspek yang membutuhkan penyempurnaan:
1. **Penerapan Layout Style pada Theme Layung**: Pada mode *Boxed*, *Wide*, dan *Framed*, terjadi penumpukan padding horizontal (`px-6 md:px-12 lg:px-16`) di dalam kontainer yang membuat section hero dan bento grid menyempit secara tidak wajar.
2. **Homepage Sections Kurang Reaktif**: Komponen `Home.vue` pada theme Layung merender seluruh section secara statis tanpa membaca pilihan checklist `home_sections` di Theme Customizer.
3. **Sticky Breadcrumbs**: Pilihan toggle `breadcrumb_sticky` belum terhubung ke style `.layung-breadcrumb`.

---

## Keputusan

### 1. 5 Paradigma Global Layout Style ([FrontendLayout.vue](file:///home/jejakawan/dev/k2net-portal/frontend/src/modules/Layout/layouts/FrontendLayout.vue))
- **Full Width (`full`)**: Default mode. Header, Main Content, dan Footer membentang 100% lebar layar.
- **Boxed Layout (`boxed`)**: Seluruh situs dibungkus di dalam kontainer terpusat (`maxWidth: 1200px`) dengan latar luar kontras (`bg-slate-200/60` / dark `bg-slate-950/80`), sudut melengkung `rounded-xl`, border presisi, dan bayangan elegan.
- **Wide Layout (`wide`)**: Serupa dengan Boxed namun dengan lebar pandang ekstra (`maxWidth: 1480px`).
- **Framed Layout (`framed`)**: Situs tampil sebagai floating studio card dengan outer margin/padding (`p-3 sm:p-6 lg:p-8`), sudut melengkung `rounded-2xl`, deep shadow `shadow-2xl`, dan ring border halus.
- **Hybrid Layout (`hybrid`)**: Header & Footer membentang penuh (*Full Width*), sementara area konten utama (*Main Content*) terpusat rapi di dalam container `max-w-7xl`.

### 2. Eliminasi Double-Padding pada Main Content
- Di dalam mode *Boxed*, *Wide*, dan *Framed*, elemen `<main>` menggunakan `flex-1 w-full` tanpa padding berlebih, sehingga komponen section seperti Hero, Bento Grid, dan Footer mengisi lebar frame secara presisi sesuai desain aslinya.

### 3. Reaktivitas Penuh Checklist `home_sections` ([Home.vue](file:///home/jejakawan/dev/k2net-portal/frontend/src/modules/Layout/views/themes/layung/pages/Home.vue))
- `Home.vue` kini membaca `getSetting('home_sections')` dan menyediakan helper `isSectionVisible(key)` untuk mengontrol 8 section utama:
  1. `hero` (Landing Hero & Coverage Checker)
  2. `services` (Bento Infrastructure Grid & Three Business Lines)
  3. `calculator` (Interactive Bandwidth & Speed Simulator)
  4. `sla` (SLA 99.9% Performance Guarantee)
  5. `managed_services` (Managed IT & SOC Enterprise Solutions)
  6. `testimonials` (Enterprise Client Testimonials & Partners)
  7. `faq` (Technical & Provisioning FAQ)
  8. `cta` (Quotation & Urgent NOC Hotline CTA)

### 4. Sticky Header & Sticky Breadcrumbs
- `data-breadcrumb-sticky` diinjeksikan ke root DOM. Jika aktif, breadcrumb menempel halus di posisi `top: 4.5rem` dengan efek frosted glass `backdrop-filter: blur(14px) saturate(180%)`.
- `data-header-sticky` mengontrol sticky navbar pada header utama.

---

## File yang Diubah

| File | Perubahan |
|------|-----------|
| `layouts/FrontendLayout.vue` | Optimalisasi 5 mode tata letak, binding data attributes, dan penghapusan nested double padding |
| `views/themes/layung/pages/Home.vue` | Reaktivitas dinamis `home_sections` untuk 8 section beranda |
| `views/themes/layung/theme.json` | Penyelarasan opsi `home_sections` dan deskripsi skema Layout |
| `customizer/platform/schema/global.settings.schema.json` | Penyelarasan skema global untuk platform customizer |
| `views/themes/layung/assets/styles/layung.css` | Penambahan styling sticky breadcrumb dan layout modifiers |

---

## Konsekuensi

### Positif
- Seluruh 5 varian tata letak (*Full*, *Boxed*, *Wide*, *Framed*, *Hybrid*) terisolasi dengan rapi dan terlihat sangat premium.
- Administrator dapat menyalakan/mematikan section beranda manapun secara live melalui customizer dengan pembaruan instan pada kanvas preview.

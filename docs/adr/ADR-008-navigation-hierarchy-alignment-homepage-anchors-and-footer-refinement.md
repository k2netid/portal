# ADR-008: Navigation Hierarchy Alignment, Homepage Anchor Isolation, and Compact Footer Refinement

**Status:** Accepted  
**Tanggal:** 2026-09-03  
**Author:** Jejakawan Engineering & K2NET Team  
**Scope:** `frontend/src/engine/router/`, `frontend/src/modules/Layout/views/themes/layung/`, `backend/database/`

---

## Konteks

Ditemukan ketidakteraturan pada link navigasi header, target hash scroll di halaman beranda, dan tata letak footer:
1. **Target Scroll Homepage Tertukar**: Komponen `ManagedServicesSection.vue` memuat `<span id="isp">` dan `id="layanan"`. Ketika pengunjung mengklik menu *Layanan* atau *Internet* (`/#isp`), browser meloncat melewati ringkasan layanan (Bento grid) dan langsung menuju ke bagian *Managed Services*. Sementara itu, komponen `IspBentoSection.vue` tidak memiliki ID jangkar.
2. **Submenu Menyesatkan**: Di bawah menu *Layanan*, terdapat item *"Produk IT"* yang mengarah ke `/contact` (halaman form kontak), membingungkan pengguna yang mengharapkan informasi produk atau katalog perangkat.
3. **Route `/services` Ter-redirect**: Di `public.ts`, route `/services` sempat ter-hardcode memuat `pages/Solusi` sehingga tidak membuka `pages/Services.vue`.
4. **Footer Terlalu Padat**: Footer menampilkan blok kontak dan alamat kantor yang redundant karena sudah ada di halaman Kontak dan Tentang Kami, garis bawah pada wave SVG footer mengganggu estetika, dan tagline sempat menampilkan teks SLA statis.

---

## Keputusan

### 1. Isolasi Jangkar Section Beranda ([Home Sections](file:///home/jejakawan/dev/k2net-portal/frontend/src/modules/Layout/views/themes/layung/components/sections/))
- **[IspBentoSection.vue](file:///home/jejakawan/dev/k2net-portal/frontend/src/modules/Layout/views/themes/layung/components/sections/IspBentoSection.vue)**: Ditetapkan sebagai jangkar utama layanan beranda dengan atribut `id="layanan"` serta span pembantu `<span id="services">`, `<span id="isp">`, dan `<span id="bento">` dengan kelas `scroll-mt-24`.
- **[ManagedServicesSection.vue](file:///home/jejakawan/dev/k2net-portal/frontend/src/modules/Layout/views/themes/layung/components/sections/ManagedServicesSection.vue)**: Dibersihkan secara total dari tag `id="isp"` dan `id="layanan"`. Ditetapkan khusus untuk Managed Services dengan `id="msp"` serta span `<span id="solusi">` dan `<span id="managed-services">`.
- **Section Lainnya**: Diberikan ID definitif dan jarak scroll seragam (`scroll-mt-24`):
  - `SpeedCalculatorSection`: `id="calculator"` dan `<span id="simulator">`
  - `SlaGuaranteeSection`: `id="sla"` dan `<span id="guarantee">`
  - `TestimonialsSection`: `id="klien"` dan `<span id="testimonials">`
  - `FaqSection`: `id="faq"`
  - `CtaSection`: `id="cta"` dan `<span id="konsultasi">`

### 2. Hierarki Navigasi Header yang Presisi ([Header.vue](file:///home/jejakawan/dev/k2net-portal/frontend/src/modules/Layout/views/themes/layung/components/layout/Header.vue))
- **Beranda** (`/`)
- **Tentang Kami** (`/about`)
- **Layanan** (Dropdown induk menuju `/services`):
  - **Internet (ISP)** → `/pricing/isp` (Halaman detail paket Dedicated & Broadband Internet)
  - **Managed Services (MSP)** → `/solusi` (Halaman Managed IT Services, SOC, & Infrastruktur)
  - **SLA & Jaringan** → `/achievement` (Halaman Komitmen SLA Kontrak, Peering AS153992, & IDNIC)
- **Paket & Harga** (Dropdown induk menuju `/pricing`):
  - **Paket Internet** → `/pricing/isp`
  - **Paket MSP** → `/pricing/msp`
- **Berita** → `/blog`
- **Kontak** → `/contact`
- Pada desktop, dropdown otomatis menutup saat tautan diklik atau saat rute berpindah (`watch route.fullPath`).

### 3. Pemetaan Router `/services` ([public.ts](file:///home/jejakawan/dev/k2net-portal/frontend/src/engine/router/public.ts))
- Mengubah konfigurasi rute `services` agar memuat `pages/Services` secara tepat untuk tema Layung (`Services.vue` berisi detail konektivitas fiber, topologi jaringan, dan bento layanan).

### 4. Penyederhanaan & Perapihan Footer ([Footer.vue](file:///home/jejakawan/dev/k2net-portal/frontend/src/modules/Layout/views/themes/layung/components/layout/Footer.vue))
- Menggantikan blok *Hubungi Kami* di kolom 5 dengan dock ikon media sosial interaktif (`effectiveSocialLinks`).
- Menghapus daftar kontak berulang, mempertahankan hanya alamat kantor Bandung dengan format ringkas.
- Menghapus garis border visual di bawah SVG wave footer.
- Memastikan tagline perusahaan menampilkan `displayTagline` dinamis.
- Mengganti tautan duplikat di kolom 1 footer menjadi tautan interaktif *Simulator Bandwidth* (`/#calculator`).

### 5. Sinkronisasi Database Menu
- Mengupdate data tabel `menu_items` pada database staging secara live untuk menu `layung-sample-header` dan `layung-sample-footer_col_1`, serta menyelaraskan file [bundle.json](file:///home/jejakawan/dev/k2net-portal/frontend/src/modules/Layout/views/themes/layung/sample-data/bundle.json).

---

## File yang Diubah

| File | Perubahan |
|------|-----------|
| `engine/router/public.ts` | Mengarahkan route `services` ke `pages/Services` |
| `components/sections/IspBentoSection.vue` | Menetapkan jangkar `layanan`, `services`, `isp`, dan `bento` |
| `components/sections/ManagedServicesSection.vue` | Menghapus pembajakan jangkar `isp`/`layanan`, menetapkan `msp`/`solusi` |
| `components/sections/SpeedCalculatorSection.vue` | Menambahkan jangkar `calculator`/`simulator` |
| `components/sections/SlaGuaranteeSection.vue` | Menambahkan jangkar `sla`/`guarantee` |
| `components/sections/TestimonialsSection.vue` | Menambahkan jangkar `klien`/`testimonials` |
| `components/sections/FaqSection.vue` | Menambahkan jangkar `faq` |
| `components/sections/CtaSection.vue` | Menambahkan jangkar `cta`/`konsultasi` |
| `components/layout/Header.vue` | Penyelarasan item navigasi default dan penutupan dropdown reaktif |
| `components/layout/Footer.vue` | Kompaksi layout, integrasi ikon sosial, pembersihan garis wave, dan koreksi tagline |
| `sample-data/bundle.json` | Penyelarasan sampel menu header dan footer |

---

## Konsekuensi

### Positif
- Setiap menu dan tombol di portal K2NET kini mengarah secara logis, presisi, dan sesuai ekspektasi pengunjung.
- Scroll animasi antar-bagian di halaman beranda bekerja mulus dengan offset clearance sticky header yang pas.
- Footer tampil jauh lebih bersih, modern, dan tidak membebani tampilan bawah halaman.

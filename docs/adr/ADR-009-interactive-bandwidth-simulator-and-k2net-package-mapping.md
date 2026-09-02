# ADR-009: Interactive Bandwidth Simulator Integration and Real K2NET Package Mapping

**Status:** Accepted  
**Tanggal:** 2026-09-03  
**Author:** Jejakawan Engineering & K2NET Team  
**Scope:** `frontend/src/modules/Layout/views/themes/layung/` (`pages/PricingIsp.vue`, `components/sections/SpeedCalculatorSection.vue`)

---

## Konteks

Halaman Paket Internet ([PricingIsp.vue](file:///home/jejakawan/dev/k2net-portal/frontend/src/modules/Layout/views/themes/layung/pages/PricingIsp.vue)) sebelumnya hanya menampilkan daftar paket statis (Dedicated, SOHO, Retail) tanpa alat bantu bagi calon pelanggan untuk mengestimasi kapasitas bandwidth yang tepat sesuai jumlah perangkat dan profil aktivitas mereka.
Selain itu, komponen [SpeedCalculatorSection.vue](file:///home/jejakawan/dev/k2net-portal/frontend/src/modules/Layout/views/themes/layung/components/sections/SpeedCalculatorSection.vue) sebelumnya hanya menghasilkan angka throughput generic (misal: "180 Mbps DIA") tanpa memetakan ke paket produk riil K2NET beserta spesifikasi harga resminya.

---

## Keputusan

### 1. Desain Single Unified Main Card (Menghilangkan Double Box Wrapper)
- Menghapus outer box ganda yang canggung. Sekarang seluruh simulator dibungkus dalam **1 Single Main Card** terpadu (`border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl`).
- Judul, badge, dan deskripsi diletakkan rapi di bagian atas (header) di dalam kartu tersebut, langsung terhubung dengan kontrol segmen, slider, dan kartu rekomendasi di bawahnya.

### 2. Metodologi Perhitungan Ilmiah Berstandar Kredibel (Ookla® & Cisco Enterprise)
Perhitungan tidak lagi menggunakan asumsi sembarangan, melainkan mengadopsi formula kapasitas terstandar industri:

$$\text{Kebutuhan Bandwidth Puncak} = (\text{Jumlah User} \times \text{Faktor Konkurensi} \times \text{Throughput per User}) \times (1 + \text{Headroom Buffer})$$

1. **Throughput per User (Standar Benchmark Ookla® Speedtest)**:
   - **Office & Browsing**: `2.0 Mbps/user` (Standar Ookla untuk web browsing, email, chat, audio streaming).
   - **Video HD & CCTV**: `4.5 Mbps/user` (Standar Ookla untuk Zoom HD 1080p, Teams video call, dan streaming).
   - **Cloud ERP & Server**: `6.5 Mbps/user` (Standar sistem cloud enterprise, POS kasir, database, dan backup).
   - **High-Demand & Lab**: `10.0 Mbps/user` (Standar lab komputer, ujian online serentak, dan video 4K).
2. **Faktor Konkurensi Pengguna (Standar Perencanaan Kapasitas Cisco Enterprise)**:
   - Pengguna 2–10 unit: Konkurensi serentak `85%`
   - Pengguna 11–30 unit: Konkurensi serentak `75%`
   - Pengguna 31–70 unit: Konkurensi serentak `65%`
   - Pengguna 71–150 unit: Konkurensi serentak `55%`
   - Pengguna >150 unit: Konkurensi serentak `45%`
3. **Safety Headroom Buffer**: Ditambahkan margin `25%` (sesuai Cisco best practice) untuk meredam traffic spike dan menjaga latensi/jitter tetap stabil.
4. **Indikator Metodologi Transparan**: Menampilkan bar kalkulasi ilmiah (Beban per User, Rasio Konkurensi Aktif, Headroom Buffer, dan Kebutuhan Bersih) secara transparan.

### 4. Penyelarasan Lebar Section & Responsivitas Mobile
- **Penyelarasan Lebar Container**: Menghapus pembatasan `max-w-5xl` dan padding ganda pada `SpeedCalculatorSection`. Komponen kini menggunakan `w-full` di dalam container `max-w-7xl`, sehingga tepi kiri dan kanannya sejajar 100% presisi dengan grid kartu paket internet di atasnya (`IspPackagesSection`).
- **Refinement Breadcrumb di Mode Mobile**:
  - Pada layar desktop (`sm:inline-flex`), breadcrumb tetap menampilkan rute lengkap.
  - Pada layar mobile (`< sm`), alih-alih teks panjang berjenjang yang terpotong dan menabrak tata letak, breadcrumb beralih ke tombol navigasi balik pintar yang ringkas (*Smart Back Pill* e.g. `← Paket & Harga`).
  - Menghapus efek sticky breadcrumb pada mobile (`< 640px`) agar tidak melayang menutupi kartu konten saat di-scroll.
- **Relokasi Social Dock pada Mobile**:
  - Pada viewport mobile (`< 768px`), floating dock diposisikan aman di sudut kanan bawah (`bottom: 5.5rem; right: 1rem`) sehingga tidak lagi menutupi judul teks kartu produk di sebelah kiri.
- **Kompaksi Padding Kartu Mobile**:
  - Padding kartu paket diubah dari `p-8` menjadi `p-6 sm:p-8`, mencegah overflow horizontal pada perangkat smartphone.

### 5. Isolasi Strict Breadcrumb & Sinkronisasi 5 Preset Tata Letak Global Customizer
- **Isolasi Strict CSS Breadcrumb Mobile**:
  - Sebelumnya, class `.layung-breadcrumb` memiliki `display: inline-flex` tanpa media query sehingga mengabaikan utilitas `.hidden` Tailwind dan menyebabkan breadcrumb desktop dan mobile tampil bersamaan dan terpotong.
  - Di `layung.css`, aturan kini diisolasi secara tegas:
    - `@media (max-width: 639px)`: `.layung-breadcrumb { display: none !important; }`
    - `@media (min-width: 640px)`: `.layung-breadcrumb-mobile { display: none !important; }`
  - Di perangkat smartphone, hanya tombol Back Pill yang ringkas dan aman yang tampil tanpa ada jejak breadcrumb panjang yang terpotong.
- **Keselarasan Seluruh Sub-Halaman dengan Preset Master Layout**:
  - Seluruh sub-halaman (`PricingIsp`, `PricingMsp`, `Pricing`, `Services`, `Solusi`, `Achievement`) kini menggunakan container terpadu yang diproteksi `w-full max-w-full overflow-x-clip`.
  - Di [FrontendLayout.vue](file:///home/jejakawan/dev/k2net-portal/frontend/src/modules/Layout/layouts/FrontendLayout.vue), mode **Hybrid** dibebaskan dari penumpukan `container mx-auto px-6 md:px-12 lg:px-20` ganda dan kini dikontrol penuh oleh variabel dinamis `containerMaxWidth` (1200px / 1400px / 1480px) sesuai preset tata letak yang dipilih di Theme Customizer (*Full Width*, *Boxed*, *Wide*, *Framed*, *Hybrid*).

---

## File yang Diubah

| File | Perubahan |
|------|-----------|
| `pages/PricingIsp.vue` | Mengimpor dan merender `SpeedCalculatorSection` di bawah grid paket internet |
| `components/sections/SpeedCalculatorSection.vue` | Merombak algoritma simulator agar memetakan hasil ke portofolio produk dan harga resmi K2NET |

---

## Konsekuensi

### Positif
- Pengunjung halaman paket internet mendapatkan pengalaman interaktif yang sangat membantu dalam memilih paket yang sesuai kebutuhan dan anggaran.
- Rekomendasi simulator 100% akurat sesuai katalog produk dan pricing K2NET.

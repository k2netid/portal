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

### 3. Fitur Interaktif Tambahan
- **Filter Segmen Penggunaan**: Opsi *Otomatis*, *Rumah & Retail*, *Bisnis SOHO*, dan *Dedicated DIA*.
- **Tombol Preset Perangkat Cepat**: Tombol pilihan langsung (3 unit keluarga, 15 unit ruko, 35 unit kantor menengah, 120+ unit kampus).
- **Seleksi Beban Kerja**: Pilihan aktivitas dominan (*Office & Browsing*, *Video HD & CCTV*, *Cloud ERP & Server*, *High-Demand & Lab*).
- **Tautan CTA Dinamis**: Tombol pemesanan langsung mengarah ke `/contact?plan=...` dengan parameter paket yang dipilih.

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

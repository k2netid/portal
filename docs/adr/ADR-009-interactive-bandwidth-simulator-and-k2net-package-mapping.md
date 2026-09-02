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

### 1. Integrasi Simulator ke Halaman Paket & Harga Internet ([PricingIsp.vue](file:///home/jejakawan/dev/k2net-portal/frontend/src/modules/Layout/views/themes/layung/pages/PricingIsp.vue))
- Menyematkan `<SpeedCalculatorSection />` tepat di bawah daftar paket (`#packages`) dan di atas FAQ pada halaman Paket Internet.
- Memungkinkan pengunjung memvalidasi pilihan paket secara interaktif langsung di halaman penentuan harga.

### 2. Pemetaan Cerdas ke Portofolio Paket Riil K2NET
Simulator kini secara otomatis merekomendasikan paket produk resmi K2NET:
- **Retail Broadband**:
  - **Retail 10**: 10 Mbps (up to 15 Mbps), Rp 150.000 + PPN / bln (2–5 perangkat).
  - **Retail 15**: 15 Mbps (up to 20 Mbps), Rp 200.000 + PPN / bln (5–8 perangkat, Populer).
  - **Retail 20**: 20 Mbps (up to 25 Mbps), Rp 250.000 + PPN / bln (8–15 perangkat).
- **Broadband Bisnis SOHO**:
  - **SOHO 50**: Up to 50 Mbps, Mulai Rp 1.200.000 + PPN / bln, 1 IP Publik Statis (10–30 perangkat ruko/kantor kecil).
  - **SOHO 100**: Up to 100 Mbps, Mulai Rp 2.000.000 + PPN / bln, 1–2 IP Publik Statis, prioritas jam operasional (30–60 perangkat).
- **Dedicated Internet Access (DIA)**:
  - Bandwidth simetris 1:1 murni (50 Mbps – 1+ Gbps), IP Publik Statis sesuai kebutuhan, monitoring NOC 24/7, SLA kontrak resmi untuk kantor pusat, kampus, sekolah, dan kompleks institusi.

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

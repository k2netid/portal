# ADR-009: Simulator bandwidth internal dan pemetaan paket K2NET

**Status:** Accepted (dikoreksi 2026-09-03)  
**Tanggal:** 2026-09-03  
**Author:** Jejakawan Engineering & K2NET Team  
**Scope:** `frontend/src/modules/Layout/views/themes/layung/` (`pages/PricingIsp.vue`, `components/sections/SpeedCalculatorSection.vue`, `composables/layungBandwidthEstimate.ts`)

---

## Konteks

Halaman Paket Internet (`PricingIsp.vue`) hanya menampilkan daftar paket statis (Dedicated, SOHO, Retail) tanpa alat bantu untuk memperkirakan kapasitas. Simulator sebelumnya mengeluarkan angka throughput generic tanpa memetakan ke rentang paket K2NET.

Versi awal ADR ini mengklaim metodologi Ookla®, Cisco SBA, dan (di dokumen audit) ITU-T Y.1541. **Klaim itu tidak sesuai kode.** Koreksi ini menyatakan formula sebagai estimasi internal.

---

## Keputusan

### 1. Satu kartu simulator

Seluruh kontrol dan hasil ada dalam satu kartu (`border border-slate-800 rounded-3xl`), tanpa wrapper luar ganda.

### 2. Estimasi internal (bukan standar pihak ketiga)

Rumus di `layungBandwidthEstimate.ts`:

```
peak Mbps = max(10, round(concurrentUsers × MbpsPerDevice × 1.25))
concurrentUsers = max(1, round(userCount × concurrencyRatio))
```

Asumsi beban (knob perencanaan, bukan benchmark merek):

- Office & browsing: `2.0 Mbps/perangkat`
- Video HD & CCTV: `4.5 Mbps/perangkat`
- Cloud ERP & server: `6.5 Mbps/perangkat`
- High-demand & lab: `10.0 Mbps/perangkat`

Rasio pemakaian bersamaan (knob internal):

- 2–10: 85%
- 11–30: 75%
- 31–70: 65%
- 71–150: 55%
- >150: 45%

UI wajib menyebut **estimasi internal**, menampilkan tarif sebagai **indikasi**, dan menyatakan hasil **bukan quotation**. Nama merek pihak ketiga tidak boleh dipakai di UI atau komentar seolah-olah lisensi/standar resmi.

Pemetaan ke `retail-10/15/20`, `soho-50/100`, atau `dia` adalah heuristik produk, bukan jaminan ketersediaan atau harga.

### 3. Lebar section, breadcrumb mobile, dock

- Simulator memakai `w-full` di dalam `max-w-7xl` agar sejajar grid paket.
- Breadcrumb mobile memakai back-pill; sticky breadcrumb dimatikan di `< 640px`.
- Dock sosial di mobile diposisikan `bottom: 5.5rem; right: 1rem`.

### 4. Isolasi CSS breadcrumb vs Tailwind

Di `layung.css`:

- `@media (max-width: 639px)`: `.layung-breadcrumb { display: none !important; }`
- `@media (min-width: 640px)`: `.layung-breadcrumb-mobile { display: none !important; }`

### 5. Subhalaman full-width (bukan double-box)

Root subpage: `w-full overflow-x-clip`. Tiap section punya satu container `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`. `FaqSection` memakai lebar itu; akordeon dalam tetap `max-w-4xl`.

---

## File yang diubah

| File | Perubahan |
|------|-----------|
| `pages/PricingIsp.vue` | Merender `SpeedCalculatorSection` di bawah grid paket |
| `composables/layungBandwidthEstimate.ts` | Rumus + pemetaan plan id (sumber tes) |
| `components/sections/SpeedCalculatorSection.vue` | UI, copy estimasi, indikasi tarif |

---

## Konsekuensi

### Positif

- Pengunjung mendapat perkiraan rentang paket sebelum menghubungi sales.
- Klaim ilmiah dan merek pihak ketiga tidak lagi menyesatkan.

### Risiko

- Angka tarif di UI masih hardcoded dan harus dikonfirmasi operasional.
- Heuristik mapping bisa salah untuk profil beban nyata; CTA mengarah ke `/contact`.

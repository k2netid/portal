# ADR-013: Workspace Tampilan Konsol Dual-Mode (Easy vs Advanced) dan Komponen Slider

**Status:** Accepted / Implemented  
**Tanggal:** 2026-09-07  
**Author:** Jejakawan Engineering  
**Scope:** `frontend/src/modules/Core/System/views/settings/appearance/AppearanceSettings.vue`, `frontend/src/shared/components/ui/Slider.vue`, `frontend/src/shared/components/ui/ConfirmDialog.vue`, `frontend/src/shared/stores/theme.ts`, `backend/Modules/Core/app/System/Http/Controllers/AppearanceController.php`  
**Supersedes / Extends:** Console Appearance Architecture & Design Token Subsystem  

---

## 1. Konteks & Permasalahan

Pengaturan tampilan antarmuka konsol manajemen (`/dash/system/appearance`) dirancang untuk memberikan kendali kustomisasi visual bagi administrator sistem.

### Masalah pada Antarmuka Sebelumnya:
1. **Beban Kognitif Berlebih (Cognitive Overload)**:
   Antarmuka sebelumnya menampilkan puluhan parameter token desain CSS (border-radius, elevasi bayangan, skala tipografi, saturasi warna aksen, hingga margin/padding spacing density) dalam satu halaman panjang tanpa kategorisasi tingkat kemahiran pengguna.
2. **Resiko Kerusakan Tata Letak oleh Pengguna Biasa**:
   Administrator non-teknis yang hanya ingin mengganti tema gelap/terang atau memilih warna dasar seringkali secara tidak sengaja menggeser slider token densitas atau radius, merusak konsistensi tata letak konsol.
3. **Komponen Rentang Bawaan yang Buruk (Native Range Input Limitations)**:
   Elemen `<input type="range">` bawaan peramban memiliki kontras yang sangat rendah pada tema gelap, tidak memiliki visual *filled progress track* (jalur terisi), dan tidak memberikan umpan balik interaktif saat disentuh.
4. **Ketiadaan Pembatasan Lisensi pada Fitur Tingkat Lanjut**:
   Kemampuan mengekspor/mengimpor paket tema JSON (*Theme Portability*) dan kustomisasi logo konsol belum diproteksi oleh lisensi komersial Enterprise / Pro, padahal fitur tersebut merupakan nilai tambah komersial platform.

---

## 2. Keputusan Arsitektur

### A. Segregasi Ruang Kerja Dual-Mode (Easy Mode vs Advanced Mode)
Halaman pengaturan tampilan dipecah menjadi dua mode kerja dengan fokus yang jelas:

1. **Easy Mode (Mode Mudah - Default)**:
   - **Target Pengguna**: Seluruh administrator dan pengguna umum.
   - **Fitur yang Tersedia**:
     - Pemilihan Mode Tema: *Light* (Terang), *Dark* (Gelap), atau *System* (Mengikuti Peramban).
     - Preset Warna Cepat: Palet warna kurasi siap pakai (*Emerald*, *Sapphire*, *Indigo*, *Rose*, *Amber*, *Slate*).
   - Antarmuka ringkas, bersih, dan bebas dari konfigurasi token rumit.

2. **Advanced Mode (Mode Tingkat Lanjut)**:
   - **Target Pengguna**: Desainer UI/UX, pengembang sistem, dan integrator korporat.
   - **Fitur yang Tersedia**:
     - Kontrol Granular Token Desain: Skala Border Radius (`--radius-sm`, `--radius-md`, dll), Elevasi Bayangan (*Shadow Elevation*), Skala Tipografi (*Font Scale Ratio*), Spacing Density (*Compact*, *Normal*, *Spacious*).
     - Color Presets & Custom HSL Token Overrides.
     - Custom CSS Code Editor dengan validasi sintaks.
     - Pratinjau Visual Komponen Interaktif (*Live Component Playground*).

---

### B. Dialog Konfirmasi Peralihan Mode
Untuk memastikan pengguna menyadari implikasi dari perubahan mode kerja:
- Peralihan dari *Easy Mode* ke *Advanced Mode* memicu `ConfirmDialog.vue`.
- Dialog memberikan penjelasan bahwa mode tingkat lanjut membuka token desain mendalam dan meminta konfirmasi sebelum mengaktifkan workspace tersebut.
- Fitur "Reset ke Default" juga dilengkapi dialog konfirmasi defensif untuk mencegah hilangnya kustomisasi secara tidak sengaja.

---

### C. Komponen Kustom `Slider.vue` Berkontras Tinggi
Dibuat komponen UI baru `Slider.vue` (`frontend/src/shared/components/ui/Slider.vue`):
- **Filled Progress Track**: Menampilkan indikator persentase terisi dengan warna aksen primer yang menyala, memberikan kejelasan visual posisi nilai slider.
- **Hover & Focus Glow**: Thumb slider memiliki animasi mikro dan cincin fokus (*focus ring*) yang mudah diakses melalui keyboard maupun sentuhan.
- **Nilai Numerik Real-Time**: Menampilkan angka nilai saat ini dan satuan (misalnya `px`, `%`, atau `rem`) secara langsung.

---

### D. Proteksi Lisensi Komersial (License Gating)
Fitur tingkat lanjut dilindungi oleh sistem lisensi:
1. **Portabilitas Tema (Import/Export JSON)**:
   - Fitur ekspor dan impor skema tampilan diproteksi oleh pengecekan lisensi `licenseService.hasFeature('theme_portability')`.
   - Pengguna dengan lisensi Standar/Komunitas diberikan lencana (*badge*) informasi tier dan tombol aksi dalam status terkunci anggun.
2. **Kustomisasi Aset Logo Konsol**:
   - Dibatasi untuk pemegang lisensi Enterprise / White-Label.

---

## 3. Konsekuensi & Verifikasi

### Keuntungan Arsitektur:
- **Ergonomi Pengguna Unggul**: Pengguna awam mendapatkan pengalaman yang sangat cepat dan aman di Easy Mode, sementara pengguna mahir memiliki kebebasan penuh di Advanced Mode.
- **Aksesibilitas & Kualitas Visual**: Komponen slider kustom memenuhi standar kontras WCAG 2.1 AA pada tema terang maupun gelap.
- **Komersialisasi Bernilai Tambah**: Memberikan diferensiasi fitur yang jelas untuk lisensi tier Pro dan Enterprise.

### Verifikasi:
- Uji alur antarmuka: Beralih dari Easy ke Advanced Mode $\to$ Dialog konfirmasi muncul $\to$ Saat disetujui, panel token desain terbuka mulus.
- Uji komponen Slider: Geser track $\to$ Nilai dan visual progress track terupdate secara real-time dan reaktif.
- Uji pembatasan lisensi: Verifikasi tombol ekspor/impor terkunci saat fitur lisensi dimatikan dan aktif saat lisensi Enterprise diterapkan.
- Lulus pemeriksaan `npm run i18n:check` dan `vue-tsc -b`.

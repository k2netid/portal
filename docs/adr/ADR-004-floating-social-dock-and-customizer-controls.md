# ADR-004: Floating Social Dock Architecture, GSAP Animation Flow, & Theme Customizer Integration

**Status:** Accepted  
**Tanggal:** 2026-09-02  
**Author:** Jejakawan Engineering  
**Scope:** `frontend/src/modules/Layout/views/themes/layung/`, `frontend/src/modules/Layout/customizer/`

---

## Konteks

Sebelumnya pada Theme Layung:
1. **Tidak Ada Fitur Social Floating**: Tombol media sosial hanya ada di footer statis dan belum memiliki floating dock interaktif seperti yang ada pada theme Janari (second header).
2. **Keterbatasan Pengaturan Customizer**: Di Theme Customizer, pengaturan sosial hanya sebatas menambah link repeater tanpa ada opsi pengaturan visibilitas, posisi docking, default collapsed, maupun visibilitas di mobile.
3. **Animasi & Transisi**: Dibutuhkan animasi collapse/expand yang presisi menggunakan GSAP tanpa merusak tata letak tooltip atau memicu distorsi ikon.

---

## Keputusan

### 1. Komponen Floating Social Dock (`FloatingSocialDock.vue`)
- Mengembangkan komponen modular `FloatingSocialDock.vue` dengan 4 orientasi posisi docking:
  - `right` (Default: Kanan tengah layar)
  - `left` (Kiri tengah layar)
  - `bottom_right` (Kanan bawah layar)
  - `bottom_left` (Kiri bawah layar)
- Tooltip dinamis dengan auto-flip: ke arah kiri jika dock di kanan, dan ke arah kanan jika dock di kiri.
- Tombol trigger collapse floating (*Share Pill*) dengan ikon chevron yang membalik secara otomatis (`rotate-180`).

### 2. Animasi Halus & Presisi dengan GSAP
- Memanfaatkan **GSAP Timeline (`gsap.timeline()`)** untuk mengontrol buka/tutup dock secara terstruktur:
  - **Expand**: Mengatur `scale: 1`, `opacity: 1`, dan stagger halus per item (`stagger: 0.04s`, `ease: 'power2.out'`).
  - **Collapse**: Mengecilkan item secara bertahap (`scale: 0.8`, `opacity: 0`), kemudian menyembunyikan kontainer body dan memunculkan tombol toggle share.
- Penanganan `cleanUp` dan `kill()` pada instance GSAP saat komponen unmounted untuk mencegah kebocoran memori.

### 3. Integrasi Penuh ke Theme Customizer
- Menambahkan skema kontrol baru di `global.settings.schema.json` dan `theme.json`:
  - `enable_floating_social` (*Boolean Switch*): Aktifkan/nonaktifkan floating dock.
  - `floating_social_position` (*Select*): Pilihan posisi `right`, `left`, `bottom_right`, `bottom_left`.
  - `floating_social_default_collapsed` (*Boolean Switch*): Keadaan awal tertutup/terbuka.
  - `floating_social_show_on_mobile` (*Boolean Switch*): Opsi tampilan pada layar mobile (< 768px).
  - `label` (*Text*) pada tiap item repeater `social_links` untuk kustomisasi nama tooltip per platform.

### 4. Presisi Warna & Kontras Mode Gelap/Terang
- **Light Mode**: Latar putih bersih (`#ffffff`), border halus (`#e2e8f0`), bayangan `0 8px 24px -4px rgba(0, 0, 0, 0.1)`.
- **Dark Mode**: Latar slate gelap pekat (`#0f172a`), border slate (`#334155`), bayangan gelap `rgba(0, 0, 0, 0.7)` dengan aksen glow halus Cyan K2NET.
- Ikon brand (`WhatsApp`, `Instagram`, `Facebook`, `LinkedIn`, `YouTube`, `Twitter`, `GitHub`, dll.) dirender tajam dengan warna brand masing-masing saat hover.

---

## File yang Diubah

| File | Perubahan |
|------|-----------|
| `themes/layung/components/layout/FloatingSocialDock.vue` | Komponen floating social dock dengan GSAP timeline dan 4-way docking |
| `themes/layung/assets/styles/layung.css` | Styling dock, hover effects, dark mode tokens, dan position modifier classes |
| `themes/layung/theme.json` | Penambahan skema `Social Media` lengkap dengan opsi posisi dan visibilitas |
| `customizer/platform/schema/global.settings.schema.json` | Penyelarasan skema global untuk platform customizer |
| `themes/layung/customizer/preview.targets.json` | Penambahan target highlight canvas `social_links` |

---

## Konsekuensi

### Positif
- Media sosial portal dapat diakses pengunjung dari halaman manapun dengan interaksi yang elegan dan intuitif.
- Administrator memiliki kendali penuh atas letak, visibilitas, dan perilaku dock melalui Theme Customizer.
- Animasi GSAP berjalan mulus 60 FPS tanpa jeda atau distorsi layout.

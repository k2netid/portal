# ADR-012: Orkes Lifecycle dan Guard Dependensi Modul pada Site Identity

**Status:** Accepted / Implemented  
**Tanggal:** 2026-09-07  
**Author:** Jejakawan Engineering  
**Scope:** `frontend/src/modules/Core/System/views/settings/general/PlatformIdentityTab.vue`, `frontend/src/modules/Core/System/views/settings/general/Index.vue`, `frontend/src/modules/Core/System/views/settings/general/GeneralTab.vue`, `frontend/src/modules/Core/System/views/extensions/Index.vue`, `backend/Modules/Core/app/System/Http/Controllers/SettingController.php`  
**Supersedes / Extends:** Module Registry Architecture & Settings Subsystem  

---

## 1. Konteks & Permasalahan

Dalam arsitektur modular Jejakawan Core Engine, fungsionalitas portal publik disediakan oleh modul opsional pihak pertama (*optional first-party pack*):
- Modul **`site`**: Bertanggung jawab atas routing apex `/` dan penyediaan shell SPA web publik.
- Modul **`layout`**: Bertanggung jawab atas penyediaan tema aktif (Sarangenge/Layung/Janari), Theme Customizer, dan sistem widget.
- Modul **`publishing`**: Bertanggung jawab atas artikel, halaman, dan konten dinamis yang ditampilkan di web publik.

### Masalah yang Ditemukan (Lack of Module Lifecycle Awareness):
1. **Antarmuka Pengaturan Tidak Sadar Lifecycle Modul**:
   Tab pengaturan *Site Identity* (`PlatformIdentityTab.vue`) sebelumnya bersifat statis dan pasif. Ketika modul `site` dinonaktifkan oleh administrator melalui Module Registry (`/dash/infra/extensions`), tab *Site Identity* tetap terbuka penuh, dapat diedit, dan dapat disimpan seolah-olah situs publik sedang aktif. Hal ini membingungkan administrator (*false sense of operation*).
2. **Ketiadaan Peringatan Dependensi Modul**:
   Jika modul `site` aktif namun modul `layout` atau `publishing` dinonaktifkan, pengunjung web publik akan menemui halaman kosong atau galat render. Tidak ada peringatan visual sama sekali di tab *Site Identity* yang menginformasikan administrator tentang dependensi yang rusak tersebut.
3. **Eksekusi Logika Backend Tanpa Guard**:
   Pada backend `SettingController::updatePlatformIdentity`, fungsi sinkronisasi `syncSiteIdentityToActiveTheme()` selalu dieksekusi tanpa memeriksa apakah modul `layout` terpasang dan aktif di database, berpotensi memicu query ke tabel tema yang tidak relevan.
4. **Diskontinuitas Navigasi Module Registry**:
   Pada antarmuka App Store / Module Registry (`extensions/Index.vue`), kartu ekstensi `site` memiliki tombol "Konfigurasi" (*Configure*), namun tombol tersebut tidak memiliki rute navigasi yang jelas menuju tab *Site Identity*.

---

## 2. Keputusan Arsitektur

### A. Reaktivitas Status Modul di Frontend (`PlatformIdentityTab.vue`)
Komponen `PlatformIdentityTab.vue` diintegrasikan dengan `useExtensionStore()` untuk memantau siklus hidup modul secara langsung:
```typescript
const extensionStore = useExtensionStore();

const isSiteActive = computed(() => extensionStore.isExtensionActive('site'));
const isLayoutActive = computed(() => extensionStore.isExtensionActive('layout'));
const isPublishingActive = computed(() => extensionStore.isExtensionActive('publishing'));
```

---

### B. Penanganan Status Modul `site` Nonaktif
Ketika modul `site` dalam kondisi tidak aktif:
1. **Badge Status di Tab Header**:
   Pada komponen induk `Index.vue`, tab *Site Identity* menyematkan badge peringatan `(Nonaktif)` di samping label tab dengan styling amber.
2. **Notice Banner & Call-to-Action (CTA)**:
   Di bagian atas formulir, dirender banner peringatan amber (*warning notice*) yang menjelaskan bahwa modul Portal Publik sedang dinonaktifkan, dilengkapi tombol navigasi instan:
   `[ Buka Module Registry → ]` yang mengarahkan administrator langsung ke `/dash/infra/extensions`.
3. **Form Disabled State**:
   Seluruh kontrol input (Nama Situs, Slogan, Deskripsi, URL, Logo Publik, Favicon Publik) serta tombol Simpan otomatis beralih ke mode `disabled` dengan opacity transparan, mencegah manipulasi konfigurasi yang sia-sia.

---

### C. Banner Peringatan Dependensi Modul (Missing Dependencies Alert)
Ketika modul `site` aktif, namun modul pendukungnya (`layout` atau `publishing`) tidak aktif:
- Sistem menampilkan banner peringatan merah (*danger alert*) yang merinci dependensi yang hilang secara dinamis.
- Administrator diberikan penjelasan bahwa tampilan publik tema tidak akan berfungsi sempurna hingga modul-modul prasyarat tersebut diaktifkan kembali.

---

### D. Cross-Tab Dependency Guard di `GeneralTab.vue`
Di dalam tab pengaturan umum (*General Tab*):
- Opsi `brand_sync_site_identity` (Sinkronkan Identitas Brand ke Identitas Situs) secara cerdas dinonaktifkan (`disabled: true`) apabila modul `site` terdeteksi nonaktif.
- Disertai pesan petunjuk visual (*helper notice*) yang menerangkan bahwa sinkronisasi situs tidak relevan selama modul publik sedang tidak aktif.

---

### E. Backend Protection Guard (`SettingController.php`)
Pada sisi backend, sinkronisasi tema aktif dilindungi oleh pengecekan status produk:
```php
if (class_exists(Extension::class) && Extension::isProductActive('layout')) {
    $this->syncSiteIdentityToActiveTheme($validated);
}
```
Hal ini memastikan operasi database tema hanya dijalankan apabila modul `layout` benar-benar aktif.

---

### F. Jembatan Navigasi dari Module Registry (`extensions/Index.vue`)
Pada daftar kartu ekstensi di Module Registry:
- Tombol aksi pada kartu ekstensi `site` diarahkan secara eksplisit ke:
  ```typescript
  router.push({ name: 'settings', query: { tab: 'identity' } });
  ```
- Memberikan pengalaman pengguna yang kohesif (*seamless user journey*) dari manajemen modul langsung ke konfigurasi identitas situs.

---

## 3. Konsekuensi & Verifikasi

### Keuntungan Arsitektur:
- **Pencegahan Human Error**: Administrator tidak akan salah mengira situs publik aktif ketika modul intinya sedang dimatikan.
- **Self-Healing Guidance**: Tautan langsung ke Module Registry memandu administrator menyelesaikan masalah dependensi dalam satu kali klik.
- **Backend Safety**: Mencegah eksekusi method tema yang redundan atau memicu galat saat modul layout dimatikan.
- **Symmetric i18n**: Seluruh notifikasi status dan pesan peringatan diterjemahkan penuh ke dalam bahasa Indonesia (`id`), Inggris (`en`), dan Sunda (`su`).

### Verifikasi:
- Uji simulasi: Matikan modul `site` di Module Registry $\to$ Buka Settings $\to$ Tab Site Identity menampilkan badge `(Nonaktif)`, notice amber, dan form terproteksi.
- Uji dependensi: Aktifkan `site`, matikan `layout` $\to$ Tab Site Identity menampilkan banner peringatan dependensi merah.
- Uji integrasi: Klik tombol "Konfigurasi" pada kartu modul `Site` di Module Registry $\to$ Halaman langsung berpindah ke tab Site Identity.
- Type-check `vue-tsc -b` dan `npm run i18n:check` lulus 100%.

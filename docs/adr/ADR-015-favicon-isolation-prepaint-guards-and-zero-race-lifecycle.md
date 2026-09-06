# ADR-015: Isolasi Favicon Prepaint Shell, Guard DOM Equality, dan Zero-Race Lifecycle

**Status:** Accepted / Implemented  
**Tanggal:** 2026-09-07  
**Author:** Jejakawan Engineering  
**Scope:** `backend/Modules/Core/app/System/Support/SpaHtmlFavicon.php`, `backend/Modules/Core/resources/views/spa.blade.php`, `frontend/src/modules/Core/ConsoleApp.vue`, `frontend/src/modules/Layout/views/FrontendLayout.vue`, `frontend/src/shared/composables/useBrandIdentity.ts`, `frontend/src/shared/composables/useSiteIdentity.ts`  
**Supersedes / Extends:** Core Shell Architecture & Blade Pre-paint Optimization  

---

## 1. Konteks & Permasalahan

Favicon pada tab peramban (browser) merupakan penanda visual krusial bagi identitas aplikasi. Dalam ekosistem Jejakawan Core Engine, terdapat dua shell utama yang berjalan berdampingan pada origin yang sama:
1. **Console Shell**: Dashboard manajemen administratif (`/dash`, `/auth/console-sign-in`).
2. **Public Site Shell**: Halaman web publik tema (`/`, `/about`, `/blog`, dll).

### Gejala Masalah (Favicon Race Condition):
1. Ketika pengguna membuka tab konsol manajemen dan tab situs publik dalam peramban yang sama, terjadi "balapan" (*race condition*) ikon pada tab konsol.
2. Tab konsol yang seharusnya menampilkan favicon brand sistem (atau favicon default Jejakawan) justru sempat atau menetap menampilkan favicon situs publik (`site_favicon`).
3. Pada saat berpindah halaman atau me-refresh tab, terjadi kedipan visual (*flicker*) di mana favicon berganti-ganti secara liar sebelum akhirnya stabil.

### Analisis Akar Masalah (Root Cause Analysis):
1. **Shared Prepaint Cache Key di LocalStorage**:
   Skrip inline Blade di `<head>` template `spa.blade.php` dirancang untuk melakukan *prepaint* favicon sebelum bundle JavaScript Vue dimuat, guna mencegah *blank favicon*. Namun, skrip tersebut membaca dan menulis ke kunci penyimpanan tunggal yang sama: `localStorage.getItem('ja_favicon_href')`.
   - Akibatnya: Saat pengguna mengunjungi situs publik, kunci tersebut diisi dengan URL favicon situs publik (`/storage/site_favicon.png`). Ketika pengguna berikutnya membuka atau me-refresh tab konsol, skrip prepaint langsung memasang favicon situs publik tersebut ke tab konsol!
2. **Kebocoran Fallback di Sisi Server (`SpaHtmlFavicon.php`)**:
   Pada helper PHP `SpaHtmlFavicon::resolveHref($shell)`:
   - Ketika `$shell === 'console'`, jika entitas `brand_favicon` kosong, logika server secara keliru jatuh (*leaked fallback*) ke `Setting::get('site_favicon')` sebelum ke `/favicon.ico`.
   - Akibatnya, HTML awal yang dikirimkan oleh server Laravel untuk halaman konsol sudah salah menyematkan URL favicon situs publik di tag `<link rel="icon">`.
3. **Pemberondongan Manipulasi DOM Tanpa Validasi Kesetaraan (DOM Thrashing)**:
   Fungsi `applyFavicon(href)` di sisi klien Vue memanipulasi DOM peramban dengan menghapus elemen `<link rel="icon">` lama dan membuat elemen baru setiap kali fungsi dipanggil, tanpa memvalidasi apakah URL saat ini sudah identik dengan URL target. Hal ini memicu browser memuat ulang aset favicon berulang kali di latar belakang.
4. **Multipel Komponen Memanggil Manipulasi Favicon**:
   Komponen `GeneralTab.vue` dan `PlatformIdentityTab.vue` secara sporadis memanggil fungsi manipulasi favicon saat form dimuat, bertabrakan dengan lifecycle shell utama.

---

## 2. Keputusan Arsitektur

### A. Pemisahan Kunci Cache Prepaint Antar-Shell
Penyimpanan cache favicon di sisi klien diisolasi secara tegas berdasarkan lingkup shell:
- **Console Shell**: Menggunakan key `ja_console_favicon_href`.
- **Public Site Shell**: Menggunakan key `ja_site_favicon_href`.

Kunci `ja_favicon_href` lama dihapus secara proaktif dari `localStorage` saat bootstrapping untuk membersihkan sisa data lawas.

---

### B. Isolasi Mutlak Resolusi Favicon di Sisi Server (`SpaHtmlFavicon.php`)
Logika resolusi server-side dirombak agar tidak terjadi kebocoran lintas domain:
```php
public static function resolveHref(string $shell = 'site'): string
{
    // Shell konsol dan landing engine HANYA mencari brand_favicon
    if ($shell === 'console' || $shell === 'landing') {
        $brandFavicon = Setting::get('brand_favicon');
        if (!empty($brandFavicon)) {
            return Storage::disk('public')->url($brandFavicon);
        }
        // Fallback resmi mutlak: favicon kanonikal engine, JANGAN PERNAH jatuh ke site_favicon
        return asset('favicon.ico');
    }

    // Shell situs publik: site_favicon -> brand_favicon -> /favicon.ico
    $siteFavicon = Setting::get('site_favicon');
    if (!empty($siteFavicon)) {
        return Storage::disk('public')->url($siteFavicon);
    }

    $brandFavicon = Setting::get('brand_favicon');
    if (!empty($brandFavicon)) {
        return Storage::disk('public')->url($brandFavicon);
    }

    return asset('favicon.ico');
}
```

---

### C. Hardening Skrip Inline Prepaint (`spa.blade.php`)
Skrip inline prepaint di `<head>` template Blade diperkuat dengan aturan defensif:
1. Menentukan nama cache key berdasarkan `$shell`:
   `var cacheKey = isConsole ? 'ja_console_favicon_href' : 'ja_site_favicon_href';`
2. Hanya mengganti elemen `<link rel="icon">` jika:
   - Nilai tersimpan di `localStorage` tidak kosong dan bukan string generic (`/favicon.ico`).
   - Ikon yang ada di DOM saat ini masih merupakan ikon generik (`/favicon.ico`).
3. Jika kondisi tidak terpenuhi, biarkan HTML server-rendered yang berbicara tanpa manipulasi DOM liar.

---

### D. Penunjukan Single Source of Truth di Klien Vue
Manipulasi favicon di sisi klien didelegasikan secara eksklusif ke komponen *root shell*:
- **Konsol Shell**: Komponen `ConsoleApp.vue` bertindak sebagai *Single Source of Truth*. Pada `onMounted` dan saat `brandFaviconUrl` berubah secara reaktif, `ConsoleApp.vue` memanggil `applyBrandFavicon()`.
- **Situs Publik Shell**: Komponen `FrontendLayout.vue` bertindak sebagai *Single Source of Truth*. Pada `onMounted` dan saat `siteFaviconUrl` berubah, `FrontendLayout.vue` memanggil `applySiteFavicon()`.
- Komponen pengaturan (`GeneralTab.vue` dan `PlatformIdentityTab.vue`) hanya memanggil pembaruan favicon saat pengguna secara eksplisit menekan tombol **Simpan**.

---

### E. Guard Kesetaraan DOM (DOM Equality Guard)
Di dalam `useBrandIdentity.ts` dan `useSiteIdentity.ts`, fungsi `applyFavicon(targetHref)` dilengkapi dengan guard berbasis resolusi URL:
```typescript
const currentIcon = document.querySelector<HTMLLinkElement>('link[rel="icon"]');
if (currentIcon) {
    try {
        const currentUrl = new URL(currentIcon.href, window.location.origin);
        const targetUrl = new URL(targetHref, window.location.origin);
        if (currentUrl.pathname === targetUrl.pathname) {
            // URL sudah identik! Cegah manipulasi DOM yang memicu reload browser.
            return;
        }
    } catch {
        // Fallback gracefully
    }
}
```

---

## 3. Konsekuensi & Verifikasi

### Keuntungan Arsitektur:
- **Zero Race Condition**: Tab konsol dan tab situs publik dapat dibuka secara simultan dalam puluhan tab peramban tanpa pernah saling menimpa atau membocorkan favicon.
- **Instant Prepaint**: Browser langsung merender ikon yang tepat sejak milidetik pertama HTML diterima peramban tanpa *flash of wrong icon* (FOWI).
- **DOM Stability**: Pengurangan mutasi DOM yang tidak perlu meningkatkan performa peramban dan stabilitas UI.

### Verifikasi:
- Uji simulasi peramban simultan: Buka konsol di Tab 1, buka web publik di Tab 2, lakukan refresh berulang-ulang di kedua tab.
- Hasil: Tab 1 secara deterministik 100% selalu menampilkan favicon konsol/brand, Tab 2 secara deterministik 100% selalu menampilkan favicon situs publik.
- Pengujian simetri i18n dan type-check: Lulus 100% tanpa regresi.

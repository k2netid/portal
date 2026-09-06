# ADR-009: Preload dan Hidrasi Reaktif Menu Konsol Database Saat Transisi Otentikasi

**Status:** Accepted / Implemented  
**Tanggal:** 2026-09-06  
**Author:** Jejakawan Engineering  
**Scope:** `frontend/src/shared/stores/navigation.ts`, `frontend/src/main.ts`, `frontend/src/modules/Core/System/views/auth/Login.vue`, `frontend/src/shared/layouts/partials/TheSidebar.vue`  
**Supersedes / Extends:** Core Engine Console Shell Lifecycle & Navigation Architecture  

---

## 1. Konteks & Permasalahan

Pada antarmuka konsol manajemen (`/dash`), hierarki dan urutan navigasi menu sidebar ditentukan oleh konfigurasi database (`sys_console_menus`) yang disediakan melalui endpoint API `/api/v1/manage/console-menus`.

### Gejala Masalah:
1. Ketika pengguna baru saja melakukan login melalui form otentikasi konsol (`/auth/console-sign-in`), sidebar dashboard pertama kali muncul dalam susunan modul registrasi lokal *in-memory* yang acak/tidak teratur (misalnya menu *Analytics*, *Users & Access*, dan *Members* berada di posisi teratas, serta *Menu Editor* berada di dalam grup *Configuration*).
2. Hanya setelah pengguna melakukan *hard reload* browser (`F5`), susunan menu sidebar baru berubah menjadi rapi dan terstruktur sesuai database (misalnya *Editorial* di posisi paling atas, disusul *Insight*, *Library*, *Audience*, dll).

### Analisis Akar Masalah (Root Cause):
1. **Pemuatan Menu Hanya Terjadi Saat Bootstrap Awal**:
   Logika pemanggilan `/manage/console-menus` sebelumnya hanya ditempatkan di `main.ts` saat SPA pertama kali diinisialisasi (`bootstrap()`), dengan pengecekan `if (authStore.isAuthenticated)`.
2. **Ketiadaan Token Otentikasi Saat Bootstrap**:
   Saat pengguna pertama kali membuka rute login (`/auth/console-sign-in`), pengguna belum terotentikasi (`isAuthenticated == false`), sehingga pemanggilan API database menu di-skip. Variabel `navigationStore.dbMenuRegistry` tetap bernilai `null`.
3. **Ketiadaan Hydration Saat Transisi Login**:
   Saat proses login berhasil di `Login.vue` (`completeLogin()`), sistem langsung mengeksekusi navigasi router `router.replace(target)` tanpa memicu pemuatan database menu terlebih dahulu.
4. **Fallback In-Memory Registry**:
   Komponen `TheSidebar.vue` mengevaluasi `computed(navigationItems)`. Karena `dbMenuRegistry.value` masih `null`, ia secara otomatis jatuh ke fallback `registry.value` (urutan registrasi modul lokal di memory).
5. **Penyebab F5 Menyelesaikan Masalah**:
   Ketika pengguna menekan tombol `F5`, browser memuat ulang seluruh aplikasi. Pada saat itu, token otentikasi sudah tersimpan di browser/cookie, sehingga `authStore.isAuthenticated` bernilai `true` saat `main.ts` dieksekusi, memicu fetch database menu dan menghasilkan susunan menu yang rapi.

---

## 2. Keputusan Arsitektur

### A. Sentralisasi Logika Fetch di Navigation Store (`navigation.ts`)
Seluruh logika pemanggilan menu dipusatkan ke dalam action `fetchConsoleMenus()` di dalam `useNavigationStore()`:
- Memuat konfigurasi database menus `/manage/console-menus` dan ekstensi navigasi dinamis `/manage/infra/extensions/navigation`.
- Dilengkapi dengan *concurrency lock flag* (`isFetchingMenus`) untuk mencegah duplikasi pemanggilan request API secara paralel.
- Memastikan `markMenusReady()` selalu terpanggil baik saat sukses maupun saat error, agar antarmuka tidak terblokir.

### B. Pre-fetching Saat Transisi Login (`Login.vue`)
Di dalam `completeLogin()` pada `Login.vue`, sebelum rute dialihkan ke dashboard (`router.replace`), aplikasi secara proaktif menjalankan:
```typescript
const navStore = useNavigationStore();
try {
    await navStore.fetchConsoleMenus();
} catch {
    // Non-blocking fallback
}
```
Hal ini menjamin bahwa saat view dashboard dan komponen sidebar di-mount, `dbMenuRegistry` sudah terisi dengan konfigurasi database yang valid, sehingga tidak ada kedipan tampilan (*Flash of Unordered Content*).

### C. Defensive Resilient Fallback di Sidebar (`TheSidebar.vue`)
Sebagai perlindungan lapis kedua (misalnya pada skenario *re-authentication*, *token refresh*, atau navigasi tanpa reload penuh), ditambahkan pemeriksaan otonom pada `TheSidebar.vue`:
1. Pada lifecycle hook `onMounted()`:
   ```typescript
   if (authStore.isAuthenticated && !navigationStore.dbMenuRegistry) {
       void navigationStore.fetchConsoleMenus();
   }
   ```
2. Pada reactive watcher `authStore.isAuthenticated`:
   ```typescript
   watch(
       () => authStore.isAuthenticated,
       (isAuth) => {
           if (isAuth && !navigationStore.dbMenuRegistry) {
               void navigationStore.fetchConsoleMenus();
           }
       },
   );
   ```

### D. Penyederhanaan Bootstrap Kernel (`main.ts`)
Blok kode imperatif di `main.ts` disederhanakan menjadi panggilan deklaratif ke store:
```typescript
if (authStore.isAuthenticated) {
    await navStore.fetchConsoleMenus();
} else {
    navStore.markMenusReady();
}
```

---

## 3. Dampak & Keuntungan

1. **Pengalaman Pengguna Sempurna (Zero FOUC)**:
   Menu konsol langsung tampil teratur dan stabil seketika pengguna selesai memasukkan kredensial login, tanpa mengharuskan pengguna melakukan refresh manual.
2. **Arsitektur Lebih Rapi & Modular**:
   Logika pemanggilan menu database dan ekstensi navigasi tidak lagi berserakan di level bootstrap aplikasi, melainkan terenkapsulasi secara rapi di dalam `navigationStore`.
3. **Ketahanan Sistem (Resilience & Graceful Degradation)**:
   Apabila terjadi kegagalan jaringan atau server backend mengalami gangguan saat fetch menu, sistem secara anggun tetap menampilkan menu in-memory modul agar pengguna tetap dapat beroperasi.
4. **Sinkronisasi Multi-Tenant Konsisten**:
   Perbaikan ini berlaku di seluruh portal downstream (`smkn6-portal` maupun `k2net-portal`) karena berada di level Core Shell Navigation.

---

## 4. Status Implementasi & Verifikasi

- **Type-Check**: `vue-tsc -b` lulus tanpa error (0 errors).
- **Unit Tests**: 49 file test lulus, 302 unit test lolos 100%.
- **Asset Compilation**: Build asset frontend berhasil dikompilasi dan disinkronkan ke backend di kedua portal.
- **Git Commit**:
  - `smkn6-portal`: `df38358` (`feat/theme-sarangenge`)
  - `k2net-portal`: `3be1427` (`main`)

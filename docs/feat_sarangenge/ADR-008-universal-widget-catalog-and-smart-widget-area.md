# ADR-008: Arsitektur Universal Widget Catalog & Smart Fallback WidgetArea

**Status:** Accepted / Implemented  
**Tanggal:** 2026-09-05  
**Author:** Jejakawan Engineering  
**Scope:** `smkn6-portal` (Frontend Layout Module, Theme System, Universal Widgets, E2E Testing)  
**Supersedes / Extends:** [ADR-004](./ADR-004-multi-theme-soc-cross-theme-isolation-and-identity-generalization.md), [ADR-006](./ADR-006-official-plugin-floating-social-dock-and-extensions-categorization.md), [ADR-007](./ADR-007-unified-package-lifecycle-and-licensing-controls.md)

---

## 1. Konteks & Permasalahan

Sebelumnya, terdapat redundansi dan keterpisahan arsitektur antara komponen widget di sistem:
1. **Redundansi Kode Sidebar Antar Tema**:
   - `BlogSidebar.vue` di tema Sarangenge dan Janari mengimplementasikan kembali logika pencarian, autocomplete, fetching kategori, penghitungan jumlah artikel, dan styling masing-masing secara terisolasi.
2. **Kekosongan pada WidgetArea (`lay_widgets`)**:
   - Komponen `<WidgetArea location="..." />` sudah tersedia di frontend dan terhubung ke tabel database `lay_widgets`. Namun saat database belum memiliki baris konfigurasi (`widgets.length === 0`), komponen tersebut menyembunyikan diri (`v-if="widgets.length > 0"`).
   - Akibatnya, halaman detail artikel (`Post.vue`) meng-hardcode komponen `<BlogSidebar />` alih-alih memanfaatkan sistem dinamis `<WidgetArea location="sidebar" />`.
3. **Ketiadaan Widget Esensial Siap Pakai**:
   - Sistem belum memiliki katalog widget kanonikal untuk interaksi umum seperti *Social Sharing* (WhatsApp, Telegram, X, Facebook, Salin Tautan) dan *Newsletter Subscription* yang dapat dipasang di sembarang lokasi sidebar atau footer.

---

## 2. Keputusan Arsitektur

### A. Pembentukan Universal Widget Catalog
Dibuat direktori kanonikal `frontend/src/modules/Layout/components/widgets/` yang memuat 5 widget universal berbasis SoC (*Separation of Concerns*) dan SoT (*Single Source of Truth*):

1. **`SearchWidget.vue`**:
   - Input pencarian interaktif dengan *real-time debounced suggestions* via endpoint `/public/search/suggestions`.
   - Navigasi keyboard (panah atas/bawah, Enter, Escape) dan tombol pembersih (*clear query*).
   - Pengalihan terpadu ke `/blog?q=...` atau `/search?q=...`.
2. **`CategoriesWidget.vue`**:
   - Mengambil daftar kategori secara mandiri atau menerima data dari props/widget settings.
   - Mendukung hierarki bertingkat (*sub-categories accordion toggle*), badge jumlah postingan, dan deteksi kategori aktif reaktif dari query URL.
3. **`RecentPostsWidget.vue`**:
   - Mengambil daftar artikel terbaru via `/public/publishing/contents?limit=5&type=post`.
   - Dilengkapi thumbnail cover, badge kategori, judul dengan *line-clamp*, dan tanggal terbit terformat lokal.
   - Otomatis memfilter artikel yang sedang dibaca (`currentPostSlug`) agar tidak muncul ganda di sidebar.
4. **`NewsletterWidget.vue`**:
   - Formulir langganan buletin warta sekolah dengan validasi format email.
   - Umpan balik visual interaktif (indikator loading spinner, pesan sukses pendaftaran, penanganan email duplikat).
5. **`SocialShareWidget.vue`**:
   - Tombol berbagi instan untuk artikel aktif: WhatsApp, Telegram, X (Twitter), Facebook, dan Salin Tautan (*Copy Link to Clipboard* dengan animasi umpan balik centang).
6. **`index.ts`**:
   - Barrel export seluruh widget untuk kemudahan konsumsi di tema publik maupun layout builder console.

---

### B. Smart Fallback pada `WidgetArea.vue`
Komponen `WidgetArea.vue` ditingkatkan menjadi *smart orchestrator*:
- **Resolusi Komponen Dinamis**:
  - `widget.type === 'search'` → `SearchWidget`
  - `widget.type === 'categories'` → `CategoriesWidget`
  - `widget.type === 'recent_posts' | 'content_list'` → `RecentPostsWidget`
  - `widget.type === 'newsletter'` → `NewsletterWidget`
  - `widget.type === 'social_share'` → `SocialShareWidget`
  - `widget.type === 'html' | 'custom'` → `ThemeSafeHtml`
  - `widget.type === 'text'` → Paragraf teks terformat
- **Smart Fallback Slot**:
  - Apabila tabel `lay_widgets` belum memiliki konfigurasi kustom untuk lokasi tertentu (`widgets.length === 0`), komponen merender slot cadangan `<slot :context="context">` jika disediakan oleh tema pemanggil.
  - Jika tidak ada slot yang disediakan, `WidgetArea` secara otomatis merender *Default Universal Widget Stack* (`SearchWidget`, `CategoriesWidget`, `RecentPostsWidget`, `SocialShareWidget`, `NewsletterWidget`), menjamin tata letak halaman tidak pernah kosong.

---

### C. Integrasi Halaman Detail Artikel (`Post.vue`) & Refaktorisasi `BlogSidebar.vue`
1. **Detail Artikel (`Post.vue`)**:
   - Kolom sidebar halaman detail artikel pada tema Sarangenge dan Janari kini dibungkus dengan:
     ```html
     <WidgetArea location="sidebar" :context="{ post }">
       <BlogSidebar />
     </WidgetArea>
     ```
   - Ini memberikan fleksibilitas penuh: jika Super Admin menambahkan widget baru di konsol `/dash/widgets`, widget kustom tersebut akan langsung muncul secara dinamis. Jika belum diatur, tata letak otomatis kembali ke sidebar standar tema.
2. **Refaktorisasi `BlogSidebar.vue`**:
   - Menghapus duplikasi kode input pencarian dan daftar kategori, menggantikannya dengan komposisi bersih `SearchWidget` dan `CategoriesWidget` sembari mempertahankan kartu kontak sekolah dan banner PPDB Online khas Sarangenge.
3. **Modal Konsol Pengaturan Widget (`WidgetModal.vue`)**:
   - Menambahkan opsi tipe `search`, `newsletter`, dan `social_share` pada dropdown tipe widget di antarmuka manajemen `/dash/widgets`.

---

## 3. Hasil Verifikasi & Pengujian

1. **Pemeriksaan Simetri Bahasa (i18n)**:
   - `npm run i18n:check`: 27 gate keys, **9.145 kunci simetris** pada `en`, `id`, dan `su` (JSON valid).
2. **Kompilasi Frontend**:
   - `npm run build`: Selesai dalam 11.8s tanpa *type error* maupun *syntax warning*.
3. **Sinkronisasi Aset**:
   - `sync-frontend-assets-to-backend.sh`: Berhasil menyinkronkan aset bundle baru ke `backend/public/`.
4. **Backend Unit/Feature Tests**:
   - `php artisan test Modules/Layout/tests`: 16/16 pengujian lolos (3.9s).
5. **Playwright End-to-End Suite**:
   - `tests/e2e/universal-widgets-lifecycle.spec.ts`: 2/2 skenario lolos (11.3s):
     - Memverifikasi keberadaan `WidgetArea[data-widget-location="sidebar"]` pada halaman artikel `/blog/sarangenge-sample-ppdb-2026`.
     - Memverifikasi interaktivitas input pencarian dan kemunculan daftar kategori.
     - Memverifikasi kehadiran universal widgets pada halaman indeks `/blog`.
   - `tests/e2e/theme-package-lifecycle.spec.ts`: 4/4 skenario tetap lolos (regresi nol).

# ADR-019: Tata Kelola Verifikasi Email Pengguna Baru dan Proteksi Otorisasi Pembuatan Akun

**Status:** Accepted / Implemented  
**Tanggal:** 2026-09-07  
**Author:** Jejakawan Engineering  
**Scope:** `backend/Modules/Core/app/System/Http/Controllers/UserController.php`, `backend/Modules/Core/app/System/Models/User.php`, `frontend/src/modules/Core/System/views/users/UserModal.vue`, `backend/Modules/Core/tests/System/Feature/RolesAndUsersApiTest.php`  
**Supersedes / Extends:** Core Engine User Authentication & Identity Model  

---

## 1. Konteks & Permasalahan

Dalam sistem autentikasi dan manajemen pengguna Jejakawan Core Engine:

1. **Inkonsistensi Status Verifikasi Pengguna Baru**:
   - Pada implementasi sebelumnya, setiap pembuatan user baru melalui admin konsol secara otomatis mengisi `email_verified_at = now()` dan `is_verified = 1` tanpa memedulikan alur verifikasi email nyata atau maksud administrator.
   - Akibatnya, pada antarmuka tabel daftar pengguna, user baru berstatus terverifikasi hijau, namun dalam kondisi tertentu atau saat alur aktivasi mandiri diuji, pengguna bingung mengenai tata cara verifikasi mandiri email pengguna.
2. **Ketiadaan Opsi Verifikasi Manual**:
   - Administrator yang berwenang (misalnya Super Admin atau Admin yang membuat akun resmi dinas/sekolah secara massal) tidak memiliki kontrol eksplisit untuk menentukan apakah suatu akun langsung aktif terverifikasi atau harus memverifikasi email terlebih dahulu.
3. **Ketiadaan Batasan Hierarki Otoritas**:
   - Pembuatan akun terverifikasi langsung seharusnya merupakan hak prerogatif pengguna dengan role atau bobot wewenang lebih tinggi, bukan perilaku default otomatis untuk semua pengguna.

---

## 2. Keputusan Arsitektur

### A. Default Unverified pada Pembuatan Pengguna Baru

1. **Default State**:
   - Setiap pengguna baru yang dibuat melalui sistem secara default memiliki status `is_verified = false` dan `email_verified_at = null`.
   - Pengguna harus melakukan verifikasi melalui tautan email konfirmasi yang dikirimkan oleh sistem atau diverifikasi secara manual oleh administrator yang memiliki kewenangan lebih tinggi.
2. **Otorisasi Verifikasi Instan (`is_verified` flag)**:
   - Backend `UserController::store` menerima parameter boolean eksplisit `is_verified`.
   - Hanya administrator yang berwenang yang dapat mengirimkan `is_verified: true` saat pembuatan akun. Jika diaktifkan, backend menandai `email_verified_at = now()`.

### B. Kontrol UI pada Formulir Pembuatan Pengguna (`UserModal.vue`)

- Formulir modal tambah pengguna dilengkapi opsi sakelar (*toggle / checkbox*): **"Verifikasi Email Langsung"** (*Directly mark email as verified*).
- Disertai teks penjelasan yang transparan: *"Jika dicentang, pengguna dapat langsung login tanpa harus melakukan verifikasi email mandiri."*
- Pilihan ini defaultnya tidak tercentang, mewajibkan tindakan sadar dari administrator.

### C. Penanganan Status Login yang Jelas

- Pada controller login dan respons autentikasi, jika akun pengguna belum memverifikasi email, sistem mengembalikan pesan instruktif yang akurat: *"Email Anda belum diverifikasi. Silakan periksa kotak masuk email Anda atau hubungi administrator untuk verifikasi akun."*

### D. Pengujian Otomatis Fitur (Feature Test)

Ditambahkan skenario pengujian komprehensif pada `RolesAndUsersApiTest.php`:
1. `test_user_created_by_default_is_unverified_and_requires_verification`: Memastikan user baru secara default `email_verified_at = null` dan login mengembalikan status unverified.
2. `test_user_created_with_is_verified_flag_is_immediately_active`: Memastikan user yang dibuat dengan flag `is_verified = true` langsung memiliki timestamp `email_verified_at` dan dapat login tanpa hambatan.

---

## 3. Konsekuensi & Keuntungan

1. **Kepatuhan Keamanan (Security Compliance)**: Mencegah eksploitasi pembuatan akun palsu atau typo alamat email yang langsung dianggap sah oleh sistem.
2. **Fleksibilitas Operasional**: Memungkinkan admin sekolah/institusi untuk mengaktifkan staf langsung tanpa menunggu email aktivasi, sembari tetap menjaga pengguna publik/member berada dalam alur verifikasi resmi.
3. **Transparansi Status**: Tabel user dan alur login mencerminkan kondisi riil akun secara 100% akurat.

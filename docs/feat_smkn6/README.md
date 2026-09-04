# Dokumentasi Proyek Website SMK Negeri 6 Bandung

Direktori ini merupakan pusat dokumentasi resmi perancangan, implementasi fitur, Architecture Decision Records (ADR), dan riwayat perubahan khusus untuk **Website SMK Negeri 6 Bandung** (berbasis fork `ja-core_engine`).

---

## 📌 Rangkuman Proyek

| Item | Keterangan |
| :--- | :--- |
| **Institusi** | **SMK Negeri 6 Bandung** (Sekolah Pusat Keunggulan) |
| **Alamat Kampus** | Jl. Soekarno-Hatta No. 636, Sekejati, Kec. Buahbatu, Kota Bandung, Jawa Barat 40286 |
| **Kontak** | Telp: (022) 7563286 · Email: `info@smkn6bandung.sch.id` |
| **Branch Git** | `feat/smkn6-theme-sarangenge` |
| **Tema Aktif** | **Sarangenge 2.0.0** (Tema Sekolah Modern & Aksesibilitas 2026) |
| **Environment Dev (ja-dev)** | `http://192.168.88.71:49280/` (Path: `/home/jejakawan/dev/smkn6-portal`) |
| **Environment Staging** | `http://192.168.10.233:49280/` (Path: `/home/jejakawan/portal/www/staging`) |
| **Environment Publish** | Domain `smkn6bandung.sch.id` (Target Path: `/home/jejakawan/portal/www/portal`) |

---

## 📂 Struktur Dokumentasi

1. **[tasks.md](./tasks.md)**: Daftar tugas, milestone implementasi, backlog fitur, dan catatan deployment.
2. **[ADR-001: Dynamic School Identity & Dedicated Staging Port](./ADR-001-dynamic-school-identity-and-vhost-port.md)**: Keputusan arsitektur mengenai pemindahan seluruh identitas sekolah ke Theme Customization dinamis dan isolasi port staging berbasis RFC 6335.
3. **[ADR-002: Pemisahan APP_NAME vs SITE_NAME](./ADR-002-app-name-vs-site-name-identity-separation.md)**: Keputusan arsitektur mengenai pemisahan identitas brand pengembang (`APP_NAME` = Jejakawan) dan identitas pemilik situs (`SITE_NAME` = SMKN 6 Bandung), termasuk mekanisme proteksi via lisensi White Label.
4. **[ADR-003: Integrasi Plugin Instagram Feed Generik & Slot Tema Dinamis](./ADR-003-generic-fail-safe-instagram-feed-plugin-and-theme-slots.md)**: Keputusan arsitektur integrasi media sosial fail-safe, circuit breaker gatekeeper, caching proxy server-side, dan slot dinamis lintas tema.

---

## 🚀 Alur Kerja & Standar Teknis

- Seluruh kode fitur sekolah dikerjakan di branch **`feat/smkn6-theme-sarangenge`**.
- Standar kualitas wajib lulus sebelum push:
  ```bash
  cd /home/jejakawan/portal/runtime/frontend
  npm run i18n:check
  NODE_OPTIONS="--max-old-space-size=4096" npm run build
  ```
- Deploy staging lokal di CT 101:
  - Frontend dikompilasi ke `backend/public/`.
  - Backend disinkronkan ke `/home/jejakawan/portal/www/staging/`.
  - Staging diakses via port **`49280`** (RFC 6335 Private Port) atau **`8080`** (IANA HTTP-Alt).

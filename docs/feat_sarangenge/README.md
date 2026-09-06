# Dokumentasi Proyek Website SMK Negeri 6 Bandung

Direktori ini merupakan pusat dokumentasi resmi perancangan, implementasi fitur, Architecture Decision Records (ADR), dan riwayat perubahan untuk **adaptasi platform JA Core Engine ke institusi pendidikan** (tema Sarangenge). Codebase sekarang bersifat **vendor/tenant-agnostic** — semua identitas brand di-resolve secara dinamis via Theme Customizer dan System Settings.

---

## 📌 Rangkuman Proyek

| Item | Keterangan |
| :--- | :--- |
| **Institusi Target** | *Dikonfigurasi via `SITE_NAME` env dan Theme Customizer* |
| **Alamat Kampus** | *Dikonfigurasi via `contact_address` di Theme Customizer* |
| **Kontak** | *Dikonfigurasi via `contact_email`, `contact_phone` di Theme Customizer* |
| **Branch Git** | `feat/smkn6-theme-sarangenge` |
| **Tema Aktif** | **Sarangenge 2.0.0** (Tema Sekolah Modern & Aksesibilitas 2026) |
| **Environment Dev (ja-dev)** | `http://192.168.88.71:49280/` (Path: `/home/jejakawan/dev/smkn6-portal`) |
| **Environment Staging** | `http://192.168.10.233:49280/` (Path: `/home/jejakawan/portal/www/staging`) |
| **Environment Publish** | *Domain dikonfigurasi per deployment* |

---

## 📂 Struktur Dokumentasi

1. **[tasks.md](./tasks.md)**: Daftar tugas, milestone implementasi, backlog fitur, dan catatan deployment.
2. **[ADR-001: Dynamic School Identity & Dedicated Staging Port](./ADR-001-dynamic-school-identity-and-vhost-port.md)**: Keputusan arsitektur mengenai pemindahan seluruh identitas sekolah ke Theme Customization dinamis dan isolasi port staging berbasis RFC 6335.
3. **[ADR-002: Pemisahan APP_NAME vs SITE_NAME](./ADR-002-app-name-vs-site-name-identity-separation.md)**: Keputusan arsitektur mengenai pemisahan identitas brand pengembang (`APP_NAME` = Jejakawan) dan identitas pemilik situs (`SITE_NAME` = SMKN 6 Bandung), termasuk mekanisme proteksi via lisensi White Label.
4. **[ADR-003: Integrasi Plugin Instagram Feed Generik & Slot Tema Dinamis](./ADR-003-generic-fail-safe-instagram-feed-plugin-and-theme-slots.md)**: Keputusan arsitektur integrasi media sosial fail-safe, circuit breaker gatekeeper, caching proxy server-side, dan slot dinamis lintas tema.
5. **[ADR-004: Multi-Theme SoC, Cross-Theme Isolation, dan Generalisasi Identitas](./ADR-004-multi-theme-soc-cross-theme-isolation-and-identity-generalization.md)**: Keputusan arsitektur eliminasi kebocoran tema silang (*cross-theme leakage*), klasifikasi archetype & parent theme, pembersihan hardcode brand, generalisasi komponen, dan seeder database.
6. **[ADR-005: Generalisasi Menyeluruh Referensi Brand Tenant & Platform](./ADR-005-complete-brand-generalization-smkn6-and-k2net.md)**: Penghapusan total hardcode SMKN 6 Bandung (Sarangenge) dan K2NET (Layung) dari seluruh codebase — composables, locales, schema, Vue components, backend, dan test fixtures.
7. **[ADR-006: Arsitektur Official Plugin Floating Social Dock & Hotline serta Kategorisasi Ekstensi](./ADR-006-official-plugin-floating-social-dock-and-extensions-categorization.md)**: Decoupling dock dari tema menjadi plugin resmi dengan slot layout `floating_overlay`, styling kanonikal, integrasi dynamic customizer, dan perbaikan kategorisasi tab panel ekstensi.
8. **[ADR-007: Arsitektur Siklus Hidup Paket (Ekspor/Impor) Tema & Ekstensi serta Kontrol Lisensi dan Keamanan](./ADR-007-unified-package-lifecycle-and-licensing-controls.md)**: Penyelarasan antarmuka ekspor/impor tema dan ekstensi, kontrol granular keamanan via `sys_settings`, integrasi komersial licensing tier (Pro/Enterprise/White-Label), dan kesetaraan i18n penuh.
9. **[ADR-008: Arsitektur Universal Widget Catalog & Smart Fallback WidgetArea](./ADR-008-universal-widget-catalog-and-smart-widget-area.md)**: Pembangunan katalog widget universal (Search, Categories, Recent Posts, Newsletter, Social Share), smart fallback slot pada `WidgetArea.vue`, eliminasi duplikasi di `BlogSidebar.vue`, dan integrasi dinamis pada `Post.vue`.
10. **[ADR-009: Preload dan Hidrasi Reaktif Menu Konsol Database Saat Transisi Otentikasi](./ADR-009-console-sidebar-menu-preloading-and-reactive-hydration.md)**: Resolusi susunan menu sidebar konsol tidak berurut saat login baru melalui pemusatan fetch di `navigationStore`, preloading di `Login.vue`, dan resilient fallback di `TheSidebar.vue`.
11. **[ADR-010: Arsitektur 3-Tier Identitas, White Label Enterprise, dan Smart Brand Sync](./ADR-010-three-tier-identity-whitelabel-brand-and-smart-sync.md)**: Pemisahan tiga lapis identitas (Core Engine vs White Label Brand vs Site Identity), smart brand sync satu-klik, eliminasi redundansi logo di Console Appearance, dan graceful auto-fallback ke brand kanonikal Jejakawan saat logo dihapus.
12. **[ADR-011: Isolasi Favicon Prepaint Shell, Guard DOM Equality, dan Zero-Race Lifecycle](./ADR-011-favicon-isolation-prepaint-guards-and-zero-race-lifecycle.md)**: Eliminasi tuntas kondisi balapan (*race condition*) favicon antar-tab peramban melalui pemisahan kunci cache `localStorage` (`ja_console_favicon_href` vs `ja_site_favicon_href`), pengetatan fallback server Blade/PHP (`SpaHtmlFavicon`), DOM equality guards di composables, dan penunjukan single source of truth di `ConsoleApp.vue` & `FrontendLayout.vue`.
13. **[ADR-012: Orkes Lifecycle dan Guard Dependensi Modul pada Site Identity](./ADR-012-module-aware-site-identity-and-dependency-orchestration.md)**: Reaktivitas status modul `site`, `layout`, dan `publishing` pada tab Site Identity, penyediaan notice banner dengan shortcut CTA ke Module Registry, form proteksi saat modul nonaktif, cross-tab dependency guard pada `brand_sync_site_identity`, dan backend protection guard di `SettingController.php`.
14. **[ADR-013: Workspace Tampilan Konsol Dual-Mode (Easy vs Advanced) dan Komponen Slider](./ADR-013-console-appearance-dual-mode-workspace-and-license-gating.md)**: Pemisahan ruang kerja tampilan menjadi Easy Mode (mode mudah & preset warna cepat) dan Advanced Mode (token CSS granular, saturasi, dan custom CSS editor) terproteksi dialog konfirmasi, komponen `Slider.vue` berkontras tinggi dengan filled progress track, serta lisensi gating untuk portabilitas tema dan aset konsol.


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

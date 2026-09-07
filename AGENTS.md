# Panduan Agen — Jejakawan Core Engine (`ja-core_engine`)

**Peran:** Upstream Source of Truth & Agnostic Kernel Platform untuk seluruh ekosistem portal Jejakawan.

---

## 1. Wajib Dibaca Sebelum Mulai (*Mandatory Reading*)
1. [`docs/AGENT_START_HERE.md`](docs/AGENT_START_HERE.md) — Arsitektur engine, modul, dan lifecycle.
2. [`docs/branching.md`](docs/branching.md) — Matriks 4-repo & strategi sinkronisasi upstream/downstream.
3. [`docs/adr/ADR-022-upstream-core-curation-and-generic-theme-seeder-architecture.md`](docs/adr/ADR-022-upstream-core-curation-and-generic-theme-seeder-architecture.md) — Filosofi pemisahan core vs deployment seeder.
4. [`docs/audit/AUDIT-2026-09-07-codebase-curation-and-upstream-sync.md`](docs/audit/AUDIT-2026-09-07-codebase-curation-and-upstream-sync.md) — Laporan audit kualitas & sinkronisasi multi-repo.

---

## 2. Batasan Scope Repositori Ini
- **Yang BOLEH dilakukan di sini**:
  - Pengembangan modul inti (`Modules/Core`, `Layout`, `Publishing`, `Library`, `Forms`, `Mail`, `Media`, `Member`, `Analytics`, `Search`, `Newsletter`).
  - Pembaruan library tema publik generic (`Janari`, `Layung`, `Sarangenge`).
  - Penambahan generic theme demo seeders (`JanariThemeDemoSeeder`, `LayungThemeDemoSeeder`, `SarangengeThemeDemoSeeder`).
  - Sinkronisasi kapabilitas manifest & Spatie permission (`php artisan rbac:sync`).
- **Yang DILARANG KERAS (RESTRICTED)**:
  - ❌ **DILARANG** memasukkan data spesifik klien/organisasi (nama legal PT, nomor telepon riil, alamat fisik instansi, jurusan spesifik sekolah).
  - ❌ **DILARANG** memasukkan logo komersial klien (misal logo K2NET atau SMKN 6).
  - ❌ **DILARANG** membuat `DeploymentSeeder` klien di repo ini.

---

## 3. Alur Kerja Git & Upstream Parity
- Branch utama: **`main`**.
- Setiap perubahan arsitektur atau perbaikan bug di repo ini wajib dipropagasikan ke 3 downstream repo:
  - [`/home/jejakawan/dev/ja-cms`](../ja-cms)
  - [`/home/jejakawan/dev/k2net-portal`](../k2net-portal)
  - [`/home/jejakawan/dev/smkn6-portal`](../smkn6-portal)

---

## 4. Quality Gate Sebelum Selesai
```bash
# Verifikasi linter & test
npm run agent:verify

# Verifikasi sinkronisasi RBAC
php artisan rbac:sync

# Verifikasi seeder tema
php artisan theme:seed --all
```

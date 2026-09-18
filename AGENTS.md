# Panduan Agen — Jejakawan Core Engine (`ja-core_engine`)

**Peran:** Upstream Source of Truth & Agnostic Kernel Platform untuk seluruh ekosistem portal Jejakawan.

---

## 1. Wajib Dibaca Sebelum Mulai (*Mandatory Reading*)
1. [`docs/WORKFLOW.md`](docs/WORKFLOW.md) — Urutan kerja: audit → diskusi → rencana → implement → verify → catat.
2. [`docs/DOCUMENTATION.md`](docs/DOCUMENTATION.md) — SoT / SoC / Diátaxis & aturan ADR (jangan campur docs tenant ke core).
3. [`docs/AGENT_START_HERE.md`](docs/AGENT_START_HERE.md) — Arsitektur engine, modul, dan lifecycle.
4. [`docs/branching.md`](docs/branching.md) — Matriks 4-repo & strategi sinkronisasi upstream/downstream.
5. [`docs/architecture/04-theme-system.md`](docs/architecture/04-theme-system.md) — Host vs theme package; pointer ke naming-conventions di kode.
6. [`docs/adr/ADR-022-upstream-core-curation-and-generic-theme-seeder-architecture.md`](docs/adr/ADR-022-upstream-core-curation-and-generic-theme-seeder-architecture.md) — Filosofi pemisahan core vs deployment seeder.
7. [`docs/audit/AUDIT-2026-09-07-codebase-curation-and-upstream-sync.md`](docs/audit/AUDIT-2026-09-07-codebase-curation-and-upstream-sync.md) — Laporan audit kualitas & sinkronisasi multi-repo.

Task M+: buat/isi brief di [`docs/work/`](docs/work/README.md) dari [`docs/templates/task-brief.md`](docs/templates/task-brief.md). How-to: [`docs/guides/start-a-task.md`](docs/guides/start-a-task.md).

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

How-to: [`docs/guides/run-quality-gates.md`](docs/guides/run-quality-gates.md) · Testing: [`docs/reference/testing.md`](docs/reference/testing.md) · E2E: [`docs/guides/run-e2e-playwright.md`](docs/guides/run-e2e-playwright.md) · CLI: [`docs/reference/cli-commands.md`](docs/reference/cli-commands.md) · Changelog: [`docs/guides/update-changelog.md`](docs/guides/update-changelog.md) · Versioning: [`docs/guides/release-and-versioning.md`](docs/guides/release-and-versioning.md) · [`CONTRIBUTING.md`](CONTRIBUTING.md).

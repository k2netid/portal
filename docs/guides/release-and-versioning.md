# How-to: Release & versioning

Satu produk, beberapa lapisan versi. Jangan campur SoT-nya.

## Prerequisites

- Baca [update-changelog.md](update-changelog.md) (cara potong `[Unreleased]`)
- Kontrak modul: [module-contract.md](../extensions/module-contract.md)

## Lapisan versi (SoT)

| Lapisan | SoT | Contoh | Catatan |
| :--- | :--- | :--- | :--- |
| **Produk** (Core Engine release) | Root `package.json` → `"version"` | `1.0.0-beta.2` | Harus sama dengan `frontend/package.json` + `backend/composer.json` |
| **Override deploy** | `APP_VERSION` di `.env` | opsional | Mengisi `config('app.version')` / `site_version` |
| **OpenAPI info.version** | Sama produk; override `API_VERSION` | jarang | Scramble + Dynamic OpenAPI memakai `config('app.version')` kecuali `API_VERSION` diset |
| **URL API** | Prefix `api/v1` | stabil | Bukan SemVer. Naik ke `v2` hanya lewat **ADR** breaking change |
| **Modul / plugin** | `module.json` / `manifest.json` → `version` | `1.0.0` | SemVer mandiri; dependency constraints dicek saat activate |
| **Tema** | `theme.json` → `version` | `2.0.0` | SemVer (`x.y.z`, boleh pre-release). Jangan `V.2.0` |
| **Release notes** | Root `CHANGELOG.md` | `## [1.0.0-beta.2]` | Modul boleh tetap `[Unreleased]` sampai pack di-tag terpisah |

## Rules of thumb (SemVer produk)

- **MAJOR** — breaking API publik / kontrak modul / data migration wajib
- **MINOR** — fitur mundur-kompatibel
- **PATCH** — bugfix
- **Pre-release** — `1.0.0-beta.N` untuk garis beta

## Module / theme SemVer (lapisan pack)

Produk global **tidak** menggantikan versi pack. SoT pack:

| Pack | SoT versi |
| :--- | :--- |
| First-party module / plugin | `backend/Modules/<Name>/manifest.json` → `version` |
| First-party extension folder | `backend/extensions/<slug>/manifest.json` → `version` |
| Theme package | `frontend/src/modules/Layout/views/themes/<slug>/theme.json` → `version` |

**Baseline:** banyak pack masih `1.0.0` = *pre-discipline*. Bukan janji fitur setara antar pack. **Forward-only** — jangan mass-bump; naikkan saat ada perubahan runtime nyata. Layout sudah boleh di `1.1.0+`.

### Kapan bump (pack)

| Jenis | Contoh |
| :--- | :--- |
| **PATCH** | Bugfix, hardening non-breaking, **locales/i18n** |
| **MINOR** | Fitur / API / UI mundur-kompatibel |
| **MAJOR** | Breaking kontrak pack, migration wajib, hapus API |

Satu PR: **satu** bump SemVer per pack yang tersentuh. Tetap isi `[Unreleased]` di CHANGELOG pack (atau domain Core) — lihat [update-changelog.md](update-changelog.md).

### Allowlist (tidak wajib bump)

- `**/CHANGELOG.md`, `**/README.md`
- `docs/**` (dokumentasi repo)

### Runtime (wajib bump + CHANGELOG ikut berubah)

- BE: `backend/Modules/<Name>/app|routes|database|config/**`, `manifest.json` (field kontrak), dll. selain allowlist
- FE: `frontend/src/modules/<Name>/**` kecuali CHANGELOG/README — **termasuk locales**
- Core: bump `Modules/Core/manifest.json`; changelog boleh di `Core/{System,Infra,Security}/CHANGELOG.md` (BE/FE)
- Tema: perubahan di `views/themes/<slug>/` (non-allowlist) → bump `theme.json` + `themes/<slug>/CHANGELOG.md` (bukan wajib bump Layout host)
- Layout host (di luar `views/themes/`) → bump Layout `manifest.json`

### Dependency constraints

Saat MAJOR pack yang di-depend: update constraint di dependents dalam PR yang sama (atau PR susulan — activate tetap gagal sampai match; gate runtime sudah ada).

### CI pack

```bash
npm run modules:versions:check   # diff vs origin/main (atau GITHUB_BASE_REF)
```

Fail jika path runtime pack/tema berubah tanpa naikkan `version` di SoT + tanpa menyentuh CHANGELOG pack/tema.

## Checklist cut release

1. Pastikan quality gates hijau (`npm run agent:verify`).
2. Update tiga manifest ke tag yang sama:
   - `package.json`
   - `frontend/package.json`
   - `backend/composer.json`  
   (dan top-level `version` di lockfile masing-masing bila ada).
3. Root `CHANGELOG.md`: pindahkan `[Unreleased]` → `## [x.y.z] - YYYY-MM-DD`.
4. Jalankan `npm run versions:check`.
5. Tag git `vx.y.z` (atau `x.y.z` sesuai konvensi remote) + push tag.
6. Opsional: `npm run release` (bundle memakai `frontend/package.json` version).
7. Regenerasi OpenAPI bila perlu: `npm run docs:openapi` (info.version ikut produk).

## Runtime yang sudah ditegakkan

- `config('app.version')` → PublicSettings `site_version`
- Extension activate: constraint SemVer antar pack (`>=`, `^`, `~`, …)
- Manifest first-party: `version` wajib **format SemVer** (`ModuleManifestValidator`)
- CI / lokal: `npm run versions:check` (alignment tiga package + README)
- CI / lokal: `npm run modules:versions:check` (bump pack/tema saat path runtime berubah)

## API versioning policy (singkat)

- Path tetap `/api/v1/...` sampai ada ADR deprecation untuk `v2`.
- Jangan naikkan path hanya karena bump produk SemVer.
- Dokumentasi UI: `/docs/api` (admin+); dynamic CRUD: `dynamic:openapi`.

## Verify

```bash
npm run versions:check
npm run modules:versions:check
npm run docs:links
cd backend && php artisan tinker --execute="echo config('app.version');"
```

## Related

- Changelogs: [update-changelog.md](update-changelog.md)
- HTTP/OpenAPI: [http-api.md](../reference/http-api.md)
- Quality gates: [run-quality-gates.md](run-quality-gates.md)

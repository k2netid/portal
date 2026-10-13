---
id: 2026-09-18-release-and-versioning
title: Release & versioning guide + enforce product SemVer alignment
status: done
scale: M
repo: ja-core_engine
owner: agent
created: 2026-09-18
updated: 2026-09-18
blocks: []
related_adr: []
---

# Task: Release & versioning

## 1. Tujuan

Dokumentasikan SoT SemVer produk + tegakkan alignment package/composer/OpenAPI/`site_version`; perbaiki drift `beta.1` vs CHANGELOG `beta.2`.

## 2. Audit

- Docs: changelog guide ada; tidak ada release/API version policy
- Packages `1.0.0-beta.1`; CHANGELOG punya `[1.0.0-beta.2]`
- `config('app.version')` tidak terdefinisi → `site_version` kosong
- Scramble `API_VERSION` default `0.0.1`; Dynamic OpenAPI hardcoded `1.0.0`
- Janari `theme.json` version `V.2.0` non-SemVer

## 3. Plan

- [x] Guide `docs/guides/release-and-versioning.md` + index links
- [x] `App\Support\ProductVersion` + `config/app.php` + scramble + DynamicOpenApiBuilder
- [x] Align package/composer/README ke `1.0.0-beta.2`; `.env.example`
- [x] SemVer pattern schema + ModuleManifestValidator; fix Janari theme
- [x] `scripts/check-versions.mjs` + CI + agent:verify
- [x] Changelog + tutup brief

## 4. Log

| Waktu | Catatan |
| :--- | :--- |
| 2026-09-18 | Audit + implementasi + verify |

## 5. Hasil

- Guide + `npm run versions:check` (CI docs-links job)
- Product manifests `1.0.0-beta.2`
- `config('app.version')` → package.json / `APP_VERSION`
- Manifest SemVer enforced; Janari `2.0.0`

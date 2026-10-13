---
id: 2026-09-18-modular-versioning-discipline
title: Disiplin SemVer per modul/tema (hard allowlist + forward-only)
status: done
scale: M
repo: ja-core_engine
owner: agent
created: 2026-09-18
updated: 2026-09-18
blocks: []
related_adr: []
---

# Task: Modular versioning discipline

## 1. Tujuan

Policy + CI: perubahan runtime pack/tema wajib bump `manifest.json` / `theme.json` + CHANGELOG; docs-only tidak; baseline forward-only.

## 2. Plan

- [x] Extend release-and-versioning + pointers
- [x] `scripts/check-module-versions.mjs`
- [x] Wire npm + CI
- [x] Verify + sync downstream + tutup

## 3. Log

| Waktu | Catatan |
| :--- | :--- |
| 2026-09-18 | Implementasi + smoke fail/pass + Core 1.0.1 |

## 4. Hasil

- Guide section Module/theme SemVer; locales = PATCH
- `npm run modules:versions:check` in `agent:verify` + CI (`fetch-depth: 0`)
- Core `manifest.json` bumped `1.0.1` for current runtime diffs
- Smoke: bump missing → fail; restored → pass

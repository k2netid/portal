---
id: 2026-09-18-scramble-openapi-warnings
title: Perbaiki Scramble OpenAPI export (GEN001 + VR002)
status: done
scale: M
repo: ja-core_engine
owner: agent
created: 2026-09-18
updated: 2026-09-18
blocks: []
related_adr: []
---

# Task: Perbaiki Scramble OpenAPI export

## 1. Tujuan

`php artisan scramble:export` / `npm run docs:openapi` bersih dari **1 error GEN001** dan **10 warning VR002**.

## 2. Audit (current state)

- ERR GEN001: `Publishing\SettingController@bulkUpdate` — action dipakai bersama `update()` (PUT settings + POST bulk-update).
- VR002: validasi bergantung `$user`/`$member`/`$contentType`/`$rules` yang null saat evaluasi statis Scramble (User profile, Role, DataModel, FileManager, Content, Member profile/email/directory).

## 3. Diskusi / keputusan

- GEN001: ekstrak private `applyBulkSettings` — jangan biarkan route action memanggil route action lain.
- VR002: nullsafe / `Rule::unique->ignore` yang aman; Form-friendly static rules; FileManager inline helper calls; Content cek body publish setelah validate.
- Dynamic CRUD (`api/v1/dynamic/*`): **exclude** dari Scramble (`scramble.api_path.exclude`) — SoT OpenAPI-nya `dynamic:openapi` / `DynamicOpenApiBuilder`.

## 4. Plan

- [x] Audit `scramble:export -v`
- [x] Fix controllers/support
- [x] Exclude `api/v1/dynamic` dari Scramble
- [x] Re-export verify 0 error / 0 warning
- [x] Changelog + tutup brief

## 5. Log proses

| Waktu | Catatan |
| :--- | :--- |
| 2026-09-18 | Audit verbose: 1 ERR GEN001 + 10 WARN VR002 |
| 2026-09-18 | Fix Publishing/Core/Member controllers + support |
| 2026-09-18 | Sisa 1 WARN DataModel store → exclude `api/v1/dynamic` |
| 2026-09-18 | `scramble:export -v` + `npm run docs:openapi` → 0 ERR / 0 WARN |

## 6. Hasil

- `docs/api/console.openapi.json` regenerasi tanpa ERR/WARN
- Dynamic artifacts tetap dari `dynamic:openapi`

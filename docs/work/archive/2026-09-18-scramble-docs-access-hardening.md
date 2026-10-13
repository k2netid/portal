---
id: 2026-09-18-scramble-docs-access-hardening
title: Harden Scramble /docs/api access (no local bypass)
status: done
scale: S
repo: ja-core_engine
owner: agent
created: 2026-09-18
updated: 2026-09-18
blocks: []
related_adr: []
---

# Task: Harden Scramble docs access

## 1. Tujuan

`/docs/api` dan `/docs/api.json` tidak boleh terbuka di `APP_ENV=local` pada host LAN.

## 2. Keputusan

Ganti `Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess` dengan `EnsureApiDocsAccess` yang selalu mengecek `Gate::viewApiDocs` (admin+). Guest UI → redirect console sign-in; JSON/guest/non-admin → 403.

## 3. Plan

- [x] Middleware + wire `config/scramble.php`
- [x] Feature tests
- [x] Docs + changelog
- [x] Verify HTTP + tests

## 4. Hasil

- Guest: UI redirect, JSON 403
- Admin: 200
- Editor: 403

---
id: 2026-10-13-downstream-structure-polish-p2
title: "P2: theme locations sync docs, Billing pack docs, seeder-only identity"
status: done
scale: S
repo: k2net-portal
owner: agent
created: 2026-10-13
updated: 2026-10-13
blocks: []
related_adr: [ADR-022, ADR-023]
---

# Task: Downstream structure polish P2

## 1. Tujuan

Menutup gap dokumentasi struktur (3 lokasi tema + identitas seeder-only) dan parity docs modul Billing, tanpa mengubah runtime/`theme.json` defaults (aman sync upstream).

## 2. Hasil

- `docs/themes/locations-and-sync.md`
- `docs/product/k2net-identity-and-theme-defaults.md`
- `frontend/src/modules/Billing/{README,CHANGELOG}.md`
- `backend/Modules/Billing/README.md`
- Pointer di `AGENTS.md` + `docs/themes/README.md`

## 3. Out of scope (tetap ditunda)

- Rewrite social URLs di `theme.json` → akun K2NET
- Rename `composer.json` package name
- Redis/DB prefix rename di live env

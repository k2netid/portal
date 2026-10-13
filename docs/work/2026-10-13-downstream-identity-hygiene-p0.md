---
id: 2026-10-13-downstream-identity-hygiene-p0
title: "P0 hygiene identitas downstream K2NET (aman untuk merge upstream)"
status: done
scale: S
repo: k2net-portal
owner: agent
created: 2026-10-13
updated: 2026-10-13
blocks: []
related_adr: []
---

# Task: Downstream identity hygiene P0

## 1. Tujuan

Menyelaraskan lapisan identitas clone `k2net-portal` agar agen/manusia tidak mengira repo ini adalah `ja-core_engine`, tanpa merusak jalur sync upstream.

## 2. Audit (current state)

- Runtime sudah K2NET (`K2netDeploymentSeeder`, tema `layung`).
- README / CHANGELOG / package names / docs hubs masih berbicara sebagai core engine.
- `AGENTS.md` link handoff/runbook putus.
- `backend/composer` PHAR ~3.2MB ter-track meski di `.gitignore`.

## 3. Diskusi / keputusan

- **Banner + rewrite README/AGENTS**, bukan rewrite massal `docs/guides|reference|architecture` (`.upstream-sot.md`).
- **Pertahankan** `backend/composer.json` name `jejakawan/ja-core-engine`.
- **Tunda** rename Redis/DB prefixes di `.env.example` (hanya `APP_NAME`).

## 4. Plan

### In scope

- [x] `git rm --cached backend/composer`
- [x] README, AGENTS, CONTRIBUTING, docs hubs banners
- [x] `package.json` / FE name + x-agent scope
- [x] CHANGELOG preamble + billing Unreleased pointer
- [x] `.env.example` / `backend/.env.example` `APP_NAME=K2NET`

### Out of scope (dilanjut di P1 / P2)

- ADR collision / work archive / smkn6 leftovers → P1 (`2026-10-13-downstream-docs-and-adr-hygiene-p1`)
- theme.json social defaults, composer package rename → P2

## 5. Hasil

- Identitas metadata mengarah ke K2NET; sync upstream tetap aman (mirror docs + composer package name utuh).
- Ops links: `../docs/ja-dev/k2net/agent-handoff.md`, `../docs/ja-srv/k2net/runbook.md`.

## 6. Verify

- `npm run docs:links`
- `npm run versions:check`

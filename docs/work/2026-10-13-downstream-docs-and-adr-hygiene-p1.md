---
id: 2026-10-13-downstream-docs-and-adr-hygiene-p1
title: "P1: ADR-028 collision fix, work archive, smkn6 leftovers"
status: done
scale: S
repo: k2net-portal
owner: agent
created: 2026-10-13
updated: 2026-10-13
blocks: []
related_adr: [ADR-023, ADR-028]
---

# Task: Downstream docs & ADR hygiene P1

## 1. Tujuan

Menghilangkan bentrok nomor ADR-023, merapikan indeks work log, dan membersihkan leftover SMKN6 di repo K2NET — tanpa menyentuh runtime.

## 2. Hasil

- Services-hub / SRI / Services+Team ADR → **ADR-028** (ex misnumbered ADR-023).
- ADR-023 tetap theme registry packs (SoT kode + indeks).
- Brief Sep 18–19 diarsip ke `docs/work/archive/`.
- Hapus `test:e2e:staging:smkn6` dan `scripts/templates/nginx-smkn6-staging.conf`.
- Docs tema: default produk K2NET = `layung`; konversi Solusi/Tim → Services/Team + alias.

## 3. Out of scope

- theme.json social defaults Jejakaawan → K2NET (P2 branding decision)
- composer.json package rename
- Redis/DB prefix rename

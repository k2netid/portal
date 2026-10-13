---
id: 2026-09-18-deferred-cinematic-nav-and-archetype-slots
title: Janari cinematic-nav + PluginSlot pada halaman archetype
status: done
scale: M
repo: ja-core_engine
owner: agent
created: 2026-09-18
updated: 2026-09-18
blocks: []
related_adr:
  - ADR-018
---

# Task: Deferred — cinematic-nav Janari + archetype PluginSlot

## 1. Tujuan

Tutup deferred dari CMS baseline: (1) Janari Home pakai host `cinematic-nav` saja; (2) `after_hero` pada halaman archetype; (3) hygiene indeks `docs/work/README.md`.

## 2. Audit

- Janari `Home.vue` masih mount `SectionNavDots`; plugin `floating_overlay` sudah ada di host tapi self-hide karena `.janari-nav-dots`.
- CMS baseline pages sudah `after_hero`; archetype sebagian besar belum.
- `docs/work/README.md` hanya indeks 1 brief.

## 3. Keputusan

- Hapus `SectionNavDots.vue` + wiring Home; host tetap pemilik `floating_overlay`.
- Archetype: `after_hero`; Post layung/sarangenge: `after_post_content` + `sidebar_article`.
- Hygiene: indeks work lengkap.

## 4. Plan

- [x] Hygiene work README
- [x] Janari → cinematic-nav
- [x] Archetype PluginSlot
- [x] Docs/versions + verify + sync downstream

## 5. Log

| Waktu | Catatan |
| :--- | :--- |
| 2026-09-18 | Audit; implementasi; versions bump |

## 6. Hasil

- Deleted `janari/components/shared/SectionNavDots.vue`; Home relies on `cinematic-nav`
- `after_hero` on all non-Post theme pages; Post layung/sarangenge slotted
- Versions: janari `2.0.5`, layung `1.0.6`, sarangenge `2.0.6`, sareupna `1.1.4`, Layout `1.1.3`
- Host contract side-nav section updated

## 7. Status akhir

`done`

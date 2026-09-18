---
id: 2026-09-18-theme-cms-baseline-alignment
title: Align fitur CMS lintas tema (SoC khas tetap)
status: done
scale: L
repo: ja-core_engine
owner: agent
created: 2026-09-18
updated: 2026-09-18
blocks: []
related_adr:
  - ADR-004
  - ADR-006
  - ADR-018
  - ADR-022
---

# Task: Align fitur CMS lintas tema

## 1. Tujuan

Pastikan **kontrak CMS yang harus sama** ada dan align di janari / layung / sarangenge / sareupna, tanpa mengorbankan **fitur khas** tiap tema (SoC).

## 2. Hasil

### P0 — kontrak CMS
- Sareupna About own page, SEO/identity, enums, menus, sample pages/posts, `contact_form_slug`
- Sarangenge Contact via Reach slug
- Layung + Sareupna `menus` in theme.json
- Docs CMS baseline checklist

### P1 — schema honesty
- Sareupna customizer theme-only; drop dead side-nav keys
- Remove `social_instagram_feed_*` (use extension)
- Floating dock keys → platform schema
- Layung blog documented as widgets
- Sareupna Pricing/Solusi i18n

### P2 — polish
- `after_hero` on CMS baseline pages (Home/About/Contact/Blog/Page) all themes; Layung About alias fixed
- sample-data menus dict contract documented; sareupna menus converted (seeder was skipping list form)
- Janari `SectionNavDots` vs `cinematic-nav` documented (keep Janari local; no re-dupe elsewhere)
- `theme-host-contract.md` + seed guide updated

### Versions
janari `2.0.2` · layung `1.0.3` · sarangenge `2.0.3` · sareupna `1.1.2` · Layout host `1.1.1`

### Verify
- `npm run modules:versions:check` OK
- ESLint on P0/P1 touched paths OK

### Deferred (optional)
- Migrate Janari Home off local `SectionNavDots` onto `cinematic-nav` only
- Archetype pages (PricingIsp, Programs, …) PluginSlot — not CMS baseline

## 3. Plan (checklist)

### P0
- [x] all items

### P1
- [x] all items

### P2
- [x] PluginSlot standar audit + wire CMS pages
- [x] Reference sample-data kontrak
- [x] Docs non-obvious (host contract, side-nav)
- [x] SectionNavDots consolidation → **document only** (Janari may keep)

## 4. Log

| Waktu | Catatan |
| :--- | :--- |
| 2026-09-18 | Analisa; gas P0; lanjut P1; lanjut P2 → `done` |

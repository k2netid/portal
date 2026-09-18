# Themes — Indeks Dokumentasi

Dokumentasi **lintas-repo** untuk paket tema publik di Core Engine.

> **SoT UI/paket tema** tetap di kode:  
> `frontend/src/modules/Layout/views/themes/<slug>/readme.md`  
> Folder ini hanya **indeks + ADR theme-scoped**. Jangan menduplikasi README tema.

## Paket tema

| Tema | Fokus | SoT di kode | Docs di sini | Changelog |
| :--- | :--- | :--- | :--- | :--- |
| [Janari](janari/README.md) | CMS korporat / reference | [`.../janari/readme.md`](../../frontend/src/modules/Layout/views/themes/janari/readme.md) | Indeks | [`CHANGELOG`](../../frontend/src/modules/Layout/views/themes/janari/CHANGELOG.md) |
| [Layung](layung/README.md) | ISP / fiber / MSP | [`.../layung/readme.md`](../../frontend/src/modules/Layout/views/themes/layung/readme.md) | Indeks + ADR-001, 007, 008 | [`CHANGELOG`](../../frontend/src/modules/Layout/views/themes/layung/CHANGELOG.md) |
| [Sarangenge](sarangenge/README.md) | Sekolah / kampus | [`.../sarangenge/readme.md`](../../frontend/src/modules/Layout/views/themes/sarangenge/readme.md) | Indeks | [`CHANGELOG`](../../frontend/src/modules/Layout/views/themes/sarangenge/CHANGELOG.md) |
| [Sareupna](sareupna/README.md) | Cloud / developer platform | [`.../sareupna/readme.md`](../../frontend/src/modules/Layout/views/themes/sareupna/readme.md) | Indeks | [`CHANGELOG`](../../frontend/src/modules/Layout/views/themes/sareupna/CHANGELOG.md) |

## Cross-cutting (bukan per-tema)

- [Architecture: Theme System](../architecture/04-theme-system.md) — host vs theme, SoC/SoT customizer
- [ADR-018](../adr/ADR-018-theme-sarangenge-side-nav-presets-and-viewport-scroll-snap.md) — plugin platform `cinematic-nav` / slot contracts (**scope: core**)
- [ADR-022](../adr/ADR-022-upstream-core-curation-and-generic-theme-seeder-architecture.md) — generic theme demo seeders

Kebijakan: [`DOCUMENTATION.md`](../DOCUMENTATION.md).

## CMS baseline (alignment lintas tema)

Kontrak fitur yang **harus sama** vs yang **tetap khas** per tema:  
[`docs/work/2026-09-18-theme-cms-baseline-alignment.md`](../work/2026-09-18-theme-cms-baseline-alignment.md)

### Checklist kontrak (P0)

Setiap paket tema publik harus punya:

| Item | Catatan |
| :--- | :--- |
| Own `pages/About.vue` bila `enable_about=true` | Jangan andalkan fallback parent chrome |
| Contact → Reach via `contact_form_slug` | Jangan hardcode `/public/forms/contact/submit` |
| `menus` di `theme.json` | Minimal: header, footer (+ opsional col/sidebar) |
| `*PublicSeo` + identity composable | Dipakai host `FrontendLayout` / chrome |
| sample-data `pages` + `posts` | Demo seed Publishing |
| `disabled_page_behavior` | Enum: `message` \| `redirect` saja |
| Customizer schema theme-only | Platform identity / floating dock / colors dari host `global.settings.schema.json` |
| Instagram | Extension `instagram-feed` + slots — **bukan** `social_instagram_feed_*` di tema |
| Side-nav | Host `cinematic-nav` (ADR-018); keys `home_side_nav_dots\|style\|show_mobile` |
| PluginSlot | `after_hero` on CMS pages; `before_footer` host-only — see [theme-host-contract](../../frontend/src/modules/Layout/views/themes/theme-host-contract.md) |
| sample-data menus | Dict keyed by location (list form is ignored by seeder) |
| Page filenames | English **and theme-native** (`Solutions` ≠ Layung `Services` ≠ school `Programs`) |

Ringkas: About/Contact(Reach slug)/Blog/Search/menus/SEO/sample-data/disabled enums align; section archetype (ISP, PPDB, terminal, …) tetap SoC paket.

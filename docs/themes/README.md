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
| [Sareupna](sareupna/README.md) | (paket tema) | [`.../sareupna/readme.md`](../../frontend/src/modules/Layout/views/themes/sareupna/readme.md) | Indeks | [`CHANGELOG`](../../frontend/src/modules/Layout/views/themes/sareupna/CHANGELOG.md) |

## Cross-cutting (bukan per-tema)

- [Architecture: Theme System](../architecture/04-theme-system.md) — host vs theme, SoC/SoT customizer
- [ADR-018](../adr/ADR-018-theme-sarangenge-side-nav-presets-and-viewport-scroll-snap.md) — plugin platform `cinematic-nav` / slot contracts (**scope: core**)
- [ADR-022](../adr/ADR-022-upstream-core-curation-and-generic-theme-seeder-architecture.md) — generic theme demo seeders

Kebijakan: [`DOCUMENTATION.md`](../DOCUMENTATION.md).

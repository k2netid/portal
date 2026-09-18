# 04. Theme System — Host, Paket Tema, Customizer (SoC / SoT)

Explanation singkat arsitektur tema publik di **Jejakawan Core Engine**. Kontrak detail (naming, schema, allowed imports) **tidak digandakan di sini** — lihat lapisan L0 di kode.

---

## 1. Mental model

```
┌─────────────────────────────────────────────────────────┐
│  HOST (platform)                                        │
│  Layout/customizer/  — shell, merge schema, API save    │
│  engine/plugins/     — floating extensions, slots       │
│  FrontendLayout      — slot mounting                    │
└───────────────────────────┬─────────────────────────────┘
                            │ contract
                            ▼
┌─────────────────────────────────────────────────────────┐
│  THEME PACKAGE  views/themes/<slug>/                    │
│  theme.json · ui/ · pages/ · customizer/ · locales/     │
│  SoT visual & theme-scoped settings                     │
└─────────────────────────────────────────────────────────┘
```

| Layer | Tanggung jawab | SoT |
| :--- | :--- | :--- |
| **Host / platform** | Customizer shell, merge schema, preview canvas, persistence API | `frontend/src/modules/Layout/customizer/` |
| **Platform settings** | Keys `scope: platform` (identitas global, layout modes, …) | `customizer/platform/schema/` |
| **Theme package** | UI publik, CSS tokens, halaman, theme-scoped settings | `views/themes/<slug>/` |
| **Theme customizer extension** | Sidebar bindings, schema theme-only, visibility | `views/themes/<slug>/customizer/` |
| **Plugins / slots** | Fitur lintas-tema (mis. cinematic-nav, social dock) | `engine/plugins/` + ADR-018 |

Inspirasi industri: Shopify (`settings_schema` vs section schema) dan WordPress (`theme.json` vs templates).

---

## 2. Pointer wajib (L0 / L1)

| Dokumen | Isi |
| :--- | :--- |
| [`customizer/naming-conventions.md`](../../frontend/src/modules/Layout/customizer/naming-conventions.md) | Naming keys, scope platform vs theme |
| [`theme-host-contract.md`](../../frontend/src/modules/Layout/views/themes/theme-host-contract.md) | Import yang diizinkan dari host |
| [`docs/themes/README.md`](../themes/README.md) | Indeks tema + ADR theme-scoped |
| README per tema | `views/themes/<slug>/readme.md` |

---

## 3. SoC yang wajib dijaga

1. **Jangan** taruh aturan CSS spesifik tema di `frontend/src/styles/` global shell.
2. **Jangan** import komponen console/dashboard ke dalam paket tema publik.
3. **Jangan** hardcode identitas klien (logo legal, nomor telepon riil, jurusan sekolah) di core — lihat ADR-022 / `AGENTS.md`.
4. Fitur yang dipakai **lebih dari satu tema** naik ke host/plugin (contoh: ADR-018), bukan digandakan per tema.

---

## 4. ADR terkait (core)

| ADR | Topik |
| :--- | :--- |
| [ADR-002](../adr/ADR-002-customizer-page-isolation-and-site-branding.md) | Isolasi halaman customizer & branding situs |
| [ADR-003](../adr/ADR-003-customizer-canvas-proportional-zoom-and-mockup-frames.md) | Zoom kanvas & mockup frames |
| [ADR-004](../adr/ADR-004-floating-social-dock-and-customizer-controls.md) | Floating social dock |
| [ADR-005](../adr/ADR-005-brand-visual-styles-live-preview-sync-and-smart-poppers.md) | Live preview sync |
| [ADR-006](../adr/ADR-006-master-layout-modes-and-homepage-sections.md) | Master layout modes |
| [ADR-010](../adr/ADR-010-builder-viewport-preview-scaling-and-adaptive-responsive-toolbar.md) | Viewport builder |
| [ADR-011](../adr/ADR-011-visual-builder-modular-extension-and-license-gating.md) | Visual builder modular |
| [ADR-018](../adr/ADR-018-theme-sarangenge-side-nav-presets-and-viewport-scroll-snap.md) | Plugin SoC/SoT & slot contracts |
| [ADR-022](../adr/ADR-022-upstream-core-curation-and-generic-theme-seeder-architecture.md) | Generic theme demo seeders |

ADR tema Layung: [`docs/themes/layung/`](../themes/layung/).

---

## 5. Quality gates terkait tema

```bash
php artisan theme:seed --all
npm run agent:verify
```

Kebijakan dokumentasi: [`DOCUMENTATION.md`](../DOCUMENTATION.md).

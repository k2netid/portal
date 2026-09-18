# Reference: Contracts index (L0)

Lokasi kanonis kontrak kode. **Jangan menduplikasi** isi schema di sini.

| Kontrak | Path kanonis | Catatan |
| :--- | :--- | :--- |
| Module manifest JSON Schema | [`docs/extensions/module-manifest.schema.json`](../extensions/module-manifest.schema.json) | Validasi `manifest.json` |
| Module product contract | [`docs/extensions/module-contract.md`](../extensions/module-contract.md) | Dual boot + gating checklist |
| Extension lifecycle | [`docs/extensions/lifecycle.md`](../extensions/lifecycle.md) | activate / deactivate / uninstall |
| Install profiles | [`docs/extensions/install-profiles.md`](../extensions/install-profiles.md) | `core` / `cms` / `cms_site` |
| Customizer naming | [`frontend/.../customizer/naming-conventions.md`](../../frontend/src/modules/Layout/customizer/naming-conventions.md) | Platform vs theme keys |
| Theme host contract | [`frontend/.../theme-host-contract.md`](../../frontend/src/modules/Layout/views/themes/theme-host-contract.md) | Import yang diizinkan |
| Theme package manifest | `frontend/src/modules/Layout/views/themes/<slug>/theme.json` | Per tema |
| Theme customizer schema | `.../themes/<slug>/customizer/schema.settings.json` | Theme-scoped settings |
| Platform settings schema | `frontend/src/modules/Layout/customizer/platform/schema/` | `scope: platform` |
| HTTP / OpenAPI | [`reference/http-api.md`](http-api.md) | Scramble + `dynamic:openapi` |
| Backend module layout | [`architecture/02-backend-standards.md`](../architecture/02-backend-standards.md) | Pola Laravel modul |
| Frontend module layout | [`architecture/03-frontend-standards.md`](../architecture/03-frontend-standards.md) | Pola Vue modul |
| Data Studio vs CCK | [`architecture/data-studio-vs-cck.md`](../architecture/data-studio-vs-cck.md) | Batas domain data |
| Documentation policy | [`DOCUMENTATION.md`](../DOCUMENTATION.md) | SoT / SoC / Diátaxis / ADR |

## Themes

Indeks: [`docs/themes/README.md`](../themes/README.md) — SoT UI tetap di `views/themes/<slug>/readme.md`.

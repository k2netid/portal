# Reference: Modules catalog

SoT per modul = `README.md` + `CHANGELOG.md` di folder modul (wajib).  
Core: changelog per domain `System` / `Infra` / `Security` (+ index).  
Tema: `views/themes/<slug>/CHANGELOG.md`. Dual-layer: [update-changelog.md](../guides/update-changelog.md).

## Backend — `backend/Modules/`

| Modul | Peran ringkas | README |
| :--- | :--- | :--- |
| **Core** | Kernel: System / Infra / Security | [README](../../backend/Modules/Core/README.md) |
| **Layout** | Themes, customizer host, theme seeders | [README](../../backend/Modules/Layout/README.md) |
| **Site** | Public site / theme activation surface | [README](../../backend/Modules/Site/README.md) |
| **Publishing** | Editorial / content publishing pack | [README](../../backend/Modules/Publishing/README.md) |
| **Library** | Content library / editorial fields | [README](../../backend/Modules/Library/README.md) |
| **Media** | Media library | [README](../../backend/Modules/Media/README.md) |
| **Forms** | Dynamic forms | [README](../../backend/Modules/Forms/README.md) |
| **Mail** | Mail — golden sample optional module | [README](../../backend/Modules/Mail/README.md) |
| **Member** | Member area | [README](../../backend/Modules/Member/README.md) |
| **Newsletter** | Newsletter | [README](../../backend/Modules/Newsletter/README.md) |
| **Search** | Search | [README](../../backend/Modules/Search/README.md) |
| **Analytics** | Analytics | [README](../../backend/Modules/Analytics/README.md) |
| **CmsAi** | CMS AI assist | [README](../../backend/Modules/CmsAi/README.md) |

## Frontend — `frontend/src/modules/`

| Modul | README |
| :--- | :--- |
| Core | [README](../../frontend/src/modules/Core/README.md) |
| Layout | [README](../../frontend/src/modules/Layout/README.md) |
| Publishing | [README](../../frontend/src/modules/Publishing/README.md) |
| Library | [README](../../frontend/src/modules/Library/README.md) |
| Media | [README](../../frontend/src/modules/Media/README.md) |
| Forms | [README](../../frontend/src/modules/Forms/README.md) |
| Mail | [README](../../frontend/src/modules/Mail/README.md) |
| Member | [README](../../frontend/src/modules/Member/README.md) |
| Newsletter | [README](../../frontend/src/modules/Newsletter/README.md) |
| Search | [README](../../frontend/src/modules/Search/README.md) |
| Analytics | [README](../../frontend/src/modules/Analytics/README.md) |
| CmsAi | [README](../../frontend/src/modules/CmsAi/README.md) |

> **Site** terutama backend; FE publik hidup di paket tema `Layout/views/themes/<slug>/`.

## Related

- Kontrak pack: [module-contract.md](../extensions/module-contract.md)
- Overview tier: [01-overview-and-tier-design.md](../architecture/01-overview-and-tier-design.md)
- Tambah modul: [add-optional-module.md](../guides/add-optional-module.md)

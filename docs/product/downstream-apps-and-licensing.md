# Downstream apps & licensing

## Peran repo ini

**Jejakawan Core Engine** = kernel reusable. Downstream apps (contoh: CMS penuh, portal sekolah, hub operasional) **extend** kernel dengan modul tambahan dan branding sendiri.

## Hub lisensi (JA-CP)

| Komponen | Repo | Peran |
| :--- | :--- | :--- |
| Licensing & heartbeat | `ja-control-plane` | Aktivasi key, tier, grace period |
| Runtime kernel | `ja-core_engine` | Menjalankan app; sync status via `license:check` |

Env relevan:

- `services.jacp.url` — URL control plane (default produksi Jejakawan)
- Settings `license_key`, `license_type`, `license_status`

UI: **Settings → License** — sync manual ke JA-CP.

## OAuth / SSO

Core engine **bisa** bertindak sebagai **OAuth2 identity provider** untuk app downstream (`ja-platform`, tenant apps, dsb.). Copy lama menyebut "control plane" — yang benar: **instance core engine ini** sebagai IdP.

## Pola membangun app baru

1. Fork atau submodule `ja-core_engine` @ `main`
2. Tambah **optional packs** (Mail contract) — lihat [external-module-packaging.md](../extensions/external-module-packaging.md)
3. Atau scaffold produk: `scripts/bootstrap-downstream-app.sh`
4. Register `modules_statuses.json` + Module Registry activate
5. (Opsional) Aktivasi lisensi via JA-CP untuk tier Pro/Enterprise

CMS domains diekstrak dari `ja-cms` sebagai pack (`publishing`, `media`, `forms`, …) — bukan dimasukkan ke `Modules/Core`.

## Editions (konsep)

| Edition | Isi tipikal |
| :--- | :--- |
| **Community** | Core kernel, tanpa key komersial |
| **Starter / Pro** | Extension premium, builder modules, watermark removal |
| **Enterprise / White-label** | Multi-site, branding console, priority sync |

Detail tier = kontrak bisnis di JA-CP; matrix fitur di `LicenseService::getFeaturesMatrix()`.

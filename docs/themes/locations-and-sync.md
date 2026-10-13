# Theme locations & sync (K2NET / downstream)

Tiga lokasi tema di tree ini punya peran berbeda. Jangan “merge” atau edit sembarangan.

| Lokasi | Peran | Git | Edit? |
| :--- | :--- | :--- | :--- |
| `frontend/src/modules/Layout/views/themes/<slug>/` | **SoT pengembangan** — Vue pages, `theme.json`, customizer, locales | Tracked | **Ya** — satu-satunya tempat edit fitur tema |
| `backend/theme-packs/theme-<slug>/manifest.json` | Pack tipis untuk Module Registry / lisensi (ADR-023) | Tracked | Hanya bump `version` / metadata pack, bukan UI |
| `backend/resources/themes/<slug>/` | **Salinan runtime** agar staging/prod bisa scan tema setelah `frontend/` tidak ikut publish | **Gitignored** | Jangan edit manual — hasil sync |

## Alur sync

```
edit FE themes/<slug>/
        │
        ▼
npm run deploy:assets   # atau scripts/sync-frontend-assets-to-backend.sh
        │
        ├─► frontend/dist → backend/public/   (SPA assets)
        └─► FE themes/*  → backend/resources/themes/*  (rsync --delete per slug)
```

Slug yang di-sync: `janari`, `layung`, `sarangenge`, `sareupna`.

Discovery pack: `backend/theme-packs/` → `sys_extensions` (`type: theme`).  
Path runtime scan: `ThemeViews` / Layout prefer `resources/themes` bila ada.

## Aturan agen

1. Ubah UI/settings tema di **FE SoT** saja.
2. Setelah build/deploy staging, pastikan sync assets dijalankan (jangan andalkan salinan `resources/themes` yang basi).
3. Jangan commit isi `backend/resources/themes/` (sudah di `backend/.gitignore`).
4. Pack manifest di `theme-packs/` ikut SemVer discipline modul (`npm run modules:versions:check`).

Lihat juga: [`docs/themes/README.md`](README.md) · [ADR-023](../adr/ADR-023-first-party-themes-as-registry-packs-and-license-quotas.md) · [`AGENTS.md`](../../AGENTS.md).

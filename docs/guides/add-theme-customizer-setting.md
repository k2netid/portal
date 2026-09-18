# How-to: Add a theme customizer setting

Tambah satu setting baru tanpa melanggar SoC host vs tema.

## Prerequisites

- Baca L0: [`naming-conventions.md`](../../frontend/src/modules/Layout/customizer/naming-conventions.md)
- Baca explanation: [04-theme-system.md](../architecture/04-theme-system.md)
- Putuskan scope: **platform** (semua tema) vs **theme** (satu slug)

## Steps — setting platform (host)

1. Tambah definisi di `frontend/src/modules/Layout/customizer/platform/schema/` (key dengan `scope: "platform"`).
2. Pastikan sidebar group ada di `platform/sidebar.groups.json` bila kategori baru.
3. Jangan taruh key platform di `views/themes/<slug>/customizer/schema.settings.json`.
4. Uji di Theme Customizer: ubah nilai → live preview → save → reload.

## Steps — setting theme-scoped

1. Buka paket tema: `frontend/src/modules/Layout/views/themes/<slug>/customizer/`.
2. Tambah key di `schema.settings.json` (hanya scope tema; ikuti prefix/naming di L0).
3. Daftarkan binding preview di `bindings.registry.json` bila perlu highlight di canvas.
4. Update sidebar tema (`sidebar.navigation.json` / `sidebar.pages.json`) bila item navigasi baru.
5. Baca nilai di UI tema via composable host (`useTheme` / data bindings) — jangan import komponen console.
6. Uji isolasi: aktifkan tema lain; setting theme-A tidak boleh bocor ke theme-B.

## Do / Don't

| Do | Don't |
| :--- | :--- |
| Namespace key sesuai naming conventions | Hardcode brand klien di schema core |
| Co-locate schema tema di folder tema | CSS tema di `frontend/src/styles/` global |
| Naikkan fitur lintas-tema ke plugin/host (ADR-018) | Copy-paste komponen yang sama ke tiap tema |

## Related

- Host contract: [`theme-host-contract.md`](../../frontend/src/modules/Layout/views/themes/theme-host-contract.md)
- Themes index: [themes/README.md](../themes/README.md)

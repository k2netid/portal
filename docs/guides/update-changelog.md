# How-to: Update changelogs

Dual-layer changelog — **modul dulu, root ringkas**.  
Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## Prerequisites

- Baca rule: `.cursor/rules/module-documentation.mdc`
- Tentukan lapisan yang berubah (lihat tabel)

## Lapisan mana yang diisi?

| Perubahan | Modul / tema / domain CHANGELOG | Root `CHANGELOG.md` |
| :--- | :--- | :--- |
| API, permission, UI, perilaku pack | **Wajib** di modul BE dan/atau FE yang tersentuh | Satu baris di `[Unreleased]` + pointer |
| Core System / Infra / Security | **Wajib** di `Modules/Core/{System\|Infra\|Security}/CHANGELOG.md` (dan FE setara); index `Modules/Core/CHANGELOG.md` hanya rollup | Satu baris bila user-visible |
| Hanya docs/workflow/ADR (tidak ubah runtime) | Opsional | **Wajib** ringkas di root |
| Host customizer / multi-tema (Layout) | **Layout** BE/FE | Satu baris bila user-visible |
| Hanya satu paket tema (`views/themes/<slug>/`) | **`themes/<slug>/CHANGELOG.md`** + ringkas di Layout FE bila perlu | Satu baris bila user-visible |

Jangan copy-paste esai modul ke root. Root = release notes.

## Core domain changelogs

Kernel `Modules/Core` (dan FE `modules/Core`) memakai **tiga changelog domain**:

- `System/CHANGELOG.md`
- `Infra/CHANGELOG.md`
- `Security/CHANGELOG.md`

File `Core/CHANGELOG.md` = **index/rollup** saja. Saat menyentuh satu domain, update file domain itu.

## Steps

1. Buka CHANGELOG modul yang tepat. Pastikan ada section:

```markdown
## [Unreleased]

### Added
- …

### Changed
- …

### Fixed
- …
```

2. Tulis bullet di kategori yang benar (`Added` / `Changed` / `Fixed` / `Deprecated` / `Removed` / `Security`).
3. Link ADR / task brief bila relevan.
4. Update root `CHANGELOG.md` → `[Unreleased]` dengan **satu baris** pointer, contoh:

```markdown
- Docs: sistem dokumentasi SoT/Diátaxis + WORKFLOW agen (lihat `docs/WORKFLOW.md`).
```

5. Saat rilis/tag SemVer produk: pindahkan `[Unreleased]` root → `## [x.y.z] - YYYY-MM-DD`. Modul boleh tetap `[Unreleased]` sampai pack di-version terpisah.

## Theme packages

Path: `frontend/src/modules/Layout/views/themes/<slug>/CHANGELOG.md`

- Catat perubahan **khusus slug itu** (halaman, token CSS, customizer theme-scoped).
- Perubahan host/platform → `frontend/src/modules/Layout/CHANGELOG.md` (+ BE Layout bila API).
- Keputusan arsitektur tema → ADR di `docs/themes/<slug>/`, bukan pengganti changelog.

## Verify

- [ ] Modul tersentuh punya entri `[Unreleased]`
- [ ] Root punya pointer jika user/release-facing atau docs sistem
- [ ] Tidak ada secret di changelog

## Related

- Template header: [changelog-module.md](../templates/changelog-module.md)
- DoD: [WORKFLOW.md](../WORKFLOW.md)
- Start task: [start-a-task.md](start-a-task.md)

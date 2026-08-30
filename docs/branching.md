# Git branching — `ja-core_engine`

Update: 2026-08-30

Model: **trunk + short-lived worktrees**. Hanya `main` yang long-lived. Integrasi besar boleh ada **satu** branch sementara, lalu merge & hapus.

## Jenis branch

| Jenis | Pola nama | Basis | Umur | Kapan dipakai |
| :--- | :--- | :--- | :--- | :--- |
| **Trunk** | `main` | — | Selamanya | Kernel (+ pack yang sudah layak rilis). Satu-satunya default PR target setelah integrasi selesai. |
| **Integration** | `integrate/<domain>` | `main` | Gelombang besar saja → **merge ke `main` lalu hapus** | Contoh: `integrate/cms` (ex-`fix/module-registry-p0-kernel-lock`). Max satu integrate aktif per domain besar. |
| **Feature** | `feat/<area>-<ringkas>` | `main` *atau* `integrate/<domain>` bila menyentuh line itu | Hari–minggu | Kerja harian; PR → basis; **hapus** setelah merge. |
| **Hotfix** | `fix/<ringkas>` | `main` (atau tag rilis) | Pendek | Bug mendesak di trunk; PR kecil; hapus setelah merge. |

**Dilarang:** long-lived `develop`, `mail`, `publishing`, `member`, dll. Domain = nama *feature/integrate*, bukan trunk kedua.

## Alur

```
main
 └── feat/… / fix/…         ← kerja harian dari trunk
 └── integrate/<domain>     ← hanya gelombang besar baru (sementara → merge & hapus)
```

1. Semua kerja harian → branch dari **`main`**, PR ke **`main`**.
2. Gelombang besar (jarang) → `integrate/<domain>` dari `main`, lalu PR ke `main` dan **hapus**.
3. ~~`integrate/cms`~~ → **merged** PR #14 (2026-08-30); hapus remote/local sisa.

## Hygiene GitHub

- Setelah merge: hapus branch remote (+ lokal).
- Jangan force-push `main`.
- `integrate/*` boleh rebase ke `main` berkala (tim sepakat); feature di atasnya ikut rebase/merge.
- Branch `feat/mail-*` lama: **sudah** terserap ke `integrate/cms` dan dihapus di remote (2026-08-30). Tutup PR mail terbuka di GitHub UI bila masih ada.

## Snapshot aktif (2026-08-30)

| Branch | Status |
| :--- | :--- |
| `main` | Kernel + CMS packs (PR #14) |
| ~~`integrate/cms`~~ | **Merged** → hapus sisa remote/local |
| ~~`feat/mail-*`~~ | **Dihapus** — sudah terserap sebelum merge CMS |

Lihat juga: [architectural-status.md](architectural-status.md) · [AGENT_START_HERE.md](AGENT_START_HERE.md)

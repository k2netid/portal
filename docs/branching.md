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
 └── integrate/cms          ← sementara (CMS packs + honesty pass)
      └── feat/cms-theme-…  ← kerja harian di line CMS
 └── feat/mail-quota-…      ← pack/kernel kecil langsung ke main (setelah CMS merge)
 └── fix/session-cookie-…
```

1. Kernel / pack yang sudah di `main` → branch dari **`main`**.
2. Kerja di gelombang CMS yang belum merge → branch dari **`integrate/cms`**, PR ke `integrate/cms`.
3. Selesai honesty + quality gate → PR **`integrate/cms` → `main`**, hapus `integrate/cms`.
4. Setelah itu semua pack CMS ikut `main`; feature baru dari `main`.

## Hygiene GitHub

- Setelah merge: hapus branch remote (+ lokal).
- Jangan force-push `main`.
- `integrate/*` boleh rebase ke `main` berkala (tim sepakat); feature di atasnya ikut rebase/merge.
- Branch `feat/mail-*` lama: triage (cherry-pick/merge ke `integrate/cms` atau `main` bila masih relevan) lalu hapus.

## Snapshot aktif (2026-08-30)

| Branch | Status |
| :--- | :--- |
| `main` | Kernel thin + deps |
| `integrate/cms` | Line CMS packs — ditahan merge sampai honesty pass |
| ~~`feat/mail-*`~~ | **Dihapus** — sudah terserap di `integrate/cms` (`ship JA-Mail P1–P2 stack with honesty hardening`) |

Lihat juga: [architectural-status.md](architectural-status.md) · [AGENT_START_HERE.md](AGENT_START_HERE.md)

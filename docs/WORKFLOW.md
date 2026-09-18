> **Source of Truth:** `ja-core_engine/docs/WORKFLOW.md` — mirrored copy. Do not edit here.
> Re-sync: `bash /home/jejakawan/dev/ja-core_engine/scripts/sync-docs-guides-reference-downstream.sh`

# Alur Kerja Standar Agen — Jejakawan Core Engine

Kontrak wajib untuk setiap pekerjaan di `ja-core_engine` (manusia atau agen AI).  
Melengkapi [`DOCUMENTATION.md`](DOCUMENTATION.md) (SoT docs) dan [`AGENTS.md`](../AGENTS.md) (batasan repo).

---

## 1. Urutan kanonis (jangan dibalik)

```
┌─────────┐   ┌──────────┐   ┌────────┐   ┌───────────┐   ┌──────────┐   ┌────────────┐
│ 1 AUDIT │ → │ 2 DISKUSI│ → │3 RENCANA│ → │4 IMPLEMENT│ → │5 VERIFY  │ → │6 CATAT/TUTUP│
│ discovery│   │ decide   │   │ plan    │   │ build     │   │ DoD      │   │ record     │
└─────────┘   └──────────┘   └────────┘   └───────────┘   └──────────┘   └────────────┘
     │              │
     │              └── lewati jika keputusan sudah jelas / trivial
     └── selalu: pahami state aktual (kode + docs) sebelum ubah
```

| Fase | Nama industri | Output dokumen | Kapan wajib |
| :--- | :--- | :--- | :--- |
| **1. Audit** | Discovery / current-state | Catatan di task brief § Audit | Selalu (ringkas OK) |
| **2. Diskusi** | Design review / decide | Chat + opsi; **ADR** jika arsitektur | Jika ada trade-off / SoC / multi-repo |
| **3. Rencana** | Implementation plan | Task brief § Plan + acceptance | Medium+; trivial boleh 3–5 bullet |
| **4. Implementasi** | Build | Diff kode + update L0/L1 bila kontrak berubah | Setelah rencana disetujui (atau trivial) |
| **5. Verifikasi** | Definition of Done | Bukti perintah gate | Selalu |
| **6. Catat & tutup** | Record / handoff | Status task, CHANGELOG modul, tutup brief | Selalu |

**Bukan:** implementasi dulu baru “audit belakangan”.  
**Bukan:** ADR setelah merge sebagai formalitas — ADR **sebelum** atau **bersamaan** keputusan, bukan dekorasi.

---

## 2. Skala pekerjaan (shortcut yang diizinkan)

| Skala | Contoh | Fase yang boleh dipadatkan |
| :--- | :--- | :--- |
| **XS** | Typo docs, link putus, copy string | Audit 2 baris → implement → `docs:links` / verify ringan → catat di PR/chat |
| **S** | Bug lokal 1 modul, 1 setting customizer | Audit singkat → plan bullet → implement → `agent:verify` → CHANGELOG modul |
| **M** | Fitur lintas file, RBAC, theme seed | Full brief di `docs/work/` → semua fase |
| **L** | Kontrak modul baru, SoC host/theme, multi-repo | Brief + **Diskusi user** + ADR bila perlu → baru implement |
| **XL** | Restruk docs/arsitektur besar | Brief + ADR/policy dulu → implementasi bertahap |

Jika ragu skala: anggap **M**.

---

## 3. Di mana setiap artefak hidup

| Artefak | Lokasi | SoT? |
| :--- | :--- | :--- |
| Kebijakan docs | `docs/DOCUMENTATION.md` | Ya |
| Alur kerja ini | `docs/WORKFLOW.md` | Ya |
| Task aktif / arsip kerja | `docs/work/` | Ya (riwayat pekerjaan) |
| Template | `docs/templates/` | Ya |
| Keputusan arsitektur | `docs/adr/` atau `docs/themes/<slug>/` | Ya |
| How-to harian | `docs/guides/` | Ya |
| Ops host / kredensial | `/home/jejakawan/dev/docs/` (workspace) | Terpisah — jangan campur ke engine |
| Handoff mesin/tenant | workspace `docs/…/agent-handoff.md` | Ops, bukan task fitur engine |

---

## 4. Status task (wajib satu nilai)

Gunakan tepat satu status di frontmatter task:

| Status | Arti |
| :--- | :--- |
| `proposed` | Brief dibuat, belum disetujui |
| `approved` | Rencana OK, belum coding |
| `in_progress` | Sedang implementasi |
| `blocked` | Menunggu keputusan/user/deps — tulis blocker |
| `verify` | Kode selesai, gate berjalan |
| `done` | Gate lulus + catatan hasil diisi |
| `cancelled` | Dibatalkan — tulis alasan |
| `superseded` | Diganti task/ADR lain — link pengganti |

Agen **tidak** boleh menandai `done` tanpa § Hasil + bukti verify.

---

## 5. Definition of Done (pintu tutup)

Checklist minimum sebelum `done`:

1. [ ] Scope sesuai rencana (tidak creep tanpa update brief)
2. [ ] SoC dihormati (core vs tema vs tenant)
3. [ ] Docs alignment: L0/L1/L2 sesuai [`DOCUMENTATION.md`](DOCUMENTATION.md)
4. [ ] `npm run agent:verify` (atau subset yang disepakati di brief untuk XS)
5. [ ] RBAC/tema/i18n dijalankan **jika** menyentuh area itu (lihat guides)
6. [ ] Task brief § Hasil + status `done`
7. [ ] Changelog dual-layer diisi ([update-changelog.md](guides/update-changelog.md))
8. [ ] Modul `README.md` akurat bila surface berubah
9. [ ] Commit hanya jika user minta

---

## 6. Aturan multi-agen

1. **Satu task = satu brief** di `docs/work/YYYY-MM-DD-slug.md` (atau lanjutkan brief yang sama).
2. Sebelum mulai: baca brief + `status`. Jika `in_progress` milik sesi lain → jangan overwrite; append § Log.
3. Jangan menghapus § Audit/Rencana lama — append amend dengan tanggal.
4. Keputusan yang mengubah kontrak lintas modul → usulkan ADR, jangan “silent architecture”.
5. Tenant-specific → kerjakan di repo downstream; di core hanya pola generik.
6. Setelah update `guides/` / `reference/`: `npm run docs:sync-downstream` jika perlu mirror.

---

## 7. Pintu masuk cepat

| Butuh | Buka |
| :--- | :--- |
| Mulai kerja baru | [guides/start-a-task.md](guides/start-a-task.md) |
| Template brief | [templates/task-brief.md](templates/task-brief.md) |
| Indeks kerja | [work/README.md](work/README.md) |
| How-to teknis | [guides/README.md](guides/README.md) |

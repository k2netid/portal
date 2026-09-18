# How-to: Start a standard task (agen)

Alur wajib: **Audit → Diskusi? → Rencana → Implement → Verify → Catat**.  
Detail: [`WORKFLOW.md`](../WORKFLOW.md).

## Prerequisites

1. Baca [`AGENT_START_HERE.md`](../AGENT_START_HERE.md) + [`AGENTS.md`](../../AGENTS.md)
2. Pastikan repo/scope benar (`ja-core_engine` vs downstream)
3. Tentukan skala XS–XL ([WORKFLOW §2](../WORKFLOW.md))

## Steps

### 1. Audit (selalu)

- Baca kode & docs terkait (jangan andalkan chat lama saja)
- Catat: apa yang ada sekarang, gap, risiko, file tersentuh
- Untuk M+: buat file dari template:

```bash
# contoh nama
cp docs/templates/task-brief.md docs/work/$(date +%F)-short-slug.md
```

Isi frontmatter `status: proposed` + § Audit.

### 2. Diskusi (jika perlu)

- Ada trade-off / SoC / multi-repo / breaking → **stop & diskusi user**
- Keputusan arsitektur → draft ADR (`proposed`) sebelum implementasi besar
- XS/S dengan jalur jelas → boleh lanjut tanpa putaran diskusi panjang

### 3. Rencana

- Isi § Plan: langkah, out-of-scope, acceptance criteria
- Set `status: approved` setelah user setuju (atau brief eksplisit “gas” di chat untuk S/M yang sudah di-scope)
- Jangan coding di luar acceptance tanpa amend rencana

### 4. Implementasi

- Set `status: in_progress`
- Append § Log singkat per sesi agen (apa yang dikerjakan)
- Update L0/L1 jika kontrak berubah; ADR bila keputusan baru

### 5. Verifikasi

- Set `status: verify`
- Jalankan gate di brief (default: `npm run agent:verify`)
- Tambahan relevan: `rbac:sync`, `theme:seed`, `i18n:check`, `docs:links`

### 6. Catat & tutup

- Isi § Hasil (perilaku baru, file kunci, sisa follow-up)
- `status: done` hanya jika DoD di WORKFLOW terpenuhi
- Update `CHANGELOG.md` modul/tema + pointer root bila perlu ([update-changelog.md](update-changelog.md))
- Commit **hanya jika user minta**

## Verify

- [ ] Satu status valid di frontmatter
- [ ] Brief punya Audit + Plan + Hasil (untuk M+)
- [ ] Gate tercatat (perintah + lulus/gagal)

## Related

- Template: [task-brief.md](../templates/task-brief.md)
- Indeks: [work/README.md](../work/README.md)
- Quality: [run-quality-gates.md](run-quality-gates.md)

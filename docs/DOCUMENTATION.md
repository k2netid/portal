# Kebijakan Dokumentasi — Jejakawan Core Engine

Panduan ini adalah **kontrak dokumentasi** untuk repo `ja-core_engine`. Semua agen dan kontributor wajib mengikutinya sebelum menambah atau memindah dokumen.

---

## 1. Tujuan

Dokumentasi harus:

1. Menggambarkan proyek dari aspek pemrograman yang relevan (domain, backend, frontend, theme, identity/RBAC, extensions, security, i18n, upstream/downstream, quality gates).
2. Menjaga **Single Source of Truth (SoT)** — tidak menduplikasi kontrak yang sudah hidup di kode.
3. Menjaga **Separation of Concerns (SoC)** — core vs tema vs tenant/deployment.
4. Tetap selaras dengan codebase aktual (docs mengikuti kode, bukan sebaliknya untuk fakta implementasi).

---

## 2. Lapisan SoT (Source of Truth)

| Lapisan | Isi | Lokasi kanonis |
| :--- | :--- | :--- |
| **L0 — Code contract** | Schema, types, manifest, naming | Colocated di kode (mis. `module-manifest.schema.json`, `naming-conventions.md`, `theme.json`) |
| **L1 — Module docs** | Cara modul bekerja, API permukaan | `backend/Modules/*/README.md`, `frontend/src/modules/*/README.md` (+ `CHANGELOG.md`) |
| **L2 — Architecture** | Cross-cutting: tier, theme host, security, i18n | `docs/architecture/` |
| **L3 — Decisions** | Kenapa X dipilih (immutable) | `docs/adr/` (core); `docs/themes/<slug>/` (ADR tema) |
| **L4 — Product / ops** | Downstream, licensing, bootstrap | `docs/product/` |
| **L5 — Evidence** | Audit, investigasi | `docs/audit/` (core saja). Audit/ADR tenant → repo downstream |

**Aturan:** `docs/` menjelaskan konteks dan **menaut** ke L0/L1. Jangan menyalin ulang schema/types ke L2 kecuali ringkasan singkat yang mudah basi.

---

## 3. Diátaxis — jenis dokumen

Setiap dokumen baru harus masuk salah satu kuadran:

| Jenis | Pertanyaan | Contoh |
| :--- | :--- | :--- |
| **Tutorial** | “Saya baru — bawa saya jalan” | `AGENT_START_HERE.md`, bootstrap downstream |
| **How-to** | “Saya mau X — langkahnya?” | seed theme, packaging modul, sync RBAC |
| **Reference** | “Apa kontraknya?” | JSON Schema, naming conventions, module contract |
| **Explanation** | “Kenapa arsitekturnya begini?” | overview tier, Data Studio vs CCK, ADR |

Hindari dokumen hibrida panjang. Lebih baik dua file pendek yang saling link.

---

## 4. SoC — apa boleh di repo ini

| Boleh di `ja-core_engine/docs` | Tidak boleh (pindah ke tempat lain) |
| :--- | :--- |
| Arsitektur kernel & packs generik | Identitas legal/brand klien (K2NET, SMKN 6, dll.) sebagai SoT operasional |
| ADR keputusan **engine-level** | ADR / audit yang hanya relevan satu portal tenant |
| Indeks tema + ADR **per paket tema** di `docs/themes/<slug>/` | Duplikat README tema (SoT UI tetap di `frontend/.../themes/<slug>/readme.md`) |
| Pola generik plugin/slot/customizer | Deployment seeder, paket harga, nomor telepon, logo klien |
| Audit kualitas **codebase core** | Task tracker fitur yang sudah merge (`feat_*` usang) |

**Shared docs** = prinsip/kontrak generik di core yang dipakai semua portal.  
**Tenant docs** = repo downstream (`k2net-portal`, `smkn6-portal`, …).  
**Workspace ops docs** (`/home/jejakawan/dev/docs/`) = kredensial, runbook host, handoff — bukan duplikat arsitektur engine.

---

## 5. Aturan ADR

1. **Nomor ADR abadi** — jangan renumber setelah diterbitkan.
2. **Jangan rewrite sejarah** — ADR accepted bersifat append-only. Keputusan baru → ADR baru; ADR lama di-mark `superseded` / `deprecated`.
3. **Frontmatter wajib** (YAML di atas judul):

```yaml
---
status: accepted          # proposed | accepted | deprecated | superseded
scope: core               # core | theme:<slug> | downstream:<repo>
date: YYYY-MM-DD
supersedes: []            # daftar ID ADR, opsional
---
```

4. **Penempatan fisik:**
   - `scope: core` → `docs/adr/`
   - `scope: theme:<slug>` → `docs/themes/<slug>/`
   - `scope: downstream:<repo>` → repo target; di core boleh ada **stub pointer** di path lama agar link historis tidak putus.
5. Path lama setelah pindah: sisakan stub 5–15 baris yang menunjuk lokasi baru (jangan biarkan 404 diam-diam).

---

## 6. Alignment docs ↔ kode

| Perubahan | Wajib update |
| :--- | :--- |
| Kontrak L0 (schema/types/naming) | File L0 + mention di L1/L2 yang merujuknya |
| Perilaku modul / tema | `README`/`CHANGELOG` modul atau `themes/<slug>/CHANGELOG.md` (L1) — lihat [update-changelog.md](guides/update-changelog.md) |
| Rilis / docs sistem user-facing | Root `CHANGELOG.md` `[Unreleased]` (pointer ringkas) |
| Cross-cutting architecture | `docs/architecture/` (L2) |
| Keputusan arsitektur baru | ADR baru (L3) |
| Tema publik | README di paket tema (L1) + indeks `docs/themes/` bila perlu |

Sebelum menutup task besar: pastikan link dari `docs/README.md` / `AGENT_START_HERE.md` masih valid, dan `npm run agent:verify` lulus bila menyentuh kode.

---

## 7. Struktur folder kanonis

```
docs/
├── DOCUMENTATION.md          ← kebijakan docs (SoT / Diátaxis)
├── WORKFLOW.md               ← alur kerja agen (audit → … → catat)
├── README.md                 ← peta navigasi
├── AGENT_START_HERE.md       ← tutorial onboarding
├── branching.md
├── architectural-status.md
├── architecture/             ← L2 explanation + standards
├── guides/                   ← Diátaxis how-to
├── reference/                ← Diátaxis reference (indeks kontrak & modul)
├── templates/                ← task brief & template lain
├── work/                     ← brief tugas + status (aktif / archive)
├── adr/                      ← L3 core decisions (+ stub pointer bila perlu)
├── themes/                   ← indeks tema + ADR theme-scoped
├── extensions/               ← kontrak pack / lifecycle / RBAC seed
├── product/                  ← downstream & licensing
├── api/                      ← generated OpenAPI artifacts
└── audit/                    ← evidence core
```

> Dokumen usang / tenant (`feat_sarangenge`, audit K2NET, ADR-009 penuh) hidup di repo downstream (`k2net-portal`, `smkn6-portal`) — **jangan** diarsipkan ulang di core.

### Peta Diátaxis (isi saat ini)

| Kuadran | Lokasi |
| :--- | :--- |
| Tutorial | `AGENT_START_HERE.md`, `product/bootstrap-downstream-app.md` |
| How-to | `guides/` |
| Reference | `reference/`, `extensions/*schema*`, `docs/api/` (generated), L0 di kode |
| Explanation | `architecture/`, `adr/`, `architectural-status.md` |

Mirror ke downstream (bukan SoT): `npm run docs:sync-downstream` → guides + reference + **architecture** + policy → `k2net-portal`, `smkn6-portal`, `ja-cms`.

---

## 8. Pintu masuk per aspek pemrograman

Lihat indeks di [`README.md`](README.md). Ringkas:

1. Domain & modul → `architecture/01-overview…` + `reference/modules.md`
2. Backend → `architecture/02-backend-standards.md` + `backend/Modules/*/README.md`
3. Frontend → `architecture/03-frontend-standards.md` + `frontend/src/modules/*/README.md`
4. Theme & customizer → `architecture/04-theme-system.md` → L0 di kode · how-to: `guides/add-theme-customizer-setting.md`
5. Identity & RBAC → ADR-014…021 · how-to: `guides/sync-rbac-and-capabilities.md`
6. Extensions lifecycle → `extensions/lifecycle.md` · how-to: `guides/add-optional-module.md`
7. Security → `architecture/06-security-and-governance.md` · how-to: `guides/security-checks.md`
8. i18n → `architecture/05-i18n-guidelines.md` · how-to: `guides/add-i18n-keys.md`
9. Upstream/downstream → ADR-022 + `product/*` · sync guides: `npm run docs:sync-downstream`
10. Quality gates → `guides/run-quality-gates.md` · `reference/cli-commands.md` · `npm run docs:links`
11. HTTP API → `reference/http-api.md` · artifacts: `docs/api/`

---

## 9. Completeness checklist

Lihat [`COMPLETENESS.md`](COMPLETENESS.md) untuk status fondasi dokumentasi.


> **Source of Truth:** `ja-core_engine/docs/COMPLETENESS.md` — mirrored copy. Do not edit here.
> Re-sync: `bash /home/jejakawan/dev/ja-core_engine/scripts/sync-docs-guides-reference-downstream.sh`

# Documentation completeness — ja-core_engine

Checklist fondasi docs (update saat menambah kuadran/lapisan baru).

| Area | Status | Lokasi |
| :--- | :---: | :--- |
| Kebijakan SoT / SoC / Diátaxis | ✅ | `DOCUMENTATION.md` |
| Alur kerja agen | ✅ | `WORKFLOW.md`, `guides/start-a-task.md`, `work/` |
| Tutorial onboarding | ✅ | `AGENT_START_HERE.md`, `CONTRIBUTING.md` |
| How-to (guides) | ✅ | `guides/` |
| Reference | ✅ | `reference/`, `extensions/*schema*` |
| Explanation / standards | ✅ | `architecture/` |
| ADR core + theme stubs | ✅ | `adr/`, `themes/` |
| HTTP / OpenAPI | ✅ | `reference/http-api.md`, `npm run docs:openapi`, `api/` |
| Testing / QA suite map | ✅ | `reference/testing.md`, `guides/run-e2e-playwright.md` |
| Dual-layer changelog | ✅ | modul + tema + root |
| Release / SemVer alignment | ✅ | `guides/release-and-versioning.md`, `npm run versions:check`, `npm run modules:versions:check` |
| Core domain changelogs | ✅ | `Core/{System,Infra,Security}/CHANGELOG.md` (BE+FE) |
| Link checker + CI | ✅ | `npm run docs:links`, CI `docs-links` (+ product & module version checks) |
| Mirror downstream | ✅ | guides + reference + **architecture** + policy → k2net, smkn6, smkn1cijulang, ja-cms |
| Ops/kredensial host | ✅ (terpisah) | `/home/jejakawan/dev/docs/` |

## Saat task docs berikutnya

1. Brief di `work/`
2. Jangan duplikasi L0 di L2
3. `npm run docs:links` + `npm run docs:sync-downstream` jika guides/reference/**architecture**/policy berubah
4. `npm run docs:openapi` setelah ubah API/model fields

# Work: Tim → Team (English + theme-native)

Date: 2026-09-18  
Status: done (core + downstream synced)

## Decision

Same convention as Solutions: English Vue filename + canonical English path; Indonesian path stays as **alias** only. UI copy (ID/SU labels) unchanged.

| Theme | File | Path | Alias | Gate |
| :--- | :--- | :--- | :--- | :--- |
| janari | `Team.vue` | `/team` | `/tim` | (always on) |
| layung | `Team.vue` | `/team` | `/tim` | `enable_team` |
| sarangenge | `Team.vue` | `/team` | `/tim`, `/guru`, `/staf`, … | `enable_team` |

Kept for backcompat (like `page_solusi_*`): setting keys `page_tim_*`, customizer category `Tim Page`.

## Versions

- janari `2.0.4`, layung `1.0.5`, sarangenge `2.0.5`
- Layout module `1.1.2`

## Downstream

Synced FE Layout + Publishing locales + BE `resources/themes` + sample factory/manifest: ja-cms, k2net-portal, smkn6-portal, smkn1cijulang-portal; control-plane janari residual.
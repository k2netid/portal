# Global styles (JA Control Plane)

## Layer model

| Layer | Path | Role |
|-------|------|------|
| **1. Foundation** | `foundation/tokens.css`, `foundation/base-resets.css` | Design tokens (`@theme`), `:root` / `.dark`, form resets — imported by shell Tailwind |
| **2. Shell Tailwind** | `shell/console-tailwind.css`, `shell/public-tailwind.css` | Tailwind v4 + `@source` per shell |
| **3. Shell chrome** | `console.css`, `console-presets.css`, `editor.css`, theme `janari.css` | Shell-specific surfaces |
| **4. Module CSS** | Vite chunks (`mod-content-*`, …) | Vue SFC scoped styles — no per-module Tailwind entry |

## Entrypoints

- `console.html` → `src/main-console.ts` → `shell/console-tailwind.css`
- `index.html` → `src/main-public.ts` → `shell/public-tailwind.css`

Module loaders: `src/modules/bootstrap/*`, `src/modules/deferred/console.ts`.
Bootstrap: `src/engine/bootstrap/console.ts` | `public.ts`.

# Core (kernel)

Platform kernel: System, Infra, Security. Always product-active. Not App Store toggleable.

## Paths

- `app/System/` IAM, settings, extension registry, console menus
- `app/Infra/` Data Studio, backup, webhooks
- `app/Security/` RBAC/ABAC, CSP, SIEM
- Settings Identity owns group `general` (site name). Product packs own `seo` / `comments` / `analytics`

## Agent notes

- First-party modules cannot be uninstalled
- Plugin uninstall must not proceed if deactivate is blocked by reverse dependents
- Kernel `/manage/ai/generate` is gated by `ai_enabled`, not the cms-ai pack

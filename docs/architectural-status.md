# Architectural Status — `main` (canonical kernel)

Update: 2026-08-22

## Status `main`

| Area | Status | Path |
| :--- | :--- | :--- |
| System IAM | **Live** | `backend/Modules/Core/app/System/` |
| Infra (data studio, backup, webhooks) | **Live** | `backend/Modules/Core/app/Infra/` |
| Security (RBAC/ABAC, SIEM, CSP) | **Live** | `backend/Modules/Core/app/Security/` |
| Unified console SPA | **Live** | `frontend/src/modules/Core/` |
| Extension engine | **Live** | Mail manifest + marketplace |
| License → JA-CP | **Live** | `LicenseService`, `license:check` |
| Downstream bootstrap | **Live** | `scripts/bootstrap-downstream-app.sh` |

## Removed from repo (Aug 2026)

| Item | Notes |
| :--- | :--- |
| Branch **`develop`** | CMS line — gunakan repo downstream / fork produk |
| E2E theme/content/member | Bukan scope kernel |
| Router `member-*` guards | DNA ja-control-plane |
| CI `develop` trigger | Single canonical branch |

## Downstream products

Content, themes, member portal, platform billing → **fork `main`** + modul produk sendiri.  
Lihat [bootstrap-downstream-app.md](product/bootstrap-downstream-app.md).

## Cleanup checklist

- [x] Docs SoT + identitas Core Engine
- [x] Hapus branch `develop`
- [x] E2E kernel-only
- [x] Router guards tanpa member
- [x] PHPStan 138 fixes + baseline regen
- [x] Bootstrap scaffold script
- [ ] FileManager PHPStan baseline debt (~85 entries) — optional next pass

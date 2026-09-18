# Changelog — Core / Security (BE)

RBAC/ABAC enforcement, auth hardening, security notifications, perimeter controls under `Modules/Core/app/Security`.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

Index: [`../CHANGELOG.md`](../CHANGELOG.md)

## [Unreleased]

### Fixed
- **Backend API Defense-in-Depth**: Sensitive Core routes gated with Spatie `permission:...` ([ADR-020](../../../../docs/adr/ADR-020-rbac-hierarchy-route-hardening-and-ui-primitives.md)).
- **Seeder Permission Preservation (`CmsRolesSeeder.php`)**: `givePermissionTo` instead of wiping via `syncPermissions` ([ADR-020](../../../../docs/adr/ADR-020-rbac-hierarchy-route-hardening-and-ui-primitives.md)).
- Decoupled `appName` fallback in `SecurityNotificationService` from vendor name to generic kernel default.
- Scramble `/docs/api`: replace local-bypass `RestrictedDocsAccess` with `EnsureApiDocsAccess` (always `viewApiDocs` / admin+).

### Added
- `EnsureApiDocsAccess` middleware + feature tests for Scramble docs UI/JSON gating.

### Changed

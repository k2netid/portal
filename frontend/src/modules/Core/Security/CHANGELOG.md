# Changelog — Core / Security (FE)

RBAC UI primitives, forbidden handling, security-facing console UX.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

Index: [`../CHANGELOG.md`](../CHANGELOG.md)

## [Unreleased]

### Added
- **RBAC UI Primitives**: `v-can` / `v-role` / `<Can>` ([ADR-020](../../../../../docs/adr/ADR-020-rbac-hierarchy-route-hardening-and-ui-primitives.md)).

### Fixed
- **Global 403 Forbidden Event** via `app:forbidden` on API client ([ADR-020](../../../../../docs/adr/ADR-020-rbac-hierarchy-route-hardening-and-ui-primitives.md)).

### Changed

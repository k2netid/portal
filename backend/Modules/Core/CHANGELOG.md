# Changelog — Modules/Core (BE index)

Kernel module rollup. **Detail per domain** (wajib diisi saat ubah domain itu):

| Domain | Changelog |
| :--- | :--- |
| System | [`System/CHANGELOG.md`](System/CHANGELOG.md) |
| Infra | [`Infra/CHANGELOG.md`](Infra/CHANGELOG.md) |
| Security | [`Security/CHANGELOG.md`](Security/CHANGELOG.md) |

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).  
Policy: [`docs/guides/update-changelog.md`](../../../docs/guides/update-changelog.md).

## [Unreleased]

### Added
- Domain changelogs split: System / Infra / Security (see table above).
- See domain files for feature entries (identity, extensions, Data Studio, RBAC hardening, …).

### Changed
- Version 1.0.3: `Setting::setIfMissing()` non-destructive helper to preserve downstream client identity settings during updates.
- This file is now an **index**; do not duplicate long bullets here — link domains instead.
- ADR-023: First-party theme packs as registry packs, tier license quota enforcement, and downgrade remediator.
- PHPStan level 9 strict typing and error resolution across Core modules.

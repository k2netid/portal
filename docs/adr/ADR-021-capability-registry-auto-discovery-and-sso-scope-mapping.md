# ADR-021: Capability Registry Auto-Discovery, Manifest Standardization, and SSO Scope Mapping

## Status
Accepted

## Context
Previously, Role-Based Access Control (RBAC) permissions were defined and seeded imperatively across scattered database seeders (`*PermissionSeeder.php`). This caused several operational friction points:
1. **Lack of a Single Source of Truth (SoT)**: There was no declarative capability catalog mapping which modules, features, actions, and settings exist in the system.
2. **Third-Party & Extension Isolation**: When new modules or third-party extensions were installed or activated, permissions required manual database seed execution or hardcoded migrations.
3. **Inability to Reset to Factory Defaults**: Once role permissions were customized by operators, there was no way to preview the difference or reset roles back to factory default permissions defined by each module.
4. **Mixed Languages & Localization Gaps**: Manifests had inconsistencies where Indonesian text was mixed into configuration files without standard `label_key` paths.
5. **SSO & Identity Provider (IdP) Preparedness**: As the portal architecture evolves toward serving as a central identity provider (IdP) for satellite portals and student/parent services, permissions needed standard OAuth2 / OIDC scope mappings.

## Decision
We implemented an enterprise-grade **Declarative Capability Registry & Permission Discovery Engine**:

### 1. Declarative Capability Schema in `manifest.json`
Every module declares its features, actions, factory default roles, and OAuth2 scopes declaratively in its `manifest.json`:
- **Strict English Standard**: All canonical metadata (`name`, `description`, `label`) in `manifest.json` are strictly 100% English.
- **i18n Translation Key (`label_key`)**: Each action specifies a `label_key` (e.g. `system.capabilities.publishing.contents.view`). Translations reside in frontend locale bundles (`en.json`, `id.json`, `su.json`), with fallback to canonical `label`.
- **Action Types**: Granular action categories (`read`, `create`, `update`, `delete`, `execute`, `manage`, `admin`) to power visual badges and role policies.
- **OAuth2 Scope Mapping**: Each action specifies an `oauth_scope` (e.g., `publishing:read`, `member:manage`, `media:write`) enabling future Laravel Passport / OpenID Connect tokens.

### 2. Backend `CapabilityRegistryService`
Implemented `Modules\Core\System\Services\CapabilityRegistryService`:
- `getDiscoveredManifests()`: Auto-discovers in-tree modules (`Modules/*/manifest.json`) and active DB plugins (`backend/extensions/*/manifest.json`), respecting dual product enablement.
- `getRegistryTree()`: Hierarchical tree structure (Module -> Feature -> Actions).
- `getAllDeclaredPermissions()`: Flat list of Spatie permissions.
- `syncToDatabase()`: Idempotent database synchronization ensuring all declared permissions exist.
- `getDefaultPermissionsForRole(string $roleName)`: Returns declared factory defaults for any standard role (`admin`, `editor`, `author`, `operator`, `security-officer`, `system-admin`, `member`).
- `getRoleDiffWithDefaults(Role $role)`: Computes exact additions (`to_add`) and revocations (`to_remove`) against module defaults.
- `resetRoleToDefaults(Role $role)` & `resetAllRolesToDefaults()`: Resets roles back to factory defaults while protecting the `super` role.

### 3. CLI Command: `php artisan rbac:sync`
Provides console administrators with:
- `php artisan rbac:sync`: Discovers all capabilities and syncs permissions.
- `php artisan rbac:sync --reset-defaults`: Resets all standard roles to module factory defaults.
- `php artisan rbac:sync --role=editor --reset-defaults`: Resets a single role.

### 4. REST API Endpoints
Added under `api/v1/manage/system/roles`:
- `GET /roles/capabilities` (`view roles`): Returns full capability hierarchy and OAuth scopes.
- `GET /roles/{role}/defaults-diff` (`view roles`): Returns additions/removals diff vs defaults.
- `POST /roles/{role}/reset-defaults` (`edit roles|manage roles`): Resets role to factory defaults.
- `POST /roles/reset-all-defaults` (`edit roles|manage roles`): Global factory default reset.

### 5. Frontend UI Revamp (`roles/Index.vue`)
- **Hierarchical Capability Matrix**: Grouped by Module -> Feature with action badges (`READ`, `CREATE`, `UPDATE`, `DELETE`, `EXECUTE`, `MANAGE`, `ADMIN`).
- **Dangerous Action Indicators**: Red danger badges for destructive capabilities.
- **Quick Selection Controls**: "Select All Read", "Select Module", "Clear All" per module/feature.
- **Reset to System Default Modal**: Compares current permissions with module defaults, previewing additions (`+`) and revocations (`-`) before execution.

## Consequences
### Positive
- **Declarative Extensibility**: Installing a new module automatically registers its permissions and role defaults upon activation without touching seeders.
- **Self-Healing RBAC**: Role permission drift can be audited and restored at any time via UI or CLI.
- **Clean Internationalization**: Standardized English in manifest configurations with symmetric locale bundles (`en`, `id`, `su`).
- **SSO / IdP Foundation**: OAuth2 scopes are centrally cataloged and aligned with console permissions.

### Negative / Trade-offs
- Adding new actions requires updating both `manifest.json` and locale dictionaries for Indonesian and Sundanese translations.

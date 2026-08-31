# RBAC, Enable/Disable Strategy & Module Seeders (RFC)

**Status:** Draft RFC — planning only (no implementation yet)  
**Update:** 2026-08-31  
**Audience:** agents and humans touching App Store, Spatie roles, or pack activate hooks  
**Depends on:** [module-contract.md](./module-contract.md), [lifecycle.md](./lifecycle.md), [install-profiles.md](./install-profiles.md)  
**Companion:** [member-area.md](./member-area.md)

---

## 1. Principles

1. **Dual enablement stays:** nwidart boot ≠ product active (`sys_extensions.status`).
2. **Deactivate = gate, not destroy:** no migration rollback, no automatic truncate, no permission row deletion.
3. **Console RBAC ≠ Reader capabilities:** Spatie/`web` for operators; capability flags for `mem_members`.
4. **Seeders must be idempotent:** safe on activate, install profile, and boot heal.
5. **Destructive purge is explicit:** `module:purge` / plugin uninstall with confirmation — never silently on deactivate.

These match the frozen product contract in [module-contract.md](./module-contract.md) and the contribution rule already coded in `ExtensionContributionService` (*permissions seeded on activate, never deleted on deactivate*).

---

## 2. Recommended strategy on enable / disable

### 2.1 Three layers

| Layer | Mechanism | On **activate** | On **deactivate** |
| :--- | :--- | :--- | :--- |
| **L1 Runtime gate** | `extension.active:<slug>` (API) + FE `meta.extension` / nav filter | Routes & menus usable | 403 / hide / soft unavailable |
| **L2 Permission catalog** | Manifest `permissions[]` + pack `*PermissionSeeder` | `firstOrCreate` permission names; grant to roles if present | **Keep rows**; do not delete |
| **L3 Role assignment** | Spatie `role → permissions` | Grant to `super` (+ `admin` when role exists); pack seeders may grant `editor` / `author` | **Default: keep grants** (L1 still blocks). Optional “hard lockdown” later |

```
activate:
  migrate module path → Hook extension_activated → seedPermissions(manifest)
  → PermissionSeeder → domain seeders (idempotent) → status=active → sync menus

deactivate:
  Hook extension_deactivated → hide menus → status=inactive
  → (optional) soft flags / cache bust
  → NO truncate, NO revoke by default, NO rollback
```

### 2.2 Why not revoke permissions on deactivate?

- Operators re-activating packs should not reconfigure roles every time.
- L1 already prevents use of inactive pack APIs.
- Orphan permissions in Spatie are harmless and visible in Role UI.

**Optional hard lockdown** (future flag on App Store): revoke pack permission names from all roles except `super`. Re-activate must re-run seeders. Document as destructive preference, default off.

### 2.3 Data policy

| Action | Domain data (posts, forms, mailboxes, …) |
| :--- | :--- |
| Deactivate | **Preserved** |
| Re-activate | Available again behind L1 |
| Uninstall plugin | Optional purge; first-party Modules/* **cannot** uninstall |
| Explicit purge CLI | Allowed with `--force` + confirmation (to be added) |

---

## 3. Console RBAC (operators)

### 3.1 Stack (live)

- Spatie Laravel Permission, guard `web`, models under `Modules\Core\System\Models`.
- `Gate::before`: role `super` bypasses.
- `Gate::after`: ABAC (`AbacEvaluator`).
- Route middleware: `auth:sanctum` + `permission:…` + usually `extension.active:…`.

### 3.2 Permission sources (three paths)

| Source | When | Example |
| :--- | :--- | :--- |
| `FoundationSeeder` | Fresh seed | users, roles, security, mail, plugins |
| Manifest on activate | `ExtensionContributionService::seedPermissions` | `view members`, `manage members` |
| Pack `*PermissionSeeder` | `extension_activated` + boot heal | Publishing content perms |

### 3.3 Critical gap (production)

`FoundationSeeder` today creates:

- `super`, `system-admin`, `security-officer`
- Spatie role `member` (subscription self-service — **not** public readers)

Pack seeders (e.g. `PublishingPermissionSeeder`) **grant** to roles named `admin`, `editor`, `author` **if they exist**. Those roles are created in **tests** (`TestCase`) but **not** in production foundation seed.

`ExtensionContributionService` also grants new manifest perms to `super` and `admin` — if `admin` is missing, only `super` receives them.

**Required fix (P0 before Member Area scale):**

1. Add `CmsRolesSeeder` (or extend `FoundationSeeder`) creating:

| Role | Rank (guideline) | Purpose |
| :--- | :--- | :--- |
| `admin` | 95 | Site / CMS admin (editorial + pack settings) |
| `editor` | 60 | Publish / approve |
| `author` | 40 | Draft / own content |
| `operator` | 85 | Optional ops without full admin |

2. Run when `INSTALL_PROFILE` is `cms` or `cms_site`, **or** on first CMS pack activate (idempotent).
3. Document Spatie role `member` as **operator subscription** alias; prefer UI label “Subscriber”.

### 3.4 Role vs pack matrix (target)

| Role | Core ops | Publishing | Layout | Media | Forms | Member directory | Mail |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `super` | all | all | all | all | all | all | all |
| `system-admin` | infra / users / security | — | — | — | — | — | use/manage per seed |
| `admin` | settings subset | full CMS | full | full | full | manage | pack-dependent |
| `editor` | — | publish | theme view | view/upload | view | view | — |
| `author` | — | draft/edit own | — | upload | — | — | — |
| `security-officer` | security ops | — | — | — | — | — | — |

Exact permission lists stay in pack seeders; this table is the assignment intent.

---

## 4. Reader capabilities (public Member pack)

See [member-area.md](./member-area.md) §7.

- Guard: `auth:member`.
- **No Spatie** on `mem_members`.
- Capability = f(active extensions, verified email) by default.
- Console permissions `view members` / `manage members` are for **operators** managing the directory only.

---

## 5. Seeder audit (as of 2026-08-31)

### Legend

| Mark | Meaning |
| :--- | :--- |
| ✅ | Idempotent; activate hook and/or boot heal |
| ⚠️ | Partial / missing roles or domain data |
| ❌ | Missing |
| 🔒 | Kernel — always product-active |

### Table

| Pack | Migrations | PermissionSeeder | Domain / default data | Manifest `permissions` | `extension_activated` | `extension_deactivated` listener |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| Core (System/Security/Infra) 🔒 | ✅ | ✅ Foundation | ✅ settings, langs, tasks | N/A | N/A | N/A |
| Site | ❌ | ❌ | ❌ | ❌ | — | ❌ |
| Layout | ✅ | ✅ | ⚠️ Theme sample via `theme:install-sample` CLI | ✅ | ✅ | ❌ |
| Publishing | ✅ | ✅ | ❌ no auto demo posts | ✅ | ✅ | ❌ |
| Library | ✅ | ⚠️ via Publishing seeder | ❌ | ✅ | (via publishing) | ❌ |
| Media | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ |
| Forms | ✅ | ✅ | ✅ `ContactFormSeeder` | ✅ | ✅ | ❌ |
| Newsletter | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ |
| Analytics | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ |
| Search | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ |
| Member | ✅ | ⚠️ manifest-only | ❌ | ✅ | via contribution service | ❌ |
| Mail | ✅ | ⚠️ Foundation | ❌ | ❌ | — | ❌ |
| CmsAi | ✅ | ❌ | ❌ | ❌ | ✅ (hook present) | ❌ |

### Findings

1. **Forms is the golden sample** for activate: permissions + domain `ensure()` + boot heal.
2. **Zero packs listen to `extension_deactivated`** for cleanup — acceptable under “gate not destroy,” but means soft-disable flags (if any) must be designed explicitly.
3. **Domain default data is sparse:** only Forms contact form + Layout theme sample CLI. Publishing/Member/Newsletter lack activate-time demo data.
4. **Member** has no dedicated `MemberPermissionSeeder` (relies on manifest → `super`/`admin` grant).
5. **CmsAi / Site** underspecified for permissions and seeders.

---

## 6. Proposed lifecycle seeder contract

### 6.1 Manifest fields (planned)

```json
{
  "lifecycle": {
    "preserve_data_on_deactivate": true,
    "seeders_on_activate": [
      "Modules\\Forms\\Database\\Seeders\\ContactFormSeeder"
    ],
    "seeders_on_deactivate": [],
    "purge_seeders_on_uninstall": []
  }
}
```

Rules:

- `seeders_on_activate` classes must expose static `ensure(): void` or be invokable seeders that are idempotent.
- `seeders_on_deactivate` default empty; only soft flags / cache, never truncate.
- Hard delete only via uninstall (plugins) or future `php artisan module:purge {slug} --force`.

### 6.2 Orchestrator (planned)

`ExtensionLifecycleOrchestrator` (Core):

| Hook | Steps |
| :--- | :--- |
| Activate | migrate → manifest permissions → run `seeders_on_activate` → pack PermissionSeeder (existing hooks) → menus |
| Deactivate | menus off → run soft deactivate seeders → status inactive |
| Discover / profile apply | same activate path for missing packs |

Keep existing `Hook::action('extension_activated'|'extension_deactivated')` so packs can retain local listeners during migration to orchestrator.

### 6.3 Domain seeder backlog (suggested)

| Pack | Default data candidate |
| :--- | :--- |
| Publishing | Optional “Welcome” post when empty (flag `seed_demo_content`) |
| Member | None required (auth only); optional demo readers **never** in production profiles |
| Newsletter | Default list “General” |
| Layout | Already: `theme:install-sample` — consider invoke from activate when theme empty |
| Search | Index config defaults |
| Analytics | Retention defaults (settings, not rows) |

Demo content must be **opt-in** (`INSTALL_SEED_DEMO=true` or App Store checkbox), never forced on production `cms_site` without consent.

---

## 7. Install profiles interaction

From [install-profiles.md](./install-profiles.md):

| Profile | Effect on RBAC / seeders |
| :--- | :--- |
| `core` | CMS packs deactivated; CMS roles may exist but unused |
| `cms` | Activate CMS family; run CMS roles + pack seeders; Site off |
| `cms_site` | Same + Site; public member portal eligible |

Profile apply should call the same activate path (migrate + seeders), not only flip `status`.

---

## 8. Implementation checklist (when coding starts)

### P0 — RBAC foundation

- [ ] `CmsRolesSeeder` (+ call from Foundation or install profile applicator)
- [ ] Tests: fresh `cms_site` seed → `admin`/`editor`/`author` exist; Publishing perms attached
- [ ] Docs note: Spatie `member` ≠ `mem_members`

### P1 — Lifecycle contract

- [ ] Manifest schema: `lifecycle` + optional `member_area` ([member-area.md](./member-area.md))
- [ ] `ExtensionLifecycleOrchestrator` or extend `ExtensionController::performActivation`
- [ ] Document `extension_deactivated` soft-only policy in [lifecycle.md](./lifecycle.md)

### P2 — Pack seeder parity

- [ ] Member: `MemberPermissionSeeder` assigning directory perms to `admin`
- [ ] Newsletter list default seeder
- [ ] Optional demo content flag for Publishing
- [ ] CmsAi: declare permissions or document as feature-flag only

### P3 — Member portal adaptive gates

- [ ] Implement capabilities + portal registry per [member-area.md](./member-area.md)

---

## 9. Anti-patterns

| Do not | Why |
| :--- | :--- |
| Truncate `pub_*` / `mem_*` on deactivate | Data loss; violates lifecycle.md |
| Delete Spatie permissions on deactivate | Breaks role UI; re-activate pain |
| Put reader ACLs in Spatie `web` guard | Collides with console IAM |
| Assume `admin` role exists without seeding it | Current production gap |
| Seed fake readers on every activate in production | Privacy / noise |

---

## Related code (reference)

| Concern | Path |
| :--- | :--- |
| Activate / deactivate | `backend/Modules/Core/app/System/Http/Controllers/Console/ExtensionController.php` |
| Manifest permissions | `…/Services/ExtensionContributionService.php` |
| Foundation roles | `…/database/seeders/System/FoundationSeeder.php` |
| Forms golden sample | `backend/Modules/Forms/app/Providers/FormsServiceProvider.php` |
| Publishing perms | `backend/Modules/Publishing/app/Database/Seeders/PublishingPermissionSeeder.php` |
| Member pack | `backend/Modules/Member/` |

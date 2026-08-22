# JA-Mail Extension

**Module:** `backend/Modules/Mail/` + `frontend/src/modules/Mail/`  
**Marketplace slug:** `mail`  
**Status:** Optional Pro extension (inactive until activated in App Store)

## Architecture

JA-Mail is a **first-class Laravel + Vue module**, not embedded Core code:

| Layer | Path |
|-------|------|
| Manifest (App Store) | `backend/Modules/Mail/manifest.json` |
| nwidart module | `backend/Modules/Mail/module.json` |
| API routes | `backend/Modules/Mail/routes/api.php` |
| Controllers / models | `backend/Modules/Mail/app/` |
| Frontend SPA | `frontend/src/modules/Mail/` |
| Registry | `frontend/src/modules/Mail/module.ts` → `engine/bootstrap/console.ts` |

## Gating

- **API:** middleware `mail.extension` — requires `sys_extensions.slug=mail` with `status=active`
- **Router:** `meta.extension: 'mail'` — checked in `engine/router/guards.ts`
- **Sidebar:** `extension: 'mail'` on nav item + `TheSidebar.vue`

Activate via **Settings → Extensions (App Store)** before use.

## Capabilities (kernel `main`)

| Feature | Status |
|---------|--------|
| Webmail UI (inbox, compose, folders) | Live |
| Multi-account CRUD + encrypted credentials | Live |
| Send via system SMTP or custom account SMTP | Live |
| Scheduled send (`scheduled_at` + `mail:process-scheduled`) | Live |
| Canned templates / labels / settings | Live |
| IMAP inbox sync | **Not in kernel** — local index refresh only; full IMAP = downstream product line |

## Permissions

Seeded in `FoundationSeeder`:

- `manage system` — required for all JA-Mail API routes (`permission:manage system`)
- `manage personal mail account`
- `manage multi mail accounts`

Super / system-admin bypass via role for account RBAC checks.

## Security model (kernel `main`)

| Control | Implementation |
|---------|----------------|
| Mailbox isolation | `user_id` on `sys_mail_messages`; all queries via `UserMailRepository` |
| Extension gate | `mail.extension` middleware + router `meta.extension` |
| RBAC | `permission:manage system` on API group |
| Send rate limit | `throttle:30,1` on `POST /send` |
| Schedule rate limit | `throttle:20,1` on `POST /messages/schedule` |
| Connection test SSRF | `MailHostValidator` blocks private/reserved IPs |
| Send failures | `MailDispatchException` → HTTP 502 `MAIL_SEND_FAILED` |
| Scheduled dispatch | `mail:process-scheduled` with cache lock + `dispatch_locked_at` |
| Credential logging | `MailAccount` activity log redacts SMTP/IMAP passwords |

## UI stub inventory (honest labels)

These UI surfaces are **preview-only** in kernel webmail until IMAP/full MIME pipeline ships:

| Surface | Kernel behavior |
|---------|-----------------|
| SPF/DKIM/DMARC badges | Labeled **PREVIEW** — not verified against live headers |
| TLS protocol row | Shows “not available (local index only)” |
| Raw MIME headers | Synthetic preview from local message fields |
| Quick reply inline send | Opens reply composer (no direct SMTP from detail pane) |
| Attachment download | Disabled unless `attachment.url` present |
| Sync button | Refreshes local index only (message explains IMAP downstream) |
| AI draft reply button | Disabled preview |

## Cron

`mail:process-scheduled` — seeded every 5 minutes in `ScheduledTaskSeeder`, whitelisted in `ScheduledTask::ALLOWED_COMMANDS`.

## Downstream

Forks that need full webmail (IMAP pull, calendar, etc.) extend `Modules/Mail/` or ship a separate product repo — do not re-embed mail into `Modules/Core`.

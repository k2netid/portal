# JA-Mail Extension

**Module:** `backend/Modules/Mail/` + `frontend/src/modules/Mail/`  
**Marketplace slug:** `mail`  
**Status:** Optional Pro extension (inactive until activated in App Store)

Module READMEs: [backend](../../backend/Modules/Mail/README.md) · [frontend](../../frontend/src/modules/Mail/README.md) · [CHANGELOG](../../backend/Modules/Mail/CHANGELOG.md)

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

See the frozen contract: [module-contract.md](./module-contract.md).

- **Package boot:** nwidart `modules_statuses.json` → Mail always loaded
- **Product active:** `sys_extensions.slug=mail` + `status=active`
- **API:** middleware `mail.extension`
- **Router:** `meta.extension: 'mail'` — checked in `engine/router/guards.ts`
- **Sidebar:** `extension: 'mail'` on nav item + `TheSidebar.vue`
- **FE register:** console bootstrap registers Mail **only when** `active_extensions` includes `mail`

Activate via **Identity → Module Registry & App Store** before use.

## Capabilities (kernel)

| Feature | Status |
|---------|--------|
| Webmail UI (inbox, compose, folders incl. scheduled + archive) | Live |
| Multi-account CRUD + encrypted credentials | Live |
| Send via system SMTP or custom account SMTP | Live |
| Scheduled send + cancel → drafts (`mail:process-scheduled`) | Live |
| Storage quota enforcement on send/schedule | Live |
| Attachment extension + MIME blocklist + path ownership checks | Live |
| Trash retention purge (`mail:purge-trash`) | Live |
| Vacation / OOO on `MailboxIngestService` | Live |
| Soft Media Library register on attach | When Content/Media present |
| In-app notifications on send failure / vacation | Live |
| Canned templates / labels / settings | Live |
| Remote image blocking (`block_remote_images`) | Live |

### Explicitly out of scope (mail server / DNS / MTA)

| Concern | Owner |
|---------|--------|
| IMAP / POP inbox pull | Mail server or separate product — **not** this module |
| SPF / DKIM / DMARC signing & verification | DNS + MTA |
| TLS between MTAs | Mail server |

JA-Mail is an **outbound + local mailbox UI** for the console. Do not track IMAP/SPF/DKIM as kernel backlog.

## AI Copilot (depends on Settings → AI)

Mail AI is **not** standalone. Effective readiness:

`global ai_enabled` ∧ provider API key ∧ mail `ai_enabled` ∧ `ai_scope_drafting` → API field `ai_ready`

| Capability | Kernel status |
|------------|---------------|
| Composer Draft / Polish | Live (gated) |
| Summarize / smart reply / sentiment | Unavailable (locked off) |
| Human-in-the-loop | Always on (AI never sends) |
| PII masking | Optional client redact before prompt |

Global generate API (`POST /manage/ai/generate`) returns `403 AI_DISABLED` when Settings → AI is off.

Agent rule: `.cursor/rules/mail-ai-governance.mdc`

## Permissions

- `use mail` — API + console nav
- `manage personal mail account` / `manage multi mail accounts` — account CRUD depth

## Runtime dependencies

| Dependency | Required? | Notes |
|------------|-----------|-------|
| PostgreSQL or SQLite | Yes | Message/account tables |
| SMTP (`MAIL_*` and/or Settings `mail_*`) | Yes for real send | Dev often uses `MAIL_MAILER=log` |
| Disk `local` | Yes | Attachments under `mail-attachments/` |
| Redis | Optional | Cache + Horizon; vacation rate-limit uses Cache (works with `file`/`database` too) |
| Queue worker | Optional | Needed only if `MAIL_QUEUE_OUTBOUND=true` or vacation/port `queue: true` |
| Horizon / Supervisor | Optional | Prefer `queue:work` or Horizon when queueing outbound |
| IMAP PHP extension / libs | **No** | Not used |
| Spatie Media Library | Soft | Register attach only if Content bridge bound |

Default queue in `.env.example` is `QUEUE_CONNECTION=database` — a worker is enough; Redis/Horizon are platform choices, not Mail-hard requirements.

## Notifications integration

**Direction:** Mail → in-app `sys_notifications` (bell). Not the reverse.

| Event | In-app notification |
|-------|---------------------|
| `MailMessageFailed` | `error` for mailbox user |
| `VacationAutoReplySent` | `info` for mailbox user |

Auth verify / password reset / Security alerts still use Laravel `Mail::` / Notifiable and **bypass** JA-Mail by design (kernel always-on).

Cross-module **outbound** email should use `OutboundMailPortInterface` (not NotificationController).

## Cross-module send

```php
app(OutboundMailPortInterface::class)->send(
    to: 'user@example.com',
    subject: 'Hello',
    htmlBody: '<p>Hi</p>',
    queue: true,
);
```

## Inbound (local)

```php
app(MailboxIngestService::class)->ingest($user, [/* fields */]);
```

Fires `MailMessageReceived`, then vacation. `MailInboundSyncInterface` defaults to local index refresh only.

## Cron

| Command | Seeded schedule |
|---------|-----------------|
| `mail:process-scheduled` | every 5 minutes |
| `mail:process-snoozed` | every 5 minutes |
| `mail:purge-trash` | daily 03:15 |

## Security

Mailbox isolation via `user_id`, extension gate, `permission:use mail`, send/schedule throttles, SSRF host validation on account test, attachment extension blocklist, download path prefix ownership, and storage quota checks on outbound attach.

Optional env: `MAIL_QUEUE_OUTBOUND=true` to dispatch outbound sends via queue.

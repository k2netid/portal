# JA-Mail (backend)

Laravel module: outbound SMTP webmail, multi-account, schedule, trash purge, vacation on ingest.

## Quick map

| Path | Role |
|------|------|
| `app/Http/Controllers/` | API (`MailController`, `MailAccountController`) |
| `app/Services/` | Dispatch, attachments, ingest, vacation, outbound port |
| `app/Contracts/MailInboundSyncInterface.php` | Local index sync (no IMAP in kernel) |
| `app/Events/` / `app/Listeners/` | Domain events → in-app notifications |
| `app/Console/Commands/` | `mail:process-scheduled`, `mail:purge-trash` |
| `routes/api.php` | `auth:sanctum` + `mail.extension` + `permission:use mail` |
| `config/config.php` | `mail_module.queue_outbound` ← `MAIL_QUEUE_OUTBOUND` |
| `database/migrations/` | `sys_mail_*` |
| `tests/Feature/` | PHPUnit |

## Read next

1. [docs/extensions/ja-mail.md](../../../docs/extensions/ja-mail.md) — product/security/deps
2. [CHANGELOG.md](./CHANGELOG.md)
3. Frontend: [frontend/src/modules/Mail/README.md](../../../frontend/src/modules/Mail/README.md)

## Agent notes

- Prefer `OutboundMailPortInterface` for cross-module send.
- Do **not** add IMAP/SPF/DKIM verification here — MTA/DNS concerns.
- Mail AI **depends** on Settings → AI; see `.cursor/rules/mail-ai-governance.mdc`. Only drafting is live.
- Update `CHANGELOG.md` when behavior changes; keep FE README in sync for UI/API contracts.

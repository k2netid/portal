# JA-Mail (frontend)

Vue console module for JA-Mail webmail UI.

## Quick map

| Path | Role |
|------|------|
| `module.ts` | Registers routes + nav with the console bootstrap |
| `router/index.ts` | Route `mail` — `permission: use mail`, `extension: mail` |
| `navigation.ts` | Sidebar Communications → JA-Mail |
| `views/mail/Index.vue` | Main mailbox shell |
| `components/mail/` | Composer, settings, security inspector, etc. |
| `composables/useMailClient.ts` | API client (send FormData, accounts, sync) |

## API base

`/api/v1/manage/mail/*` (Sanctum + active `mail` extension + `use mail`).

## Read next

1. [docs/extensions/ja-mail.md](../../../../docs/extensions/ja-mail.md)
2. Backend: [backend/Modules/Mail/README.md](../../../../backend/Modules/Mail/README.md)
3. [CHANGELOG.md](./CHANGELOG.md)

## Agent notes

- Security inspector explains MTA/DNS auth — do not reintroduce SPF/DKIM “verification” UI backlog.
- Keep permission meta as `use mail` (not `manage system`).
- Update this README + CHANGELOG when routes, forms, or API contracts change.

# How-to: Security checks (dev / pre-merge)

Prosedur singkat sebelum merge perubahan yang menyentuh auth, member, upload, route publik, atau dependensi.

## Prerequisites

- Backend bisa boot; untuk smoke member, API lokal harus jalan
- Baca explanation: [06-security-and-governance.md](../architecture/06-security-and-governance.md)

## Steps

### 1. Dependency audit

```bash
cd backend && composer audit --locked
cd ../frontend && npm audit --audit-level=critical
```

CI juga menjalankan audit ini (lihat `.github/workflows/ci.yml`).

### 2. Quality + auth-related tests

```bash
# dari root
npm run agent:verify
```

Setelah ubah permission / capability:

```bash
cd backend && php artisan rbac:sync
```

### 3. Checklist saat menambah API / route

| Cek | Tindakan |
| :--- | :--- |
| Console API | `auth:sanctum` + permission Spatie yang tepat |
| Pack opsional | middleware `extension.active:<slug>` |
| Member API | guard `auth:member` — **jangan** campur dengan `srv_auth_users` |
| Form/login publik | rate limit + captcha path yang sudah ada |
| Upload | MIME/extension validation; SVG lewat sanitizer |
| Scramble `/docs/api` | Jangan pakai `RestrictedDocsAccess` (bypass di `local`); pakai `EnsureApiDocsAccess` |

### 4. Member auth smoke (opsional, runtime)

Dengan backend di `APP_URL` / `http://127.0.0.1:8000`:

```bash
# dari root
bash scripts/member-security-qa.sh
```

Menguji register → login → logout member dan mengharapkan audit `member_logout`.

### 5. Core API smoke (opsional)

```bash
bash scripts/api-smoke.sh
```

### 6. CSP / inline assets

- Jangan menambah `<script>` / `<style>` inline tanpa **nonce** (middleware Vite nonce).
- Jangan melemahkan CSP di `.env` production hanya untuk “biar jalan”.

### 7. License / install profile

- Production: jangan set `INSTALL_SKIP_LICENSE_CHECKS=true` di luar `local`/`testing`.
- Deactivate pack ≠ hapus data / hapus Spatie permissions.

## Verify

- Audit dependensi bersih (atau temuan kritis ditangani)
- Route baru 401/403 benar untuk user tanpa permission / pack nonaktif
- Member dan console identity tetap terpisah

## Related

- RBAC how-to: [sync-rbac-and-capabilities.md](sync-rbac-and-capabilities.md)
- Member area: [member-area.md](../extensions/member-area.md)
- DAST workflow: `.github/workflows/dast-security-scan.yml`

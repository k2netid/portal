# How-to: Add or update i18n keys

Tambah string UI dengan paritas `id` / `en` / `su` penuh.

## Prerequisites

- Baca aturan: [05-i18n-guidelines.md](../architecture/05-i18n-guidelines.md)
- Tentukan lokasi: modul Core domain, pack FE, atau tema

## Steps

1. Cari file locale yang tepat (simetris tiga bahasa):

```
frontend/src/modules/<Module>/locales/{id,en,su}.json
frontend/src/modules/Layout/views/themes/<slug>/locales/{id,en,su}.json
frontend/src/engine/i18n/messages/{id,en,su}.ts   # global saja
```

2. Tambah key **di ketiga file** dengan struktur JSON yang sama. Naming: *camelCase* nested.

3. Pakai interpolasi `{param}` di string; di Vue:

```vue
{{ t('module.namespace.key', { count }) }}
```

4. Jika menambah bundle tema/pack baru, pastikan terdaftar di `frontend/src/engine/i18n/moduleLocales.ts` (lihat pola `theme.*`).

5. Verifikasi:

```bash
cd frontend
npm run i18n:check          # keys + dangerous braces
npm run i18n:check:full     # paritas penuh
npm run i18n:check:braces   # braces saja
```

`quality:frontend` / `npm run agent:verify` sudah menyertakan i18n check.

## Verify

- UI tidak menampilkan raw key (`module.foo.bar`)
- Ganti bahasa di console/public → string berubah di ketiga locale

## Related

- Explanation: [05-i18n-guidelines.md](../architecture/05-i18n-guidelines.md)
- Scripts: `frontend/scripts/i18n-check-keys.mjs`, `i18n-dangerous-braces.mjs`

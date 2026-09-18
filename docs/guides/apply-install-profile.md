# How-to: Apply install profile

Pilih bentuk produk setelah install: kernel saja, CMS, atau CMS + Site publik.

## Prerequisites

- `migrate` / `migrate:fresh --seed` sudah pernah dijalankan, atau siap menjalankan applicator CLI
- Pahami konsekuensi deactivate (lihat tabel di [install-profiles.md](../extensions/install-profiles.md))

## Steps

### A. Via environment (seed)

Set sebelum seed:

```bash
# core | cms | cms_site
export INSTALL_PROFILE=cms_site
cd backend && php artisan migrate:fresh --seed
```

### B. Via CLI (tanpa fresh)

```bash
cd backend
php artisan ja:apply-install-profile cms          # contoh
php artisan ja:apply-install-profile cms_site
php artisan ja:apply-install-profile core
```

### C. Via Console (operator)

App Store → **Install profile** → pilih opsi (UI menampilkan preview activate/deactivate).  
Atau API preview: `GET …/extensions/install-profile-preview?profile=cms`.

## Verify

| Profile | Apex `/` | Pack aktif (ringkas) |
| :--- | :--- | :--- |
| `core` | Kernel landing | Kernel only |
| `cms` | Kernel landing | CMS family; Site off |
| `cms_site` | Public theme | CMS + Site + Member |

Console tetap di `/auth/console-sign-in` dan `/dash`.

## Related

- Full contract: [install-profiles.md](../extensions/install-profiles.md)
- Lifecycle: [lifecycle.md](../extensions/lifecycle.md)

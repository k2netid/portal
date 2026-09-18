# 02. Backend Standards & Patterns — Jejakawan Core Engine

Panduan standar pengembangan backend Laravel di **Jejakawan Core Engine (`ja-core_engine`)**.

Modul contoh di bawah memakai domain **System** (kernel). Downstream apps menambah modul sendiri di `backend/Modules/{Product}/` — lihat [downstream-apps-and-licensing.md](../product/downstream-apps-and-licensing.md).

> **SoT per modul:** `backend/Modules/{Module}/README.md` (+ `CHANGELOG.md`). Dokumen ini = standar lintas-modul. Kebijakan docs: [`DOCUMENTATION.md`](../DOCUMENTATION.md).

---

## 🏗️ 1. Struktur Modul Backend

Setiap modul di `backend/Modules/{Module}/` memiliki struktur folder terstandardisasi:

```
Modules/Core/app/System/
├── Http/
│   ├── Controllers/
│   │   ├── Console/          # Admin console API
│   │   └── Public/           # Public / guest API bila ada
│   ├── Middleware/
│   └── Requests/             # FormRequest validasi
├── Models/
├── Providers/
├── Services/                 # Business logic
├── Events/
└── Listeners/
Modules/Core/database/
├── migrations/
├── seeders/
└── factories/
Modules/Core/routes/
├── system_api.php            # /api/v1/manage/system/...
└── ...
Modules/Core/tests/
├── Feature/
└── Unit/
```

---

## 📐 2. Pola Desain & Praktik Terbaik

### A. Controllers & API Responses
- **Thin Controllers**: Controller hanya bertugas menerima request, memanggil Service/Model, dan mengembalikan respons terstandardisasi.
- **Standar JSON Response**:
  ```php
  // Sukses
  return response()->json([
      'success' => true,
      'data' => $data,
      'message' => 'Konten berhasil diperbarui.',
  ]);

  // Error / Validasi
  return response()->json([
      'success' => false,
      'message' => 'Data tidak valid.',
      'errors' => $validator->errors(),
  ], 422);
  ```

### B. FormRequest & Validasi
- Selalu gunakan `FormRequest` khusus untuk request mutating (`POST`, `PUT`, `PATCH`, `DELETE`).
- Jangan letakkan aturan validasi inline di dalam Controller.
- Contoh:
  ```php
  namespace Modules\Core\System\Http\Requests;

  use Illuminate\Foundation\Http\FormRequest;

  class UpdateSettingRequest extends FormRequest
  {
      public function authorize(): bool
      {
          return $this->user()->can('manage settings');
      }

      public function rules(): array
      {
          return [
              'key' => ['required', 'string', 'max:255'],
              'value' => ['nullable', 'string'],
          ];
      }
  }
  ```

### C. Services Layer & Eloquent Boot Caching
- Letakkan logika bisnis kompleks di `app/Services/`.
- Gunakan `Cache::rememberForever` untuk data konfigurasi atau data statis yang sering dibaca publik:
  ```php
  // Otomatis invalidasi via boot observer di Model:
  protected static function booted(): void
  {
      static::saved(fn () => Cache::forget('sys_settings_global'));
      static::deleted(fn () => Cache::forget('sys_settings_global'));
  }
  ```

### D. Pencegahan Query N+1
- Pada environment lokal/testing, kernel mengaktifkan `Model::preventLazyLoading(true)` untuk mendeteksi loop N+1.
- Selalu gunakan eager loading:
  ```php
  $users = User::with(['roles', 'permissions'])->paginate(20);
  ```

---

## 🧪 3. Standar Pengujian (Testing)

- Endpoint/service penting wajib punya automated coverage (Feature dan/atau Unit).
- **Pest + PHPUnit** — lihat peta suite: [testing.md](../reference/testing.md).

### Menjalankan

```bash
# dari root
npm run test:backend
npm run test:backend:coverage

# filter
cd backend && php artisan test --filter=ModuleManifestValidatorTest
cd backend && php artisan test --testsuite=Modules
```

`npm run agent:verify` **tidak** menjalankan PHPUnit penuh — sebelum merge BE, jalankan `test:backend` atau andalkan CI job `backend`.

### Di mana menaruh tes

| Jenis | Path |
| :--- | :--- |
| Kernel app-wide | `backend/tests/Unit`, `backend/tests/Feature` |
| Pack first-party | `backend/Modules/<Name>/tests/{Unit,Feature,Security,...}` |
| Config suites | `backend/phpunit.xml` · Pest bind: `backend/tests/Pest.php` |

Pakai helpers di `Tests\TestCase` (`createAdminUser()`, `seedPermissionsAndRoles()`, dll.).

### Prinsip

- `RefreshDatabase` (atau setara) untuk Feature yang menyentuh DB.
- Authorization: user tanpa permission → **403** (atau error_code kontrak pack).
- Validasi: batas min/max, format, unique ignore (hindari VR002 Scramble di controller — lihat OpenAPI guide).
- Extension gates: uji `extension.active:*` inactive → 403.
- Jangan hardcode secret; pakai factories / env testing (`APP_ENV=testing` di phpunit.xml).

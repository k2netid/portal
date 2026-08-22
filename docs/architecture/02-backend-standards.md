# 02. Backend Standards & Patterns — Jejakawan Core Engine

Panduan standar pengembangan backend Laravel di **Jejakawan Core Engine (`ja-core_engine`)**.

Modul contoh di bawah memakai domain **System** (kernel). Downstream apps menambah modul sendiri di `backend/Modules/{Product}/` — lihat [downstream-apps-and-licensing.md](../product/downstream-apps-and-licensing.md).

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

- Seluruh endpoint dan service wajib memiliki pengujian otomatis (Feature & Unit Tests).
- Jalankan test suite:
  ```bash
  cd backend && php artisan test
  ```
- **Prinsip Pengujian**:
  - Gunakan `RefreshDatabase` pada base test case.
  - Uji permission authorization (user tanpa hak akses harus menerima `403 Forbidden`).
  - Uji validasi input batas maksimum/minimum dan format data.

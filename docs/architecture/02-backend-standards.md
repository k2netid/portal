# 02. Backend Standards & Patterns — Jejakawan CMS

Panduan standar pengembangan backend Laravel di **Jejakawan CMS (`ja-cms`)**.

---

## 🏗️ 1. Struktur Modul Backend

Setiap modul di `backend/Modules/{Tier}/{Module}/` memiliki struktur folder terstandardisasi:

```
Modules/Content/Publishing/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Console/          # Controller untuk Admin Console
│   │   │   └── Public/           # Controller untuk Publik / Visitor
│   │   ├── Middleware/
│   │   └── Requests/             # FormRequest Validasi
│   ├── Models/                   # Eloquent Models
│   ├── Providers/                # Module Service Providers
│   ├── Services/                 # Business Logic & Service Layer
│   ├── Events/                   # Domain Events
│   └── Listeners/                # Event Handlers
├── database/
│   ├── migrations/               # Migrasi database spesifik modul
│   ├── seeders/                  # Seeder data awal
│   └── factories/                # Model Factories untuk testing
├── routes/
│   ├── publishing_api.php        # Rute API (/api/v1/manage/publishing/...)
│   └── publishing_web.php        # Rute Web jika ada
└── Tests/
    ├── Feature/                  # Feature Tests (Pest/PHPUnit)
    └── Unit/                     # Unit Tests
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
  namespace Modules\Content\Publishing\app\Http\Requests;

  use Illuminate\Foundation\Http\FormRequest;

  class StoreContentRequest extends FormRequest
  {
      public function authorize(): bool
      {
          return $this->user()->can('manage publishing');
      }

      public function rules(): array
      {
          return [
              'title' => ['required', 'string', 'max:255'],
              'slug' => ['required', 'string', 'max:255', 'unique:pub_contents,slug'],
              'content' => ['nullable', 'string'],
              'status' => ['required', 'in:draft,published,scheduled'],
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
- Pada environment lokal/testing, CMS mengaktifkan `Model::preventLazyLoading(true)` untuk mendeteksi loop N+1.
- Selalu gunakan eager loading:
  ```php
  $contents = Content::with(['category', 'tags', 'author'])->paginate(20);
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

# 05. Internationalization (i18n) Guidelines — Jejakawan Core Engine

Panduan arsitektur translasi dan internasionalisasi multi-bahasa di **Jejakawan Core Engine (`ja-core_engine`)**.

---

## 🌍 1. Standar Bahasa yang Didukung

Jejakawan Core Engine mendukung 3 bahasa utama secara simetris:
1. **`id`**: Bahasa Indonesia (Default Sistem)
2. **`en`**: English (International)
3. **`su`**: Basa Sunda (Regional)

---

## 📁 2. Distribusi File Locale

Translasi diorganisasi secara modular per domain fitur:

```
frontend/src/
├── engine/i18n/
│   ├── messages/{en,id,su}.ts    # Global + moduleLocaleBundles
│   └── moduleLocales.ts          # Pack + theme bundles (theme.sarangenge.*, theme.janari.*)
└── modules/
    ├── Core/{System,Infra,Security}/locales/{en,id,su}.json
    ├── Publishing/locales/{en,id,su}.json
    ├── Layout/locales/{en,id,su}.json
    ├── Layout/locales/builder/{en,id,su}.json
    └── Layout/views/themes/{sarangenge,janari}/locales/{en,id,su}.json
```

---

## 📐 3. Aturan Ketat Pembuatan Kunci Translasi

### A. Simetri Kunci 100% (Zero Missing Keys)
- Setiap kunci yang didefinisikan pada `en.json` **WAJIB** ada di `id.json` dan `su.json`.
- Tidak boleh ada kunci yang dibiarkan hilang atau menghasilkan *raw key string* di antarmuka pengguna.

### B. Konvensi Penamaan Kunci
- Gunakan format *camelCase* untuk penamaan node JSON:
  ```json
  {
    "buttons": {
      "saveChanges": "Simpan Perubahan",
      "discard": "Buang"
    },
    "messages": {
      "saveSuccess": "Halaman berhasil disimpan.",
      "itemDeleted": "{count} item telah dihapus."
    }
  }
  ```

### C. Parameter Interpolation
- Gunakan kurung kurawal `{param}` untuk nilai dinamis, bukan string concatenation manual di template:
  ```vue
  <!-- Benar -->
  <span>{{ t('builder.contextMenu.duplicate', { type: node.name }) }}</span>

  <!-- Salah -->
  <span>{{ t('builder.contextMenu.duplicate') + ' ' + node.name }}</span>
  ```

---

## 🧪 4. Skrip Verifikasi & Quality Gates

Jejakawan Core Engine dilengkapi alat validator otomatis untuk memeriksa kepatuhan i18n sebelum rilis:

1. **Cek Paritas Lengkap**:
   ```bash
   cd frontend && npm run i18n:check:full
   ```
   *Memvalidasi seluruh 3990+ kunci gate di seluruh modul.*

2. **Cek Kurung Kurawal Berbahaya (Dangerous Braces)**:
   ```bash
   cd frontend && npm run i18n:check:braces
   ```
   *Mencegah error sintaks `VueI18n` yang diakibatkan oleh kurung kurawal yang tidak ditutup.*

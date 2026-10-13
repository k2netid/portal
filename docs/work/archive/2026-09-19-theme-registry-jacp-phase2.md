---
id: 2026-09-19-theme-registry-jacp-phase2
title: Integrasi payload lisensi themes.* JA-CP pada LicenseService & heartbeat (Fase 2)
status: done
scale: M
repo: ja-core_engine
owner: agent
created: 2026-09-19
updated: 2026-09-19
blocks: []
related_adr:
  - ADR-023
---

# Task: Integrasi Payload Lisensi themes.* JA-CP (Fase 2)

## 1. Tujuan

Mengimplementasikan integrasi payload lisensi tema `themes.*` dari JA-CP Control Plane ke dalam `LicenseService` dan sinkronisasi heartbeat sesuai **ADR-023 §2.4 & §3**:
1. Menangani payload `themes` dari respons API aktivasi & heartbeat JA-CP (`always_on`, `max_premium_active`, `catalog`).
2. Menerapkan *safe fallback mirror tier* jika payload atau field tertentu absen.
3. Menyimpan kuota dinamis pada setting `license_themes_quota` dan membersihkannya saat deactivation.
4. Memastikan `ThemeDowngradeRemediator` otomatis merevert tema aktif ke `janari` bila heartbeat JA-CP membatasi kuota tema premium.
5. Memperbarui CLI `license:check` untuk menampilkan ringkasan kuota tema aktif.

## 2. Implementasi

1. **`LicenseService.php`**:
   - `getThemeQuota()`: Mendukung pembacaan kuota kustom tersimpan dari setting `license_themes_quota` yang disinkronkan dari respons aktivasi/heartbeat JA-CP. Dilengkapi metode validasi defensif `sanitizeThemeQuotaPayload()` dengan invariant `janari` selalu ada di `always_on` dan `catalog`, serta tier Community/Starter dipaksa `max_premium_active = 0`.
   - `activateLicense()` & `syncHeartbeat()`: Mengekstrak dan memvalidasi payload `themes.*` dari respons JA-CP, menyimpan ke setting `license_themes_quota`, dan menjalankan `ThemeDowngradeRemediator::remediate()`.
   - `deactivateLicense()`: Menghapus setting `license_themes_quota` untuk mengembalikan ke baseline murni Community.
   - `getLicenseStatus()`: Menyertakan field `theme_quota` di level teratas untuk konsumsi API/CLI.
   - `isThemeServeEntitled()`: Menolak tema first-party yang tidak ada di dalam `catalog` lisensi JA-CP.
2. **`ThemeDowngradeRemediator.php`**:
   - Memastikan entri `janari` dibuat dan diaktifkan jika belum ada di tabel `lay_themes`.
3. **`LicenseCheckCommand.php`**:
   - Menampilkan baris `Theme Quota` (Max Premium & Catalog count) pada tabel ringkasan lisensi console.
4. **`ThemePackRegistryTest.php`**:
   - Menambahkan skenario test:
     - `test_get_license_status_includes_top_level_theme_quota`
     - `test_jacp_activate_persists_custom_themes_quota`
     - `test_jacp_payload_partial_missing_fields_falls_back_safely`
     - `test_jacp_heartbeat_sync_updates_theme_quota_and_remediates`
     - `test_deactivate_license_clears_themes_quota_setting`
   - Seluruh 30 test lolos (123 assertions).
5. **`CHANGELOG.md`**:
   - Mencatat penambahan fitur di `Modules/Core/System/CHANGELOG.md`.

## 3. Acceptance Criteria

- [x] `LicenseService` berhasil menyimpan dan menerapkan payload `themes` dari respons JA-CP.
- [x] Fallback tier berlaku aman jika payload `themes` absen atau parsial.
- [x] `deactivateLicense()` menghapus kuota dinamis dan mengembalikan ke baseline Community.
- [x] Heartbeat sync yang membatasi kuota memicu `ThemeDowngradeRemediator` ke `janari`.
- [x] `license:check` menampilkan kuota tema aktif.
- [x] Seluruh pengujian lolos dan `npm run agent:verify` lulus 100% (Pint, PHPStan L9 600/600, ESLint 0 warnings, vue-tsc, vitest, e2e list, docs links, SemVer).

## 4. Log

| Waktu | Catatan |
| :--- | :--- |
| 2026-09-19 01:20 | Task diinisiasi dan rencana implementasi disetujui. |
| 2026-09-19 01:25 | Implementasi `LicenseService`, `ThemeDowngradeRemediator`, dan `LicenseCheckCommand`. |
| 2026-09-19 01:28 | 30 tests di `ThemePackRegistryTest` passing, Pint & PHPStan L9 lolos bersih. |
| 2026-09-19 01:31 | `npm run agent:verify` seluruh suite berhasil 100%. |

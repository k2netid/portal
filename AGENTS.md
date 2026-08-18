# Panduan Agen — Jejakawan CMS (`ja-cms`)

**Lingkup:** Repositori Jejakawan CMS murni (`/home/jejakawan/dev/ja-cms`).

## 📋 Aturan Utama
1. Struktur backend mengikuti **Modular Monolith** di `backend/Modules/` (`Core`, `Content`, `Intelligence`).
2. Struktur frontend mengikuti **Engine Architecture** di `frontend/src/` (`engine/`, `modules/`, `shared/`).
3. Selalu jalankan `npm run agent:verify` sebelum menyelesaikan perubahan untuk memastikan seluruh test suite backend dan frontend lulus.

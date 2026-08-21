# Panduan Agen — Jejakawan Core Engine (`ja-core_engine`)

**Lingkup:** Repositori Jejakawan Core Engine (`/home/jejakawan/dev/ja-core_engine`).

## 📋 Aturan Utama
1. Struktur backend mengikuti **Modular Monolith** di `backend/Modules/Core` (`System`, `Security`, `Infra`).
2. Struktur frontend mengikuti **Engine Architecture** di `frontend/src/` (`engine/`, `modules/Core/`, `shared/`).
3. Selalu jalankan `npm run agent:verify` sebelum menyelesaikan perubahan untuk memastikan seluruh test suite backend dan frontend lulus.

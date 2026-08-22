# Panduan Agen — Jejakawan Core Engine (`ja-core_engine`)

**Lingkup:** Repositori Jejakawan Core Engine — master kernel untuk aplikasi downstream.

## Baca dulu

1. [docs/AGENT_START_HERE.md](docs/AGENT_START_HERE.md)
2. [docs/architectural-status.md](docs/architectural-status.md)
3. [docs/README.md](docs/README.md)

## Aturan utama

1. Backend: **Modular Monolith** di `backend/Modules/Core` (`System`, `Security`, `Infra`).
2. Frontend: **Engine Architecture** di `frontend/src/` (`engine/`, `modules/Core/`, `shared/`).
3. Jangan sebut produk ini `ja-cms`. JA-CP = hub lisensi eksternal, bukan identitas repo ini.
4. Jalankan `npm run agent:verify` sebelum menyelesaikan perubahan.

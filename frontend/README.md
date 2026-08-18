# Jejakawan — Frontend (`ja-control-plane`)

Vue 3 + Vite SPA untuk **jejakawan.com**: situs marketing publik, konsol operator, portal member, Platform.

## Modul FE

| Tier | Modul |
| :--- | :--- |
| Core | System, Infra |
| Content | Publishing, Media, Studio (Layout, Forms, Library) |
| Intelligence | AI, Search, Newsletter |
| Operational | Platform (+ rute `/member/*`) |

**ja-workspace** (nanti): dashboard + CMS multi-tenant pelanggan — bukan scope repo ini sekarang.

## Shell

Dua HTML: `console.html` → `main-console.ts` (`/dash/*`, auth); `index.html` → `main-public.ts` (portal Janari).

## Development

```bash
npm install && npm run dev
npm run deploy:assets:full
```

---

[Root README](../README.md)

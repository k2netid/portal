# Sareupna — High-Performance Cloud & Developer Platform Theme

Tema resmi flagship untuk **jejakawan.com** (PT Jejak Awan Digital).

## Filosofi Basa Sunda

*Sareupna* adalah momen transisi langit senja ketika cakrawala mempertemukan kehangatan cahaya horizon dengan keagungan kosmik malam berbintang. Di dalam ekosistem Jejakawan, *Sareupna* merepresentasikan fondasi teknologi cloud, arsitektur microservice tangguh, dan platform pengembang modern dengan kecepatan, presisi, dan estetika terdepan.

## Karakteristik & Fitur Utama

1. **Dark-First Cyber-Obsidian Canvas**:
   - Palet warna deep obsidian (`#07090e` / `#0b0f19`) berpadu dengan aksen twilight violet dan electric cyan glow.
   - Pilihan visual modern: Clean, Obsidian Dark, Cyber Glow, dan Frosted Glass.

2. **Directional Auto-Hide Floating Dock Navigation**:
   - Header melayang frosted glass dengan GPU acceleration (`translateY(-100%)` pada scroll down dan `translateY(0)` pada scroll up).
   - Elevated dock saat melewati scroll 20px dengan `backdrop-filter: blur(24px) saturate(180%)`.

3. **Interactive Developer CLI Hero Badge**:
   - Badge terminal dengan prompt `$`, status indicator, monospace formatting, blinking cursor, dan fitur klik-untuk-salin (*click-to-copy*).

4. **12-Column Responsive Bento Grid**:
   - Kartu arsitektur dan kapabilitas cloud dengan border dinamis dan radial hover glow.

5. **Terminal Simulator / Code Showcase**:
   - Tab interaktif untuk simulasi CLI, API, SDK, dan container deployment.

6. **Floating SectionNavDots**:
   - Navigasi titik vertikal melayang di sisi layar untuk eksplorasi seksi dengan smooth scroll dan active state detection.

## Struktur Direktori

```
sareupna/
├── assets/styles/sareupna.css   # Token tema, font, glassmorphism, dan keyframe animasi
├── components/
│   ├── layout/                 # Header, Footer, Floating Social Dock
│   ├── sections/               # Hero, BentoGrid, TerminalShowcase, Products, CTA
│   └── shared/                 # SectionNavDots, SareupnaSplitText
├── locales/                    # en.json, id.json, su.json
├── pages/                      # Home, Solusi, Pricing, Blog, Post, Page, Contact, Search
├── sample-data/bundle.json     # Paket data sampel siap pasang
├── ui/                         # Primitif UI independen
├── routes.ts                   # Registrasi rute tema
├── theme.bundle-entry.ts       # Bundler entrypoint
└── theme.json                  # Manifest & Customizer Schema
```

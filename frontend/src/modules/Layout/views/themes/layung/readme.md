# Layung Theme Package

**Layung** (bahasa Sunda: *cahaya layung / cahaya beureum konéng di langit wanci pasosoré nalika panonpoé surup*) adalah tema resmi untuk perusahaan **Internet Service Provider (ISP)**, **Fiber Optic Backbone**, dan **Managed Service Provider (MSP)** pada platform Jejakawan Core Engine — saudara filosofis **Janari** (*fajar*) dan **Sarangenge** (*matahari siang/sekolah*).

---

## 🌅 Filosofi & Arsitektur Tema

| Dimensi | Janari | Sarangenge | Layung |
|---|---|---|---|
| **Makna Sunda** | Wanci Janari (Fajar / Subuh) | Kembang Sarangenge (Bunga Matahari / Pagi) | Cahaya Layung (Senja / Pasosoré) |
| **Fokus Industri** | CMS Korporat & Startup Multi-tujuan | Kampus, Sekolah & Akademik | ISP, Fiber Optic, Datacenter & Managed MSP |
| **Palet Warna** | Emerald & Slate | Sunflower Gold & Royal Navy | Photon Orange, Crimson Dusk & Cyber Slate |
| **Fitur Kunci** | Visual Builder Parity | PPDB, Rombel, Kurikulum, Beasiswa | NOC Ticker, Bandwidth Calculator, SLA 99.999%, BGP Peering |

---

## 📁 Struktur Berkas

```
layung/
├── theme.json                                          # Manifest tema & customizer schema settings
├── theme.bundle-entry.ts                               # Dynamic loader entry
├── tsconfig.json                                       # Isolated TS configuration
├── readme.md                                           # Dokumentasi arsitektur & panduan integrasi
├── assets/
│   └── styles/
│       └── layung.css                                  # Cyber twilight tokens, fiber glow, network styling
├── composables/
│   └── useLayungIdentity.ts                            # ISP identity, NOC hotline, bandwidth helper
├── locales/
│   ├── id.json                                         # Indonesian strings (100% symmetric)
│   ├── en.json                                         # English strings (100% symmetric)
│   └── su.json                                         # Sundanese strings (100% symmetric)
├── customizer/
│   ├── index.ts                                        # Customizer extension export
│   ├── schema.settings.json                            # Customizer settings
│   ├── bindings.registry.json                          # Host bindings registry
│   ├── sidebar.navigation.json                         # Customizer sidebar navigation
│   ├── sidebar.pages.json                              # Customizer sidebar page links
│   ├── preview.targets.json                            # Interactive preview targets
│   ├── filterLayungCustomizerSettings.ts               # Customizer filter rules
│   └── onLayungSettingChange.ts                        # Real-time setting handler
├── ui/                                                 # Self-contained UI Kit
│   ├── Button.vue, Card.vue, Badge.vue, Input.vue, Textarea.vue,
│   ├── Label.vue, Select.vue, Alert.vue, Checkbox.vue, ThemeToggle.vue
│   └── index.ts
├── components/
│   ├── layout/                                         # Header (with NOC Ticker & Bridge Menu), Footer
│   ├── sections/                                       # Hero, Bento, Calculator, Packages, Topology, SLA, SOC, Testi, FAQ, CTA
│   ├── blog/                                           # PostCard.vue
│   └── shared/                                         # Breadcrumb, PageDisabled, LayungSplitText
└── pages/                                              # Route Views (ThemePageResolver)
    ├── Home.vue, About.vue, Solusi.vue, Services.vue, Pricing.vue,
    ├── Achievement.vue, CareerCenter.vue, Tim.vue, Blog.vue, Post.vue,
    └── Contact.vue, Search.vue, Page.vue
```

---

## ⚡ Fitur Utama ISP & MSP 2026

1. **NOC Status & Live Latency Ticker**: Bar pantau latensi inti, kapasitas backbone 100G, nomor BGP ASN, dan hotline darurat 24/7.
2. **Interactive Bandwidth Calculator**: Simulator interaktif kebutuhan kapasitas Mbps/Gbps berdasarkan jumlah perangkat dan tipe beban kerja bisnis.
3. **Dedicated Internet Access (DIA 1:1)**: Penjelasan arsitektur rasio simetris murni upload/download dan alokasi IP Publik Statis /29.
4. **24/7 Managed Cyber Security SOC**: Mitigasi serangan DDoS multi-terabit, Next-Gen Firewall (NGFW), dan SIEM threat monitoring.
5. **Jaminan SLA 99.999% & MTTR 15 Menit**: Komitmen hukum ketersediaan jaringan dengan sistem potongan tagihan otomatis.
6. **Topologi Jaringan & Peering Domestik/Global**: Interkoneksi IIX, OpenIXP di Cyber 1 Tower, serta gateway internasional Equinix SG1 Singapore.

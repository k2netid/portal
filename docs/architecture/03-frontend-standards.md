# 03. Frontend Architecture & Standards — Jejakawan Core Engine

Panduan arsitektur dan standar pengembangan frontend Vue 3 + TypeScript di **Jejakawan Core Engine (`ja-core_engine`)**.

---

## 🎨 1. Tech Stack Frontend

- **Framework**: Vue 3.5 (Composition API dengan `<script setup lang="ts">`).
- **Build Tool**: Vite 8 + ESBuild minifier.
- **State Management**: Pinia stores terenkapsulasi.
- **Styling**: Tailwind CSS v4 + Radix UI primitives (`radix-vue`) + Lucide Icons (`lucide-vue-next`).
- **Routing**: Vue Router 4 dengan route lazy-loading dan dynamic dashboard resolver.
- **Internationalization**: Vue I18n v9+ dengan translasi 3 bahasa (`id`, `en`, `su`).

---

## 📁 2. Struktur Folder Frontend

```
frontend/src/
├── main.ts                       # Single Unified SPA Entrypoint
├── engine/                       # Kernel Engine Core
│   ├── api/                      # Axios client, CSRF handling, auth interceptors
│   ├── i18n/                     # Konfigurasi i18n & dynamic loaders
│   ├── router/                   # Router configuration & route guards
│   ├── stores/                   # Console context & session stores
│   └── types/                    # Global TypeScript interfaces
├── modules/                      # Domain Modules
│   └── Core/
│       ├── System/               # IAM, RBAC, Settings, Journals, Extensions
│       ├── Infra/                # Data Studio, Tasks, Backups, Redis, Webhooks
│       └── Security/             # IP firewall, 2FA, Passkeys, ABAC, Logs
├── shared/                       # Shared UI & Utilities
│   ├── components/               # Atomic UI components (Button, Dialog, Card, Input)
│   ├── composables/              # Reusable Vue composables
│   ├── stores/                   # Navigation & Dashboard shared stores
│   └── utils/                    # Helper functions, icon registry, formatters
└── styles/                       # Tailwind CSS v4 & theme style definitions
```

---

## 🖥️ 3. Single Unified Console SPA Architecture

Pada `ja-core_engine`, frontend menggunakan arsitektur **Single Unified Console SPA**:

1. **Satu Entrypoint (`index.html` ➔ `src/main.ts`)**:
   - Tidak ada dual-shell overhead.
   - Seluruh inisialisasi kernel, router, i18n, dan registry modul dijalankan melalui `main.ts`.
2. **Instant Auth & Landing Flow**:
   - **Root URL `/`**:
     - Pengguna yang belum login diarahkan secara langsung ke `/login` (tanpa 404/delay).
     - Pengguna yang sudah terautentikasi diarahkan langsung ke `/:dashboard_slug/dashboard`.
   - **Direct Auth Routes**: `/login`, `/register`, `/forgot-password`, `/reset-password` merupakan rute first-class.
3. **Console Layout Shell (`ConsoleLayout.vue`)**:
   - Sidebar navigasi dinamis berbasis role/permission (`operations` & `settings` accordion).
   - Global search (`Ctrl+K`), quick actions, live notification drawer, and profile switcher.

---

## 📏 4. Standar Kode Komponen Vue

Setiap komponen Vue wajib mengikuti konvensi:

```vue
<template>
  <div class="space-y-4">
    <!-- UI Elements -->
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
// Component Logic with explicit TypeScript typings
</script>
```

- **No `any` types**: Gunakan interface eksplisit dari `@/engine/types/*` atau definisikan tipe lokal.
- **i18n Integration**: Semua label teks yang tampak oleh user wajib menggunakan `$t()` atau `t()`.
- **Lucide Icons**: Seluruh ikon diimpor secara eksplisit dari `lucide-vue-next` atau melalui utilitas `getIcon()`.

# 03. Frontend Architecture & Standards — Jejakawan CMS

Panduan arsitektur dan standar pengembangan frontend Vue 3 + TypeScript di **Jejakawan CMS (`ja-cms`)**.

---

## 🎨 1. Tech Stack Frontend

- **Framework**: Vue 3 (Composition API dengan `<script setup lang="ts">`).
- **Build Tool**: Vite 8 + ESBuild.
- **State Management**: Pinia stores.
- **Styling**: Tailwind CSS + Radix Vue (Primitives) + Lucide Icons (`lucide-vue-next`).
- **Routing**: Vue Router 4 dengan dynamic chunk lazy loading.
- **Internationalization**: Vue I18n v9+ dengan loader modular per-tier.

---

## 📁 2. Struktur Folder Frontend

```
frontend/src/
├── engine/                       # Core engine CMS
│   ├── api/                      # Axios HTTP client, interceptors, paths
│   ├── i18n/                     # Konfigurasi i18n & dynamic loaders
│   ├── router/                   # Router configuration & guards
│   └── state/                    # Global app state & session
├── modules/                      # Domain Modules
│   ├── Core/
│   │   ├── System/               # IAM, Settings, Audit Logs
│   │   ├── Infra/                # Tasks, Backups, Redirects, CCK
│   │   └── Security/             # IP firewall, 2FA, Passkeys, ABAC
│   ├── Content/
│   │   ├── Publishing/           # Posts, Pages, Categories
│   │   ├── Layout/               # JA-Builder, Themes (Janari), Menus
│   │   ├── Forms/                # Form builder & submissions
│   │   ├── Media/                # Media manager & picker
│   │   └── Library/              # Tags & custom fields
│   └── Intelligence/
│       ├── Ai/                   # AI assistants & stats
│       ├── Search/               # Search management & health
│       ├── Analytics/            # Analytics dashboard
│       └── Newsletter/           # Newsletter manager
├── shared/                       # Shared UI & Utilities
│   ├── components/               # Atomic UI components (Button, Dialog, etc.)
│   ├── composables/              # Reusable Vue composables
│   ├── stores/                   # Cross-module shared Pinia stores
│   └── utils/                    # Helper functions & formatters
└── types/                        # Global TypeScript declarations
```

---

## 🖥️ 3. Dual Entry Shell Architecture

Frontend CMS memiliki 2 shell HTML independen untuk optimasi performa dan isolasi fungsionalitas:

1. **Console Shell (`console.html` ➔ `main-console.ts`)**:
   - Area administratif `/dash/*`.
   - Menggunakan `ConsoleLayout.vue` dengan sidebar navigasi dinamis berbasis permission, dark/light theme switcher, breadcrumbs, dan session expiration timeout modal.
2. **Public Shell (`index.html` ➔ `main-public.ts`)**:
   - Area situs publik dan marketing.
   - Merender tema aktif secara dinamis (misal: **Janari Theme**) via `ThemePageResolver.vue` dan `FrontendLayout.vue`.

---

## 🧩 4. Standar Komponen & TypeScript

1. **Composition API & `<script setup lang="ts">`**:
   - Selalu gunakan syntax `<script setup lang="ts">`.
   - Jangan gunakan Options API (`export default { data(), methods }`).
2. **Strict Typing**:
   - Seluruh prop, emit, dan API response harus memiliki interface TypeScript yang jelas.
   - Hindari penggunaan type `any` implisit.
   - Contoh:
     ```vue
     <script setup lang="ts">
     interface Props {
       title: string
       status?: 'draft' | 'published'
       count: number
     }

     const props = withDefaults(defineProps<Props>(), {
       status: 'draft',
     })

     const emit = defineEmits<{
       (e: 'save', payload: { id: number; title: string }): void
       (e: 'cancel'): void
     }>()
     </script>
     ```
3. **Komponen UI Primitives**:
   - Gunakan komponen dasar yang tersedia di `@/shared/components/ui/` (`Button`, `Input`, `DialogContent`, `Select`, `DropdownMenu`, dll.) untuk menjaga konsistensi visual dan aksesibilitas (A11y).
4. **Z-Index Governance**:
   - Komponen modal/overlay global wajib menggunakan kelas layer tinggi (`z-[100050]` untuk overlay, `z-[100060]` untuk dialog content) agar tidak tertimpa kanvas visual builder fullscreen.

# 04. Visual Builder System (JA-Builder) — Jejakawan CMS

Panduan arsitektur dan sistem kerja **JA-Builder** (Visual Drag-and-Drop Page Builder) di **Jejakawan CMS (`ja-cms`)**.

---

## 🎨 1. Arsitektur JA-Builder

JA-Builder berlokasi di `frontend/src/modules/Content/Layout/components/builder/` dan `views/builder/SiteEditor.vue`.

Sistem builder dibangun di atas struktur modular:

```
builder/
├── canvas/
│   ├── Canvas.vue                # Area utama kanvas visual builder
│   ├── CanvasBlock.vue           # Wrapper node elemen kanvas
│   ├── CanvasOverlay.vue         # Bounding box seleksi & resize handle
│   └── CanvasDropZone.vue        # Dropzone drag-and-drop
├── layout/
│   ├── BuilderHeader.vue         # Toolbar atas (Device switcher, Undo/Redo, Publish)
│   ├── BuilderSidebar.vue        # Sidebar kiri (Layer tree, Blocks catalog, History)
│   └── panels/                   # Sidebar kanan (Inspector properti)
│       ├── StylePanel.vue        # Properti CSS (Spacing, Typography, Background, Shadow)
│       ├── PageSettingsPanel.vue # Pengaturan meta halaman & template
│       ├── GlobalVariables.vue   # Popover token warna, font, link, dan gambar
│       └── ContextMenu.vue       # Klik kanan menu kanvas (Duplicate, Copy, Delete)
├── modals/
│   ├── BlockInsertModal.vue      # Modal pemilihan template blok
│   └── MediaPickerModal.vue      # Modal pemilihan aset media
└── store/
    └── builderState.ts           # State management aktif dokumen builder
```

---

## 🌲 2. Model Data Dokumen (Document Tree)

Dokumen halaman pada builder direpresentasikan dalam pohon JSON hierarkis:

```typescript
interface BuilderNode {
  id: string
  type: 'section' | 'container' | 'grid' | 'text' | 'heading' | 'image' | 'button' | 'custom'
  name: string
  styles: Record<string, any>        // CSS property definitions
  attributes: Record<string, any>    // HTML attributes (href, src, alt, etc.)
  content?: string                   // Inline text content
  children?: BuilderNode[]           // Nested child elements
  hidden?: boolean
  locked?: boolean
}
```

---

## 🌐 3. Arsitektur Lokalisasi Terisolasi (Isolated i18n)

Untuk mencegah kebocoran (*translation leak*) atau pencampuran string dengan modul lain, seluruh translasi builder dikarantina dalam direktori khusus:

- **Path**: `frontend/src/modules/Content/Layout/locales/builder/`
- **File**: `en.json`, `id.json`, `su.json`, `index.ts`
- **Root Key**: `builder.*`

### Hirarki Kunci Builder:
- `builder.header.*`: Tindakan toolbar atas (Simpan, Pratinjau, Ganti Device).
- `builder.sidebar.*`: Tab navigasi sidebar (Layers, Add, Settings, History).
- `builder.panels.*`:
  - `pageSettings.*`: Slug, Title, Status, Header/Footer toggle.
  - `style.*`: Typography, Backgrounds, Spacing (Margin/Padding), Border, Shadows.
  - `globalVariables.*`: Variabel warna global, font, url link, teks, dan gambar.
- `builder.contextMenu.*`: Menu klik kanan kanvas (`builder.contextMenu.duplicateElement`, dll.).
- `builder.modals.*`: Konfirmasi discard, reset template, color picker.

---

## ⚡ 4. Fitur Interaksi Kunci

1. **Responsive Viewport Switcher**:
   - Meniru viewport `Desktop` (100%), `Tablet` (768px), dan `Mobile` (375px) secara real-time dengan live media query styling.
2. **Infinite Undo / Redo History**:
   - Snapshot immutable state disimpan setiap kali ada mutasi tree elemen untuk memungkinkan undo/redo tanpa latensi.
3. **Global Variables & CSS Custom Properties**:
   - Variabel warna global dan typography diinject langsung sebagai CSS custom properties (`var(--ja-color-primary)`) pada elemen yang menggunakan token tersebut.

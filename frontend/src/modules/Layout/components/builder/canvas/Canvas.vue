<template>
  <div 
    class="canvas flex flex-col min-h-full"
    :class="[
      { 'canvas--wireframe': wireframeMode },
      { 'canvas--grid': gridViewMode },
      `theme-${activeTheme}`,
      `device-${device}`
    ]"
    @click.self="clearSelection"
    @contextmenu.stop.prevent="handleCanvasContextMenu"
  >
    <CanvasGridView v-if="gridViewMode" />

    <template v-else>
      <!-- Header Preview (site mode only — page mode is content-only) -->
      <div v-if="showThemeChrome" class="canvas-header-preview w-full flex-none relative z-10">
        <ThemePageResolver page="components/Header" />
      </div>

      <!-- Main Content Area -->
      <main class="main-content flex-1 w-full flex flex-col">
        <!-- Public block preview (Eye) — must win over empty/theme branches -->
        <div
          v-if="previewMode && blocks.length > 0"
          class="canvas-public-preview flex-1 w-full"
          data-builder-preview="public"
        >
          <CanvasPublicPreview :blocks="blocks" />
        </div>

        <!-- Theme page live preview (view-only until Edit with Builder binds a CMS doc) -->
        <div
          v-else-if="blocks.length === 0 && activeThemePage && !themePageEditable"
          class="canvas-theme-page flex-1 w-full relative"
          data-builder-theme-page="true"
        >
          <!-- Sticky top of canvas scroll — long theme pages used to bury this CTA -->
          <div class="canvas-theme-page__bar sticky top-3 z-30 mx-auto mb-3 w-[min(92%,40rem)] rounded-xl border border-slate-200 dark:border-slate-700 bg-white/95 dark:bg-slate-900/95 backdrop-blur shadow-lg px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <p class="text-xs text-slate-600 dark:text-slate-300">
              {{ t('builder.canvas.themePageHint', 'Live theme page (view only). Enable editing to customize with the visual builder.') }}
            </p>
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium flex items-center gap-1.5 disabled:opacity-60"
                :disabled="themePageBusy"
                @click="startThemePageEdit"
              >
                {{ themePageBusy
                  ? t('builder.common.saving', 'Working…')
                  : t('builder.canvas.editThemePage', 'Edit with Builder') }}
              </button>
            </div>
          </div>
          <ThemePageResolver :page="activeThemePage" />
        </div>

        <!-- Empty builder workspace (blank CMS or theme bind with no blocks yet) -->
        <div v-else-if="blocks.length === 0" class="canvas-empty flex-1 flex flex-col items-center justify-center p-8 text-center my-6">
          <div class="canvas-empty__content max-w-lg mx-auto bg-white/90 dark:bg-slate-900/90 backdrop-blur-md p-8 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl">
            <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
              <Sparkles :size="28" />
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2">
              {{ themePageEditable
                ? t('builder.canvas.themeOverrideTitle', 'Bangun override halaman tema')
                : (contentTitle ? `Desain Halaman ${contentTitle}` : 'Mulai Mendesain Halaman') }}
            </h3>
            <p class="canvas-empty__text text-slate-500 dark:text-slate-400 text-sm mb-6">
              {{ themePageEditable
                ? t('builder.canvas.themeOverrideHint', 'Elemen tema (Hero, dll.) bukan blok builder. Tambah Section → Row → Column, atau pakai template — itu yang bisa diedit & di-publish.')
                : 'Pilih template siap pakai atau tambahkan seksi baru dari awal.' }}
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
              <button class="px-4 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium flex items-center gap-2 transition-all shadow-md shadow-indigo-500/20 cursor-pointer" @click="addSection">
                <Plus :size="16" />
                {{ $t('builder.actions.addSection', 'Add New Section') }}
              </button>
              <button class="px-4 py-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium flex items-center gap-2 border border-slate-200 dark:border-slate-700 transition-all cursor-pointer" @click="openTemplateModal">
                <Sparkles :size="16" />
                Template Library
              </button>
            </div>
            <!-- Quick Starter Presets -->
            <div class="pt-4 border-t border-slate-200 dark:border-slate-800/80">
              <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">1-Click Starter Presets</div>
              <div class="flex flex-wrap items-center justify-center gap-2">
                <button class="px-3 py-1.5 rounded-md bg-slate-100 dark:bg-slate-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-xs font-medium text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60 hover:border-indigo-400 transition-all cursor-pointer" @click="loadPreset('saas')">🚀 SaaS / Produk</button>
                <button class="px-3 py-1.5 rounded-md bg-slate-100 dark:bg-slate-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-xs font-medium text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60 hover:border-indigo-400 transition-all cursor-pointer" @click="loadPreset('company')">🏢 Profil Bisnis</button>
                <button class="px-3 py-1.5 rounded-md bg-slate-100 dark:bg-slate-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-xs font-medium text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60 hover:border-indigo-400 transition-all cursor-pointer" @click="loadPreset('pricing')">💰 Paket Harga</button>
                <button class="px-3 py-1.5 rounded-md bg-slate-100 dark:bg-slate-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-xs font-medium text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60 hover:border-indigo-400 transition-all cursor-pointer" @click="loadPreset('contact')">📞 Kontak</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modules -->
        <draggable
          v-else
          v-model="blocks"
          item-key="id"
          group="section"
          class="canvas-blocks flex-1"
          ghost-class="ja-builder-ghost"
          style="padding-bottom: 100px;"
        >
          <template #item="{ element: block, index }">
            <ModuleWrapper
              :module="block"
              :index="index"
            />
          </template>
        </draggable>
      </main>

      <!-- Footer Preview (site mode only) -->
      <div v-if="showThemeChrome" class="mt-auto relative z-10">
        <ThemePageResolver page="components/Footer" />
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, inject, watch, onMounted, ref } from 'vue'
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js'
import Sparkles from 'lucide-vue-next/dist/esm/icons/sparkles.js'
import { saasLandingPage, homePage, aboutPage, contactPage } from '@/modules/Layout/components/builder/templates/PageTemplates';
import { pricingSection, faqSection, heroGradient, ctaDark } from '@/modules/Layout/components/builder/templates/SectionTemplates';
import { useI18n } from 'vue-i18n'
import draggable from 'vuedraggable'
import ModuleWrapper from './ModuleWrapper.vue'
import CanvasPublicPreview from './CanvasPublicPreview.vue'
import CanvasGridView from './CanvasGridView.vue'
import ThemePageResolver from '@/modules/Layout/components/themes/ThemePageResolver.vue'
import type { BuilderInstance, BlockInstance } from '@/modules/Layout/types/builder'
import type { ThemeData } from '@/modules/Layout/types/theme'

// Inject builder
const builder = inject<BuilderInstance>('builder')
const openContextMenu = inject<(moduleId: string, event: MouseEvent, title?: string, type?: string, mode?: string) => void>('openContextMenu')
const { t } = useI18n()

// Computed
const blocks = computed<BlockInstance[]>({
  get: () => builder?.blocks.value || [],
  set: (val) => { if (builder) builder.blocks.value = val }
})
const contentTitle = computed(() => builder?.content?.value?.title || '')
const activeThemePage = computed(() => builder?.activeThemePage?.value || null)
const themePageEditable = computed(() => !!builder?.content?.value?.id && !!activeThemePage.value)
const themePageBusy = ref(false)
const wireframeMode = computed(() => builder?.wireframeMode.value || false)
const previewMode = computed(() => builder?.previewMode.value || false)
const gridViewMode = computed(() => builder?.gridViewMode.value || false)
const activeTheme = computed(() => builder?.activeTheme.value || 'janari')
const device = computed(() => builder?.device.value || 'desktop')
const showThemeChrome = computed(() => (builder?.mode?.value || 'site') === 'site')

const startThemePageEdit = async () => {
  if (themePageBusy.value) return
  if (!builder?.beginThemePageEdit) {
    console.error('beginThemePageEdit missing — hard-refresh the console (Ctrl+Shift+R) and reopen Site Editor')
    return
  }
  themePageBusy.value = true
  try {
    await builder.beginThemePageEdit({
      slug: builder.content?.value?.slug || '',
      themePage: activeThemePage.value || '',
      title: builder.content?.value?.title || '',
    })
    // Land in builder workspace (empty-state / insert modal), not live theme Vue.
    if ((builder.blocks.value?.length ?? 0) === 0 && builder.openInsertSectionModal) {
      builder.openInsertSectionModal(-1)
    }
  } catch (error) {
    console.error('Failed to enable theme page editing:', error)
    window.alert(
      error instanceof Error
        ? error.message
        : 'Gagal aktifkan editing theme page. Cek permission Publishing / slug conflict.',
    )
  } finally {
    themePageBusy.value = false
  }
}

// Theme Style Injection
const hexToHsl = (hex: string): string | null => {
  if (!hex || typeof hex !== 'string' || !hex.startsWith('#')) return null
  let r = 0, g = 0, b = 0
  if (hex.length === 4) {
    r = parseInt('0x' + hex[1] + hex[1])
    g = parseInt('0x' + hex[2] + hex[2])
    b = parseInt('0x' + hex[3] + hex[3])
  } else if (hex.length === 7) {
    r = parseInt('0x' + hex[1] + hex[2])
    g = parseInt('0x' + hex[3] + hex[4])
    b = parseInt('0x' + hex[5] + hex[6])
  }
  r /= 255; g /= 255; b /= 255
  const cmin = Math.min(r, g, b), cmax = Math.max(r, g, b), delta = cmax - cmin
  let h: number, s: number, l: number
  if (delta === 0) h = 0
  else if (cmax === r) h = ((g - b) / delta) % 6
  else if (cmax === g) h = (b - r) / delta + 2
  else h = (r - g) / delta + 4
  h = Math.round(h * 60)
  if (h < 0) h += 360
  l = (cmax + cmin) / 2
  s = delta === 0 ? 0 : delta / (1 - Math.abs(2 * l - 1))
  s = +(s * 100).toFixed(1); l = +(l * 100).toFixed(1)
  return `${h} ${s}% ${l}%`
}

const injectThemeStyles = () => {
    if (!builder?.themeData.value) return

    const variables: string[] = []
    const builderOverrides: string[] = []
    const settings = builder.themeSettings.value || {}
    const themeDataValue = builder.themeData.value as ThemeData
    const manifest = themeDataValue?.manifest || {}
    const themeSlug = builder.activeTheme.value || 'janari'

    if (manifest.settings_schema) {
        Object.keys(manifest.settings_schema).forEach(key => {
            const schema = manifest.settings_schema![key]
            if (!schema) return
            const value = settings[key] !== undefined ? settings[key] : schema.default
            
            if (value === undefined || value === null) return

            const cssKey = '--theme-' + key.replace(/_/g, '-')
            
            if (schema.type === 'color') {
                variables.push(`${cssKey}: ${value};`)
                const hslValue = hexToHsl(value as string)
                if (hslValue) {
                    variables.push(`${cssKey}-hsl: ${hslValue};`)
                    
                    if (key === 'color_primary' || key === 'primary_color') {
                        variables.push(`--primary: ${hslValue};`)
                        builderOverrides.push(`--builder-section: ${value};`)
                    }
                    if (key === 'color_background' || key === 'background_color') {
                        variables.push(`--background: ${hslValue};`)
                    }
                }
            } else if (schema.type === 'font' || schema.type === 'typography' || schema.type === 'select') {
                const fontValue = String(value).includes(' ') ? `"${value}"` : value
                variables.push(`${cssKey}: ${fontValue};`)
            } else {
                variables.push(`${cssKey}: ${value};`)
            }
        })
    }

    if (!variables.some(v => v.startsWith('--primary:'))) {
        variables.push('--primary: 221.2 83.2% 53.3%;')
    }

    const styleId = `builder-theme-styles`
    let styleTag = document.getElementById(styleId)
    if (!styleTag) {
        styleTag = document.createElement('style')
        styleTag.id = styleId
        document.head.appendChild(styleTag)
    }

    const cssContent = `
        .canvas.theme-${themeSlug} {
            ${variables.join('\n            ')}
            ${builderOverrides.join('\n            ')}
        }
        .canvas.theme-${themeSlug}::after {
            content: "Theme: ${themeSlug}";
            position: absolute;
            bottom: 10px;
            right: 10px;
            font-size: 10px;
            color: rgba(0,0,0,0.2);
            pointer-events: none;
            z-index: 1000;
        }
    `
    styleTag.textContent = cssContent

    if (themeDataValue?.assets && themeDataValue.assets.css) {
        themeDataValue.assets.css.forEach((cssFile: string, index: number) => {
            const linkId = `builder-theme-asset-${index}`
            if (!document.getElementById(linkId)) {
                const link = document.createElement('link')
                link.id = linkId
                link.rel = 'stylesheet'
                link.href = cssFile.startsWith('http') || cssFile.startsWith('/') ? cssFile : `/${cssFile}`
                document.head.appendChild(link)
            }
        })
    }
}

// Watchers
watch(() => builder?.themeData.value, (data) => {
    if (data) {
        injectThemeStyles()
    }
}, { immediate: true })

watch(() => builder?.themeSettings.value, injectThemeStyles, { deep: true })
watch(() => builder?.activeTheme.value, injectThemeStyles)

// Methods
const clearSelection = () => {
  builder?.clearSelection()
}

const addSection = async () => {
  if (activeThemePage.value && !builder?.content?.value?.id) {
    await startThemePageEdit()
  } else if (activeThemePage.value && builder?.ensureThemePageDocument) {
    await builder.ensureThemePageDocument()
  }
  // Prefer layout picker (section → row → column) over a bare empty section.
  if (builder?.openInsertSectionModal) {
    builder.openInsertSectionModal(-1)
    return
  }
  builder?.insertModule('section')
}

const openTemplateModal = async () => {
  if (activeThemePage.value && !builder?.content?.value?.id) {
    await startThemePageEdit()
  }
  if (typeof (builder as any)?.openPageTemplateModal === 'function') {
    (builder as any).openPageTemplateModal()
  } else {
    await loadPreset('saas')
  }
}

const loadPreset = async (type: string) => {
  if (!builder) return
  if (activeThemePage.value && !builder.content?.value?.id) {
    await startThemePageEdit()
  }
  if (type === 'saas') {
    builder.blocks.value = saasLandingPage() as any
  } else if (type === 'company' || type === 'about') {
    builder.blocks.value = aboutPage() as any
  } else if (type === 'pricing') {
    builder.blocks.value = [heroGradient(), pricingSection(), faqSection(), ctaDark()] as any
  } else if (type === 'contact') {
    builder.blocks.value = contactPage() as any
  } else {
    builder.blocks.value = homePage() as any
  }
  builder.takeSnapshot?.({ immediate: true })
}

const handleCanvasContextMenu = (e: MouseEvent) => {
    if (openContextMenu) {
        openContextMenu('canvas', e, t('builder.fields.contextMenu.canvasSettings', 'Canvas Settings'), 'Main', 'canvas')
    } else if (builder?.openContextMenu) {
        builder.openContextMenu('canvas', e, t('builder.fields.contextMenu.canvasSettings', 'Canvas Settings'), 'Main', 'canvas')
    }
}

onMounted(() => {
    if (builder?.themeData.value) {
        injectThemeStyles()
    }
})
</script>

<style scoped>
.canvas {
  min-height: 100%;
  padding: var(--spacing-lg, 24px);
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
  overflow-x: hidden;

  --builder-text-primary: #0f172a;
  --builder-text-secondary: #475569;
  --builder-text-muted: #64748b;
  --builder-border: #e2e8f0;

  background-color: var(--theme-color-background, #ffffff);
  color: var(--theme-color-text, var(--builder-text-primary));
  transition: background-color 0.3s ease, color 0.3s ease;
}

.canvas.device-tablet {
  padding: 16px;
}

.canvas.device-mobile {
  padding: 12px 8px;
}

.canvas-header-preview {
  position: relative;
  z-index: 10;
  margin-bottom: var(--spacing-lg, 24px);
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;
}

.canvas-header-preview :deep(header),
.canvas :deep(header),
.canvas :deep(header.fixed),
.canvas :deep(header[class*="fixed"]) {
  position: relative !important;
  top: auto !important;
  left: auto !important;
  width: 100% !important;
  max-width: 100% !important;
  box-sizing: border-box !important;
  overflow-x: hidden !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.canvas-footer-preview {
  position: relative;
  z-index: 10;
  margin-top: var(--spacing-xl, 32px);
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;
}

.canvas-footer-preview :deep(footer),
.canvas :deep(footer) {
  position: relative !important;
  width: 100% !important;
  max-width: 100% !important;
  box-sizing: border-box !important;
  overflow-x: hidden !important;
}

.canvas--wireframe {
  background: repeating-linear-gradient(
    45deg,
    transparent,
    transparent 10px,
    rgba(0,0,0,0.05) 10px,
    rgba(0,0,0,0.05) 20px
  );
}

.canvas--grid {
  background-color: #f8fafc;
}

.canvas-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  border: 2px dashed var(--builder-border);
  border-radius: var(--border-radius-md);
  background: rgba(0,0,0,0.02);
}

.canvas-empty__content {
  text-align: center;
}

.canvas-empty__text {
  color: var(--builder-text-muted);
  margin-bottom: var(--spacing-lg);
}

.canvas-empty__btn {
  display: inline-flex;
  align-items: center;
  gap: var(--spacing-xs);
  padding: var(--spacing-sm) var(--spacing-lg);
  background: var(--builder-accent);
  border: none;
  border-radius: var(--border-radius-sm);
  color: white;
  font-size: var(--font-size-sm);
  font-weight: 500;
  cursor: pointer;
  transition: background var(--transition-fast);
}

.canvas-empty__btn:hover {
  background: var(--builder-accent-hover);
}
</style>

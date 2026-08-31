<template>
  <div 
    ref="layoutRoot"
    class="frontend-layout min-h-screen flex flex-col bg-background text-foreground"
    :class="[rootClasses, activeThemeClass]"
    :style="[rootStyles, janariRootStyleVars]"
    v-bind="janariRootDataAttrs"
  >
    <!-- CASE 1: FULL WIDTH (Default) & HYBRID -->
    <!-- For Hybrid: Header/Footer are here (full), Main is constrained below -->
    <!-- For Full: Everything is here -->
    <template v-if="activeTheme && (layoutStyle === 'full' || layoutStyle === 'hybrid')">
      <div class="relative z-50 overflow-visible">
        <ThemePageResolver page="components/Header" />
      </div>
      <PluginSlot name="after_header" />
      
      <!-- Main Content -->
      <!-- Hybrid: Main is boxed | Full: Main is full -->
      <main
        class="main-content flex-1 w-full relative z-0"
        :class="{
          // Janari sticky = main nav + artist/breadcrumb bar (~7.5–9rem)
          'pt-36 md:pt-40': headerSticky && usesJanariCanvas,
          'pt-0': !usesJanariCanvas,
        }"
      >
        <div
          :class="{
            'container mx-auto': layoutStyle === 'hybrid',
            'px-6 md:px-12 lg:px-20': layoutStyle === 'hybrid', // Increased padding
            'w-full': layoutStyle === 'full'
          }"
          :style="hybridContentStyles"
        >
          <router-view v-slot="{ Component }">
            <div class="w-full h-full flex-1 flex flex-col page-enter route-shell">
              <component :is="Component" />
            </div>
          </router-view>
        </div>
      </main>

      <PluginSlot name="before_footer" />
      <div class="mt-auto relative z-10">
        <ThemePageResolver page="components/Footer" />
      </div>
    </template>


    <!-- CASE 2: BOXED, WIDE, FRAMED -->
    <!-- Everything wraps inside a container -->
    <div 
      v-else-if="activeTheme"
      class="layout-wrapper mx-auto flex flex-col min-h-screen bg-background shadow-xl overflow-visible"
      :class="wrapperClasses"
      :style="wrapperStyles"
    >
      <div class="relative z-50 overflow-visible">
        <ThemePageResolver page="components/Header" />
      </div>
      <PluginSlot name="after_header" />
      
      <main
        class="main-content flex-1 px-6 md:px-12 lg:px-16 py-8 relative z-0"
        :class="{
          'pt-36 md:pt-40': headerSticky && usesJanariCanvas,
          'pt-0': !usesJanariCanvas,
        }"
      >
        <!-- Added padding here too -->
        <router-view v-slot="{ Component }">
          <div class="w-full h-full flex-1 flex flex-col page-enter route-shell">
            <component :is="Component" />
          </div>
        </router-view>
      </main>

      <PluginSlot name="before_footer" />
      <div class="mt-auto">
        <ThemePageResolver page="components/Footer" />
      </div>
    </div>
    
    <!-- Initial loading guard: avoid double-mounting route component -->
    <div
      v-else-if="loading"
      class="flex-1 flex items-center justify-center bg-background"
      aria-live="polite"
      aria-busy="true"
    >
      <div class="flex items-center gap-3 text-muted-foreground">
        <span class="inline-block w-4 h-4 rounded-full border-2 border-current border-t-transparent animate-spin" />
        <span class="text-sm font-medium">Memuat tema...</span>
      </div>
    </div>

    <!-- FALLBACK: Degrade gracefully when theme API is unavailable -->
    <div 
      v-else
      class="flex-1 flex flex-col bg-background"
    >
      <div
        v-if="showThemeServiceNotice"
        class="px-4 py-2 text-xs text-center text-amber-700 dark:text-amber-300 bg-amber-500/10 border-b border-amber-500/30"
      >
        Theme service sedang tidak tersedia. Menampilkan mode fallback sementara.
      </div>
      <main class="main-content flex-1 w-full">
        <router-view v-slot="{ Component }">
          <div class="w-full h-full flex-1 flex flex-col page-enter route-shell">
            <component :is="Component" />
          </div>
        </router-view>
      </main>
    </div>

    <!-- Floating Back to Top Button (Bottom Right) -->
    <button
      v-if="showBackToTop"
      type="button"
      class="sarangenge-back-to-top fixed bottom-8 right-8 z-[9999] w-12 h-12 rounded-full shadow-2xl flex items-center justify-center transition-all duration-300 transform hover:scale-110 active:scale-95 focus:outline-none focus:ring-2 focus:ring-amber-500 bg-slate-900 text-amber-400 border border-slate-700 hover:bg-slate-800"
      style="position: fixed !important; bottom: 2rem !important; right: 2rem !important; left: auto !important; top: auto !important; z-index: 999999 !important;"
      aria-label="Kembali ke Atas"
      title="Kembali ke Atas"
      @click="scrollToTop"
    >
      <ArrowUp class="w-5 h-5 stroke-[2.5]" />
    </button>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, onUnmounted, watch } from 'vue'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import ThemePageResolver from '@/modules/Layout/components/themes/ThemePageResolver.vue'
import { useCustomizerPreviewProbe } from '@/modules/Layout/customizer/preview/useCustomizerPreviewProbe'
import '@/modules/Layout/customizer/preview/customizer-preview.css'
import { PluginSlot } from '@/shared/components'
import {
  ArrowUp,
} from 'lucide-vue-next';
import { JANARI_PRESETS } from '@/modules/Layout/config/janariPresets';
import { themeUsesJanariCanvas } from '@/modules/Layout/utils/themeManifest';
import { buildThemeViewResolveCandidates, findThemeViewKey } from '@/modules/Layout/utils/themeViewResolver'
import { useRoute } from 'vue-router'

const { activeTheme, getSetting, loading, error, loadActiveTheme } = useTheme()

const route = useRoute()
useCustomizerPreviewProbe()
const lastThemeRetryAt = ref(0)
const RETRY_INTERVAL_MS = 15000
const prefetchedThemePages = new Set<string>()
const viewModules = import.meta.glob('@/modules/Layout/views/themes/**/*.vue') as Record<string, () => Promise<unknown>>
const CORE_PUBLIC_PATH_TO_PAGE: Record<string, string> = {
  '/': 'Home',
  '/about': 'About',
  '/tim': 'Tim',
  '/solusi': 'Solusi',
  '/pricing': 'Pricing',
  '/blog': 'Blog',
  '/contact': 'Contact',
}

const activeThemeSlug = computed(
  () => (activeTheme.value as { slug?: string } | null)?.slug ?? '',
)

const usesJanariCanvas = computed(() => themeUsesJanariCanvas(activeTheme.value))

const activeThemeClass = computed(() => {
  const slug = activeThemeSlug.value
  if (!slug) return []
  const classes = [`theme-${slug}`]
  if (usesJanariCanvas.value) {
    classes.push('theme-janari')
  }
  // School theme tokens (Header/Footer sit outside page roots that also use this class).
  if (slug.toLowerCase() === 'sarangenge') {
    classes.push('sarangenge-theme')
  }
  // High-performance ISP theme tokens.
  if (slug.toLowerCase() === 'layung') {
    classes.push('layung-theme')
  }
  return classes
})

/** Janari canvas: neutral preset/intensity/texture from customizer (see themes/janari/assets/styles/janari.css) */
const janariRootDataAttrs = computed((): Record<string, string> => {
  if (!usesJanariCanvas.value) return {}
  const validPresets = new Set([
    'custom',
    'monochrome_clean',
    'oceanic_clean',
    'emerald_fresh',
    'royal_violet',
    'sunset_coral',
    'midnight_cyan',
    'forest_earth',
    'ruby_night',
    'aurora_mint',
    'slate_indigo',
    'arctic_blue',
    'sand_stone',
  ])
  const validMono = new Set(['clean', 'soft', 'matte', 'high_contrast'])
  const validIntensity = new Set(['soft', 'balanced', 'vibrant'])
  const validTexture = new Set(['clean', 'dots', 'grain'])

  const presetRaw = String(getSetting('color_preset', 'custom') ?? 'custom')
  const monoRaw = String(getSetting('monochrome_variant', 'clean') ?? 'clean')
  const intensityRaw = String(getSetting('color_intensity', 'balanced') ?? 'balanced')
  const textureRaw = String(getSetting('monochrome_texture', 'clean') ?? 'clean')
  const textureStrengthRaw = Number(getSetting('monochrome_texture_strength', 8) ?? 8)

  const normalizePreset = (value: string): string => {
    const normalized = value.trim().toLowerCase().replace(/[\s-]+/g, '_')
    const aliases: Record<string, string> = {
      monochrome: 'monochrome_clean',
      mono: 'monochrome_clean',
      oceanic: 'oceanic_clean',
      emerald: 'emerald_fresh',
      violet: 'royal_violet',
      coral: 'sunset_coral',
      cyan: 'midnight_cyan',
      forest: 'forest_earth',
      ruby: 'ruby_night',
      mint: 'aurora_mint',
      indigo: 'slate_indigo',
      arctic: 'arctic_blue',
      sand: 'sand_stone',
    }
    return aliases[normalized] ?? normalized
  }

  const styleRaw = String(getSetting('theme_style', 'clean') ?? 'clean')
  const navRaw = String(getSetting('nav_style', 'glass') ?? 'glass')
  const btnRadiusRaw = String(getSetting('button_radius', '8px') ?? '8px')
  const btnShadowRaw = String(getSetting('button_shadow', 'subtle') ?? 'subtle')

  // Background color settings
  const bgLightColor = String(getSetting('bg_light_color', 'white') ?? 'white')
  const bgLightVariant = String(getSetting('bg_light_variant', 'clean') ?? 'clean')
  const bgDarkColor = String(getSetting('bg_dark_color', 'black') ?? 'black')
  const bgDarkVariant = String(getSetting('bg_dark_variant', 'clean') ?? 'clean')

  const presetCandidate = normalizePreset(presetRaw)
  const preset = validPresets.has(presetCandidate) ? presetCandidate : 'custom'
  const mono = validMono.has(monoRaw) ? monoRaw : 'clean'
  const intensity = validIntensity.has(intensityRaw) ? intensityRaw : 'balanced'
  const texture = validTexture.has(textureRaw) ? textureRaw : 'clean'
  const textureStrength = String(Math.min(35, Math.max(0, Number.isFinite(textureStrengthRaw) ? textureStrengthRaw : 8)))
  const isMonochromePreset = preset === 'monochrome_clean'

  const attrs: Record<string, string> = {
    'data-janari-preset': preset,
    'data-janari-intensity': intensity,
    'data-janari-style': styleRaw,
    'data-janari-nav': navRaw,
    'data-janari-button-radius': btnRadiusRaw,
    'data-janari-button-shadow': btnShadowRaw,
    'data-janari-texture': texture,
    'data-janari-texture-strength': textureStrength,
    'data-janari-bg-light': bgLightColor,
    'data-janari-bg-light-v': bgLightVariant,
    'data-janari-bg-dark': bgDarkColor,
    'data-janari-bg-dark-v': bgDarkVariant,
  }

  if (isMonochromePreset) {
    attrs['data-janari-mono'] = mono
  }

  return attrs
})

const janariRootStyleVars = computed((): Record<string, string> => {
  if (!usesJanariCanvas.value) return {}

  const preset = String(janariRootDataAttrs.value['data-janari-preset'] || 'custom')

  const resolvedPresets: Record<string, { light: string; dark: string }> = {
    custom: {
      light: 'var(--theme-color-primary-hsl, var(--theme-primary-color-hsl, 0 0% 0%))',
      dark: 'var(--theme-color-primary-hsl, var(--theme-primary-color-hsl, 0 0% 100%))',
    }
  }

  // Hydrate from shared config
  Object.entries(JANARI_PRESETS).forEach(([key, val]) => {
    resolvedPresets[key] = { light: val.hslLight, dark: val.hslDark }
  })

  const resolved = resolvedPresets[preset] ?? resolvedPresets.custom ?? { light: '0 0% 0%', dark: '0 0% 100%' }
  return {
    '--janari-accent-hsl-inline': resolved.light,
    '--janari-accent-hsl-inline-dark': resolved.dark,
  }
})

// Layout settings
const layoutStyle = computed(() => getSetting('layout_style', 'full') as string)
const containerMaxWidth = computed(() => getSetting('container_max_width', 1400) as number)
const boxedBgColor = computed(() => getSetting('boxed_bg_color', '#f1f5f9') as string)
const boxedShadow = computed(() => getSetting('boxed_shadow', 'lg') as string)

// ROOT CLASSES (Outer most div)
const rootClasses = computed(() => {
  const style = layoutStyle.value
  return {
    'bg-page-background': style === 'boxed' || style === 'wide' || style === 'framed',
    'p-4 md:p-8': style === 'framed', // Visible padding for framed
  }
})

const rootStyles = computed(() => {
  const style = layoutStyle.value
  if (style === 'boxed' || style === 'wide' || style === 'framed') {
    return { backgroundColor: boxedBgColor.value }
  }
  return {}
})

// wrapper classes for Boxed/Wide/Framed
const wrapperClasses = computed(() => {
  const shadow = boxedShadow.value
  return {
    'rounded-xl': layoutStyle.value === 'framed',
    [`shadow-${shadow}`]: shadow !== 'none'
  }
})

const wrapperStyles = computed(() => {
  if (['boxed', 'wide', 'framed'].includes(layoutStyle.value)) {
    return { 
      maxWidth: `${containerMaxWidth.value}px`,
      width: '100%' 
    }
  }
  return {}
})

const hybridContentStyles = computed(() => {
  if (layoutStyle.value === 'hybrid') {
    return { maxWidth: `${containerMaxWidth.value}px` }
  }
  return {}
})

const enableBackToTop = computed(() => getSetting('back_to_top', true) as boolean)
const headerSticky = computed(() => getSetting('header_sticky', true) as boolean)
const showBackToTop = ref(false)
const layoutRoot = ref<HTMLElement | null>(null)
const showThemeServiceNotice = computed(() => !activeTheme.value && !loading.value && !!error.value)

const handleScroll = () => {
  if (window.scrollY > 300 && enableBackToTop.value) {
    showBackToTop.value = true
  } else {
    showBackToTop.value = false
  }
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const retryThemeLoadIfNeeded = async (options?: { force?: boolean }) => {
  if (loading.value) return
  const force = options?.force === true
  const now = Date.now()
  if (!force && now - lastThemeRetryAt.value < RETRY_INTERVAL_MS) return
  lastThemeRetryAt.value = now
  try {
    await loadActiveTheme('frontend', { force })
  } catch {
    // silent: fallback UI handles service degradation
  }
}

const resolveThemeViewKey = (pageName: string): string | undefined => {
  const themeSlugs = buildThemeViewResolveCandidates(activeTheme.value)
  return findThemeViewKey(viewModules, themeSlugs, pageName)
}

const prefetchThemePage = (pageName: string) => {
  if (!activeTheme.value || loading.value) return
  const key = resolveThemeViewKey(pageName)
  if (!key || prefetchedThemePages.has(key)) return
  const loader = viewModules[key]
  if (!loader) return
  prefetchedThemePages.add(key)
  void loader().catch(() => {
    prefetchedThemePages.delete(key)
  })
}

const schedulePublicPrefetch = () => {
  if (route.path === '/') return
  if (typeof navigator !== 'undefined') {
    const connection = (navigator as Navigator & { connection?: { saveData?: boolean; effectiveType?: string } }).connection
    if (connection?.saveData) return
    if (connection?.effectiveType && ['slow-2g', '2g', '3g'].includes(connection.effectiveType)) return
  }
  const path = route.path.toLowerCase()
  const targets = Object.entries(CORE_PUBLIC_PATH_TO_PAGE)
    .filter(([targetPath]) => targetPath !== path)
    .map(([, page]) => page)

  const run = () => {
    targets.forEach(prefetchThemePage)
  }

  if (typeof window !== 'undefined' && typeof window.requestIdleCallback === 'function') {
    window.requestIdleCallback(run, { timeout: 2000 })
  } else {
    setTimeout(run, 0)
  }
}

// React immediately to back_to_top setting changes
watch(enableBackToTop, (enabled) => {
    if (!enabled) showBackToTop.value = false;
    else handleScroll();
});

// Imperative CSS var injection — bypasses Vue :style race condition
watch(janariRootStyleVars, (vars) => {
  const el = layoutRoot.value
  if (!el || !Object.keys(vars).length) return
  Object.entries(vars).forEach(([key, val]) => {
    el.style.setProperty(key, val)
  })
}, { immediate: true, flush: 'post' })

const handleCustomizerSync = (event: MessageEvent) => {
  const allowed = new Set<string>([window.location.origin])
  const parentOrigin = (() => {
    try {
      const raw = new URLSearchParams(window.location.search).get('ja_parent_origin')
      return raw ? decodeURIComponent(raw) : null
    } catch {
      return null
    }
  })()
  if (parentOrigin) allowed.add(parentOrigin)
  if (!allowed.has(event.origin)) return

  if (event.data?.type === 'JA_CUSTOMIZER_THEME_BOOT' && event.data?.theme) {
    activeTheme.value = event.data.theme
    return
  }

  if (event.data?.type === 'JA_THEME_CUSTOMIZER_SYNC' && event.data?.theme) {
    const incoming = event.data.theme
    if (!activeTheme.value || activeTheme.value.slug !== incoming.slug) {
      activeTheme.value = { ...incoming }
      return
    }
    activeTheme.value = {
      ...activeTheme.value,
      ...incoming,
      settings: {
        ...(activeTheme.value.settings || {}),
        ...(incoming.settings || {}),
      },
    }
  }
}

onMounted(async () => {
  window.addEventListener('scroll', handleScroll)
  window.addEventListener('message', handleCustomizerSync)
  // Force reconcile with API — sessionStorage snapshot must not lock a stale Sarangenge shell.
  await retryThemeLoadIfNeeded({ force: true })
  schedulePublicPrefetch()

  // Apply janari vars immediately on mount
  const el = layoutRoot.value
  const vars = janariRootStyleVars.value
  if (el && Object.keys(vars).length) {
    Object.entries(vars).forEach(([key, val]) => {
      el.style.setProperty(key, val)
    })
  }
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('message', handleCustomizerSync)
})

watch(() => route.fullPath, () => {
  void retryThemeLoadIfNeeded()
  schedulePublicPrefetch()
})

watch(() => activeTheme.value?.slug, () => {
  prefetchedThemePages.clear()
  schedulePublicPrefetch()
})
</script>

<style scoped>
/* Page enter animation — pure CSS, no Vue transition lifecycle hooks */
@keyframes pageEnter {
  from {
    opacity: 0;
    transform: translateY(6px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.page-enter {
  animation: pageEnter 0.25s ease-out;
}

.route-shell {
  contain: layout paint style;
}

@media (prefers-reduced-motion: reduce), (max-width: 1024px) {
  .page-enter {
    animation: none;
  }
}

/* Shadow utilities re-implementation because tailwind classes might be purged if dynamic */
.shadow-sm { box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); }
.shadow-md { box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
.shadow-lg { box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1); }
.shadow-xl { box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1); }
</style>

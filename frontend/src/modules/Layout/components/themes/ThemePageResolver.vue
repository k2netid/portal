<template>
  <div class="theme-page-resolver-wrapper w-full h-full flex-1 flex flex-col overflow-visible">
    <component
      :is="resolvedComponent"
      v-if="resolvedComponent"
      v-bind="$attrs"
    />

    <div
      v-else-if="isLoadingTheme"
      class="min-h-[30vh] flex items-center justify-center p-8"
      aria-live="polite"
      aria-busy="true"
    >
      <div class="flex items-center gap-3 text-muted-foreground">
        <span class="inline-block w-4 h-4 rounded-full border-2 border-current border-t-transparent animate-spin" />
        <span class="text-sm font-medium">Memuat halaman...</span>
      </div>
    </div>

    <div
      v-else-if="isNotFound"
      class="min-h-[40vh] flex flex-col items-center justify-center p-12 text-center border-2 border-dashed border-border/50 m-4 rounded-[2rem] bg-muted/10"
    >
      <div class="w-16 h-16 rounded-full bg-destructive/10 flex items-center justify-center mb-6">
        <span class="text-3xl font-black text-destructive">!</span>
      </div>
      <h3 class="text-xl font-bold mb-2">
        Halaman Tidak Stabil
      </h3>
      <p class="text-muted-foreground max-w-md">
        Komponen tema gagal dimuat dengan sempurna. Silakan muat ulang halaman atau hubungi administrator.
      </p>
      <button
        type="button"
        class="mt-6 px-6 py-2 bg-primary text-primary-foreground rounded-full text-sm font-bold hover:scale-105 transition-transform"
        @click="resolveView"
      >
        Coba Lagi
      </button>
      <div class="mt-4 text-[10px] text-muted-foreground/30 font-mono text-left max-w-xs overflow-hidden mx-auto">
        <div>Theme: {{ activeTheme?.slug || 'NULL' }}</div>
        <div>Page: {{ page }}</div>
        <div>Total: {{ Object.keys(viewModules).length }} files</div>
        <div class="truncate">Match: {{ lastResolveDebug.matchingKey || '—' }}</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { shallowRef, watch, ref, computed, onBeforeUnmount, type Component } from 'vue'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import { BUNDLED_FRONTEND_THEME_SLUGS, buildThemeViewResolveCandidates, findThemeViewKey } from '@/modules/Layout/utils/themeViewResolver'
import { isUploadedThemeActive, loadDynamicThemeComponent } from '@/modules/Layout/utils/dynamicThemeLoader'
import { logger } from '@/shared/utils/logger'

const viewModules = import.meta.glob(
  '@/modules/Layout/views/themes/**/*.vue',
) as Record<string, () => Promise<{ default: Component } | Component>>

/** Cache resolved SFC components (not defineAsyncComponent — avoids stale resolveId stubs). */
const componentCache = new Map<string, Component>()

const props = defineProps<{
  page: string
}>()

const { activeTheme, loading } = useTheme()
const resolvedComponent = shallowRef<Component | null>(null)
const isNotFound = ref(false)
const isDestroyed = ref(false)
const lastResolveDebug = ref<{ matchingKey: string; page: string }>({ matchingKey: '', page: '' })
let currentResolveId = 0
const isLoadingTheme = computed(() => loading.value && !resolvedComponent.value)

function unwrapModule(mod: { default: Component } | Component): Component {
  if (mod && typeof mod === 'object' && 'default' in mod && (mod as { default: Component }).default) {
    return (mod as { default: Component }).default
  }
  return mod as Component
}

async function resolveView() {
  if (isDestroyed.value) return

  // While theme revalidates, keep the current view (prevents empty remount / inject storms).
  if (loading.value) {
    isNotFound.value = false
    return
  }

  const pageName = props.page

  if (isUploadedThemeActive(activeTheme.value)) {
    resolvedComponent.value = loadDynamicThemeComponent({
      slug: String(activeTheme.value?.slug ?? ''),
      bundleUrl: String(activeTheme.value?.bundle_url),
      page: pageName,
      bundleChecksum: (activeTheme.value?.manifest as { bundle_checksum?: string } | undefined)?.bundle_checksum ?? null,
    })
    isNotFound.value = false
    return
  }

  const themeSlugs = buildThemeViewResolveCandidates(activeTheme.value)
  let matchingKey = findThemeViewKey(viewModules, themeSlugs, pageName)
  if (!matchingKey) {
    matchingKey = findThemeViewKey(viewModules, [...BUNDLED_FRONTEND_THEME_SLUGS], pageName)
  }
  lastResolveDebug.value = { matchingKey: matchingKey ?? '', page: pageName }

  const resolveId = ++currentResolveId
  isNotFound.value = false

  if (!matchingKey || !viewModules[matchingKey]) {
    if (resolveId !== currentResolveId || isDestroyed.value) return
    logger.error('[ThemePageResolver] View not found', {
      page: pageName,
      themeSlugs,
      totalModules: Object.keys(viewModules).length,
    })
    isNotFound.value = true
    resolvedComponent.value = null
    return
  }

  const cacheKey = `${themeSlugs.join('|')}|${pageName}|${matchingKey}`
  const cached = componentCache.get(cacheKey)
  if (cached) {
    if (resolveId === currentResolveId && !isDestroyed.value) {
      resolvedComponent.value = cached
    }
    return
  }

  try {
    const mod = await viewModules[matchingKey]!()
    if (resolveId !== currentResolveId || isDestroyed.value) return
    const comp = unwrapModule(mod)
    componentCache.set(cacheKey, comp)
    resolvedComponent.value = comp
  } catch (err) {
    if (resolveId !== currentResolveId || isDestroyed.value) return
    logger.error('[ThemePageResolver] Failed to load view', {
      page: pageName,
      matchingKey,
      err,
    })
    isNotFound.value = true
    resolvedComponent.value = null
  }
}

const themeResolveKey = computed(() => {
  const t = activeTheme.value
  const slug = t && typeof t.slug === 'string' ? t.slug : ''
  const parent = t && typeof t.parent_theme === 'string' ? t.parent_theme : ''
  return `${slug}\n${parent}`
})

onBeforeUnmount(() => {
  isDestroyed.value = true
  currentResolveId++
})

if (typeof window !== 'undefined') {
  window.addEventListener('ja-frontend-theme-activated', () => {
    componentCache.clear()
    void resolveView()
  })
}

watch([themeResolveKey, () => props.page], () => {
  void resolveView()
}, { immediate: true })

watch(() => loading.value, (isLoading, wasLoading) => {
  // Only re-resolve when a load finishes (true → false), not when it starts.
  if (wasLoading && !isLoading) {
    void resolveView()
  }
})
</script>

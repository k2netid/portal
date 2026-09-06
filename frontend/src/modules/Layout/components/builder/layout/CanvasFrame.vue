<template>
  <div 
    class="canvas-frame flex-1 min-h-0 w-full h-full overflow-auto flex justify-center custom-scrollbar select-none"
    :class="[
      `canvas-frame--${device}`,
      device === 'desktop' && zoom === 100 && !width ? 'items-stretch p-0' : 'items-start p-4 md:p-8',
    ]"
  >
    <div
      class="canvas-frame__stage-wrapper flex flex-col items-center justify-start shrink-0 transition-all duration-150"
      :style="stageWrapperStyle"
    >
      <!-- DESKTOP BROWSER FRAME -->
      <div
        v-if="device === 'desktop'"
        class="canvas-frame__viewport canvas-frame__viewport--desktop relative bg-background overflow-hidden flex flex-col min-h-0 origin-top transition-transform duration-150"
        :class="[
          zoom === 100 && !width
            ? 'w-full h-full rounded-none border-0 shadow-none'
            : 'rounded-2xl border border-border/80 bg-card shadow-2xl ring-1 ring-black/5 dark:ring-white/10 shrink-0',
        ]"
        :style="previewStyles"
      >
        <!-- macOS Studio Window Header (shown when scaled or custom width) -->
        <div
          v-if="zoom !== 100 || !!width"
          class="h-9 px-4 flex items-center justify-between border-b border-border/60 bg-muted/60 backdrop-blur-md shrink-0 select-none"
        >
          <!-- Traffic Lights -->
          <div class="flex items-center gap-1.5 w-16">
            <div class="w-2.5 h-2.5 rounded-full bg-[#ff5f56] border border-black/10 shadow-xs" />
            <div class="w-2.5 h-2.5 rounded-full bg-[#ffbd2e] border border-black/10 shadow-xs" />
            <div class="w-2.5 h-2.5 rounded-full bg-[#27c93f] border border-black/10 shadow-xs" />
          </div>

          <!-- Address Pill -->
          <div class="px-3 py-0.5 rounded-lg bg-background/80 border border-border/60 text-[11px] font-mono text-muted-foreground flex items-center gap-1.5 shadow-2xs max-w-sm w-64 justify-center">
            <Lock class="w-3 h-3 text-emerald-500 shrink-0" />
            <span class="truncate">{{ previewUrl }}</span>
          </div>

          <!-- Resolution tag -->
          <div class="w-16 flex justify-end">
            <span class="text-[10px] font-mono font-semibold text-muted-foreground/60">{{ width ? `${width}px` : '1280px' }}</span>
          </div>
        </div>

        <!-- Grid Overlay -->
        <div v-if="showGridOverlay" class="canvas-grid-overlay" />

        <!-- Inner Screen Container -->
        <div 
          class="canvas-frame__screen flex-1 min-h-0 w-full"
          :class="zoom === 100 && !width ? 'h-full min-h-full' : 'overflow-y-auto overflow-x-hidden custom-scrollbar'"
        >
          <slot />
        </div>
      </div>

      <!-- TABLET FRAME (iPad Pro Titanium Bezel) -->
      <div
        v-else-if="device === 'tablet'"
        class="canvas-frame__viewport canvas-frame__viewport--tablet relative p-3 bg-gradient-to-b from-slate-800 via-slate-900 to-slate-950 rounded-[2.5rem] border border-slate-700/60 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] ring-1 ring-white/10 shrink-0 flex flex-col origin-top transition-transform duration-150"
        :style="previewStyles"
      >
        <!-- Camera Dot -->
        <div class="absolute top-1.5 left-1/2 -translate-x-1/2 w-2.5 h-2.5 rounded-full bg-slate-950 border border-slate-800 shadow-inner z-30 pointer-events-none" />

        <!-- Inner Screen -->
        <div class="rounded-[2rem] overflow-hidden bg-background flex flex-col flex-1 min-h-0 border border-slate-900/50 shadow-inner relative">
          <!-- Status Bar -->
          <div class="h-7 w-full shrink-0 z-20 flex items-center justify-between px-6 bg-slate-900/90 text-slate-300 text-[11px] font-medium border-b border-white/5 select-none">
            <div>9:41</div>
            <div class="flex items-center gap-2">
              <Wifi class="w-3 h-3 text-slate-400" />
              <BatteryFull class="w-3 h-3 text-slate-400" />
            </div>
          </div>

          <!-- Grid Overlay -->
          <div v-if="showGridOverlay" class="canvas-grid-overlay" />

          <!-- Screen Content (Scrollable) -->
          <div class="canvas-frame__screen flex-1 min-h-0 w-full overflow-y-auto overflow-x-hidden custom-scrollbar">
            <slot />
          </div>

          <!-- Bottom Home Indicator -->
          <div class="h-4 w-full shrink-0 z-20 flex items-center justify-center bg-slate-900/90 select-none">
            <div class="w-32 h-1 rounded-full bg-white/25" />
          </div>
        </div>
      </div>

      <!-- MOBILE FRAME (iPhone 16 Pro Dynamic Island) -->
      <div
        v-else-if="device === 'mobile'"
        class="canvas-frame__viewport canvas-frame__viewport--mobile relative p-2.5 bg-gradient-to-b from-slate-800 via-slate-900 to-slate-950 rounded-[3rem] border border-slate-700/60 shadow-[0_30px_70px_-15px_rgba(0,0,0,0.6)] ring-1 ring-white/10 shrink-0 flex flex-col origin-top transition-transform duration-150"
        :style="previewStyles"
      >
        <!-- Dynamic Island -->
        <div class="absolute top-3.5 left-1/2 -translate-x-1/2 w-24 h-6 rounded-full bg-black z-30 flex items-center justify-end px-2.5 shadow-md pointer-events-none border border-white/10">
          <div class="w-2.5 h-2.5 rounded-full bg-slate-900 border border-slate-800" />
        </div>

        <!-- Inner Screen -->
        <div class="rounded-[2.4rem] overflow-hidden bg-background flex flex-col flex-1 min-h-0 border border-slate-900/50 shadow-inner relative">
          <!-- Status Bar -->
          <div class="h-8 pt-1.5 w-full shrink-0 z-20 flex items-center justify-between px-6 bg-slate-900/90 text-slate-300 text-[11px] font-medium border-b border-white/5 select-none">
            <div>9:41</div>
            <div class="flex items-center gap-1.5">
              <Wifi class="w-3 h-3 text-slate-400" />
              <BatteryFull class="w-3 h-3 text-slate-400" />
            </div>
          </div>

          <!-- Grid Overlay -->
          <div v-if="showGridOverlay" class="canvas-grid-overlay" />

          <!-- Screen Content (Scrollable) -->
          <div class="canvas-frame__screen flex-1 min-h-0 w-full overflow-y-auto overflow-x-hidden custom-scrollbar">
            <slot />
          </div>

          <!-- Bottom Home Indicator -->
          <div class="h-4 w-full shrink-0 z-20 flex items-center justify-center bg-slate-900/90 select-none">
            <div class="w-28 h-1 rounded-full bg-white/25" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import {
  BatteryFull,
  Lock,
  Wifi,
} from 'lucide-vue-next'
import type { BuilderInstance } from '@/modules/Layout/types/builder'

interface Props {
  device?: 'desktop' | 'tablet' | 'mobile';
  zoom?: number;
  width?: number | string | null;
}

const props = withDefaults(defineProps<Props>(), {
  device: 'desktop',
  zoom: 100,
  width: null
})

const builder = inject<BuilderInstance | null>('builder', null)
const showGridOverlay = computed(() => builder?.showGrid?.value ?? false)

const previewHost = computed(() => (typeof window !== 'undefined' && window.location.host ? window.location.host : 'portal.local'))

const previewUrl = computed(() => {
  const slug = (builder?.content?.value?.slug || '').replace(/^\/+/, '')
  return `${previewHost.value}/${slug}`
})

const stageWrapperStyle = computed(() => {
  const scale = (props.zoom ?? 100) / 100
  if (props.device === 'desktop' && props.zoom === 100 && !props.width) {
    return { width: '100%', height: '100%' }
  }

  let baseW = typeof props.width === 'number' ? props.width : (props.width ? parseInt(String(props.width), 10) : 1280)
  let baseH = 900
  if (props.device === 'tablet') {
    baseW = 768 + 28
    baseH = 1024 + 28
  } else if (props.device === 'mobile') {
    baseW = 390 + 28
    baseH = 844 + 28
  }

  return {
    width: `${baseW * scale}px`,
    height: `${baseH * scale}px`,
    minHeight: `${baseH * scale}px`,
  }
})

const previewStyles = computed(() => {
  const scale = (props.zoom ?? 100) / 100
  let baseW = typeof props.width === 'number' ? `${props.width}px` : (props.width ? `${props.width}` : '1280px')
  let baseH = '900px'

  if (props.device === 'mobile') {
    baseW = `${390 + 28}px`
    baseH = `${844 + 28}px`
  } else if (props.device === 'tablet') {
    baseW = `${768 + 28}px`
    baseH = `${1024 + 28}px`
  } else if (props.zoom === 100 && !props.width) {
    return {
      width: '100%',
      height: '100%',
      transform: 'none',
    }
  }

  return {
    width: baseW,
    height: baseH,
    transform: `scale(${scale}) translateZ(0)`,
    transformOrigin: 'top center',
  }
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(120, 120, 140, 0.25);
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(120, 120, 140, 0.45);
}

/* Grid Overlay */
.canvas-grid-overlay {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 998;
  background-image: 
    linear-gradient(to right, rgba(100, 100, 255, 0.08) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(100, 100, 255, 0.08) 1px, transparent 1px);
  background-size: 20px 20px;
}
</style>

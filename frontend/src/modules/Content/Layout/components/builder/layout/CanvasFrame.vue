<template>
  <div class="canvas-frame" :class="[`canvas-frame--${device}`]">
    <div 
      class="canvas-frame__viewport"
      :style="viewportStyle"
    >
      <!-- Tablet Camera Dot -->
      <div v-if="device === 'tablet'" class="device-tablet-camera"></div>

      <!-- Mobile Dynamic Island -->
      <div v-if="device === 'mobile'" class="device-mobile-island">
        <div class="device-mobile-island__camera"></div>
      </div>

      <!-- Grid Overlay -->
      <div v-if="showGridOverlay" class="canvas-grid-overlay"></div>

      <!-- Inner Screen Container (Guarantees Content Clipping to Frame) -->
      <div class="canvas-frame__screen">
        <slot />
      </div>

      <!-- Mobile Home Indicator -->
      <div v-if="device === 'mobile'" class="device-mobile-home-indicator"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import type { BuilderInstance } from '@/types/builder'

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

const viewportStyle = computed(() => {
  const widths: Record<string, number | null> = {
    desktop: 1280,
    tablet: 768,
    mobile: 390
  }

  const width = widths[props.device]
  
  const styles: Record<string, string | number> = {
    transform: `scale(${props.zoom / 100}) translateZ(0)`,
    transformOrigin: 'top center',
    willChange: 'transform, width'
  }
  
  if (props.width) {
    styles.width = `${props.width}px`
    styles.maxWidth = `${props.width}px`
  } else if (width) {
    styles.width = `${width}px`
    styles.maxWidth = `${width}px`
  } else {
    styles.width = '100%'
    styles.maxWidth = '100%'
  }
  
  return styles
})
</script>

<style scoped>
.canvas-frame {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  width: 100%;
  height: 100%;
  overflow: auto;
  padding: 32px 0;
  min-width: 0;
}

.canvas-frame__viewport {
  background: var(--builder-bg-canvas, #ffffff);
  min-height: calc(100% - 64px);
  height: auto;
  box-sizing: content-box; 
  position: relative; 
  z-index: 1; 
  flex-shrink: 0;
  margin: 0 auto; /* Centered when fitting, scrollable from 0 when overflowing */
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), border-radius 0.3s ease, box-shadow 0.3s ease;
}

.canvas-frame__screen {
  width: 100%;
  height: 100%;
  min-height: inherit;
  overflow-x: hidden;
  box-sizing: border-box;
}

/* =========================================
   DESKTOP FRAME
   ========================================= */
.canvas-frame--desktop .canvas-frame__viewport {
  border-radius: 12px;
  box-shadow: 
    0 20px 50px -15px rgba(0, 0, 0, 0.18),
    0 0 0 1px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

/* =========================================
   TABLET FRAME (iPad Pro Modern Style)
   ========================================= */
.canvas-frame--tablet .canvas-frame__viewport {
  border-radius: 36px;
  border: 14px solid #1c1d22;
  box-shadow: 
    0 30px 80px -20px rgba(0, 0, 0, 0.45),
    0 0 0 1px rgba(255, 255, 255, 0.12),
    inset 0 0 0 1px rgba(0, 0, 0, 0.8);
  overflow: hidden;
}

.device-tablet-camera {
  position: absolute;
  top: -9px;
  left: 50%;
  transform: translateX(-50%);
  width: 6px;
  height: 6px;
  background: #0d0e12;
  border-radius: 50%;
  box-shadow: inset 0 0 2px rgba(255, 255, 255, 0.35);
  z-index: 100;
  pointer-events: none;
}

/* =========================================
   MOBILE FRAME (iPhone 16 Pro Style)
   ========================================= */
.canvas-frame--mobile .canvas-frame__viewport {
  border-radius: 48px;
  border: 12px solid #16171b;
  box-shadow: 
    0 35px 90px -20px rgba(0, 0, 0, 0.55),
    0 0 0 1px rgba(255, 255, 255, 0.14),
    inset 0 0 0 1px rgba(0, 0, 0, 0.9);
  overflow: hidden;
}

.device-mobile-island {
  position: absolute;
  top: 10px;
  left: 50%;
  transform: translateX(-50%);
  width: 92px;
  height: 24px;
  background: #000000;
  border-radius: 16px;
  z-index: 999;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding-right: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
  pointer-events: none;
}

.device-mobile-island__camera {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #0d0f1a;
  box-shadow: inset 0 0 2px rgba(255, 255, 255, 0.25);
}

.device-mobile-home-indicator {
  position: sticky;
  bottom: 8px;
  left: 50%;
  margin: 16px auto 4px auto;
  width: 120px;
  height: 4px;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 4px;
  z-index: 999;
  pointer-events: none;
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

<template>
  <div class="canvas-frame" :class="[`canvas-frame--${device}`]">
    <div 
      class="canvas-frame__viewport"
      :style="viewportStyle"
    >
      <!-- Grid Overlay -->
      <div v-if="showGridOverlay" class="canvas-grid-overlay"></div>
      <slot />
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
    mobile: 375
  }

  const width = widths[props.device]
  
  const styles: Record<string, string | number> = {
    transform: `scale(${props.zoom / 100}) translateZ(0)`, // translateZ forces containing block
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
  justify-content: center;
  align-items: flex-start;
  width: 100%;
  height: 100%;
  overflow: auto; /* Enable both X and Y scrolling */
  padding: var(--spacing-lg) 0;
}

.canvas-frame__viewport {
  background: var(--builder-bg-canvas);
  border-radius: var(--border-radius-md);
  box-shadow: var(--shadow-lg);
  min-height: calc(100% - 2 * var(--spacing-lg));
  height: auto;
  overflow: hidden !important; /* Force clip */
  clip-path: inset(0 round var(--border-radius-md)); /* Robust hardware clipping */
  box-sizing: content-box; 
  position: relative; 
  z-index: 1; 
  contain: content; 
}

/* Device-specific frames */
.canvas-frame--tablet .canvas-frame__viewport {
  border-radius: var(--border-radius-lg);
  border: 12px solid #1a1a1a;
}

.canvas-frame--mobile .canvas-frame__viewport {
  border-radius: 32px;
  border: 12px solid #1a1a1a;
}

/* Grid Overlay */
.canvas-grid-overlay {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 999;
  background-image: 
    linear-gradient(to right, rgba(100, 100, 255, 0.1) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(100, 100, 255, 0.1) 1px, transparent 1px);
  background-size: 20px 20px;
}
</style>

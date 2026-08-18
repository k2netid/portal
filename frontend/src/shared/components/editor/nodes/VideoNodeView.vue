<template>
  <node-view-wrapper 
    as="div" 
    :class="[
      node.attrs.displayMode === 'inline' ? 'inline-flex align-baseline mr-1' : 
      node.attrs.displayMode === 'float-left' ? 'float-left mr-4 mb-2' :
      node.attrs.displayMode === 'float-right' ? 'float-right ml-4 mb-2' :
      'flex flex-col'
    ]"
    :style="{ 
      lineHeight: '0',
      display: (node.attrs.displayMode === 'float-left' || node.attrs.displayMode === 'float-right') ? 'block' : undefined,
      marginRight: node.attrs.displayMode === 'float-left' ? node.attrs.margin : (node.attrs.displayMode === 'block' && (resolvedAlign === 'center' || resolvedAlign === 'left')) ? 'auto' : '0',
      marginLeft: node.attrs.displayMode === 'float-right' ? node.attrs.margin : (node.attrs.displayMode === 'block' && (resolvedAlign === 'center' || resolvedAlign === 'right')) ? 'auto' : '0',
      marginBottom: (node.attrs.displayMode === 'float-left' || node.attrs.displayMode === 'float-right') ? node.attrs.margin : '1.5rem',
      marginTop: '0.5rem'
    }"
    data-drag-handle
  >
    <div 
      class="relative block group" 
      :style="{ 
        width: (node.attrs.width === '100%' && node.attrs.displayMode === 'block') ? '100%' : (node.attrs.width !== 'auto' ? node.attrs.width : undefined),
        maxWidth: '100%'
      }"
    >
      <video
        :src="node.attrs.src"
        :controls="node.attrs.controls"
        :autoplay="node.attrs.autoplay"
        :loop="node.attrs.loop"
        class="transition-opacity duration-200 block"
        :class="{ 
          'ring-2 ring-primary': selected,
          'hover:ring-2 hover:ring-primary/50': !selected 
        }"
        :style="{
          width: node.attrs.width !== 'auto' ? node.attrs.width : undefined,
          height: node.attrs.height !== 'auto' ? node.attrs.height : 'auto',
          borderRadius: node.attrs.borderRadius,
          maxWidth: '100%'
        }"
      />

      <!-- Resize Handles -->
      <div 
        v-if="selected" 
        class="absolute bottom-1 right-1 w-4 h-4 bg-primary rounded-sm cursor-nwse-resize z-10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
        @pointerdown.stop.prevent="startResize"
      >
        <div class="w-2 h-2 border-r-2 border-b-2 border-primary-foreground opacity-50" />
      </div>
    </div>
  </node-view-wrapper>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { nodeViewProps, NodeViewWrapper } from '@tiptap/vue-3'

const props = defineProps(nodeViewProps)
const resolvedAlign = computed(() => props.node.attrs.textAlign || props.node.attrs.align || 'center')

// Resizing Logic
let isResizing = false
let startX = 0
let startWidth = 0
// let startHeight = 0
let aspectRatio = 1

const startResize = (e: PointerEvent) => {
    isResizing = true
    startX = e.clientX
    
    // Get current dimensions
    const video = (e.target as HTMLElement).closest('.relative')?.querySelector('video') as HTMLVideoElement
    startWidth = video.clientWidth
    // startHeight = video.clientHeight
    aspectRatio = startWidth / video.clientHeight
    
    document.addEventListener('pointermove', onResize)
    document.addEventListener('pointerup', stopResize)
    document.body.style.cursor = 'nwse-resize'
    document.body.style.userSelect = 'none'
}

const onResize = (e: PointerEvent) => {
    if (!isResizing) return
    
    const dx = e.clientX - startX
    const newWidth = Math.max(50, startWidth + dx)
    const newHeight = newWidth / aspectRatio
    
    props.updateAttributes({
        width: `${Math.round(newWidth)}px`,
        height: `${Math.round(newHeight)}px`
    })
}

const stopResize = () => {
    isResizing = false
    document.removeEventListener('pointermove', onResize)
    document.removeEventListener('pointerup', stopResize)
    document.body.style.cursor = ''
    document.body.style.userSelect = ''
}
</script>

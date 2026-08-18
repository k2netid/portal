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
      width: (node.attrs.width === '100%') ? '100%' : 'fit-content',
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
      <img
        :src="node.attrs.src"
        :alt="node.attrs.alt"
        :title="node.attrs.title"
        class="transition-opacity duration-200 block"
        :class="{ 
          'ring-2 ring-primary': selected,
          'hover:ring-2 hover:ring-primary/50': !selected 
        }"
        :style="{
          width: node.attrs.width !== 'auto' ? node.attrs.width : undefined,
          height: node.attrs.height !== 'auto' ? node.attrs.height : 'auto',
          borderRadius: node.attrs.borderRadius,
          borderWidth: node.attrs.borderWidth,
          borderColor: node.attrs.borderColor,
          borderStyle: node.attrs.borderWidth && node.attrs.borderWidth !== '0px' ? 'solid' : 'none',
          maxWidth: '100%'
        }"
      >
            
      <!-- Resize Handles -->
      <div 
        v-if="selected" 
        class="absolute bottom-1 right-1 w-4 h-4 bg-primary rounded-sm cursor-nwse-resize z-10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
        @pointerdown.stop.prevent="startResize"
      >
        <div class="w-2 h-2 border-r-2 border-b-2 border-primary-foreground opacity-50" />
      </div>
            
      <!-- URL Input Popover (Simple version for quick edits) -->
      <div
        v-if="selected && showUrlInput"
        class="absolute top-full right-0 mt-2 p-2 bg-background border rounded-md shadow-lg z-50 flex items-center gap-2 w-60"
        @mousedown.stop
      >
        <input 
          v-model="tempSrc" 
          class="flex-1 px-2 py-1 text-xs border rounded bg-background"
          :placeholder="t('publishing.editor.placeholders.imageUrl')" 
          @keydown.enter="applySrc"
        >
        <Button
          size="icon"
          variant="ghost"
          class="h-6 w-6"
          @click="applySrc"
        >
          <Check class="w-3 h-3" />
        </Button>
      </div>
    </div>
  </node-view-wrapper>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { nodeViewProps, NodeViewWrapper } from '@tiptap/vue-3'
import {
  Check,
} from 'lucide-vue-next';
import { Button } from '@/shared/components/ui';

const { t } = useI18n()
const props = defineProps(nodeViewProps)
const resolvedAlign = computed(() => props.node.attrs.textAlign || props.node.attrs.align || 'center')

const showUrlInput = ref(false)
const tempSrc = ref(props.node.attrs.src)

const applySrc = () => {
    props.updateAttributes({
        src: tempSrc.value,
    })
    showUrlInput.value = false
}

// Resizing Logic
let isResizing = false
let startX = 0
let startWidth = 0
// let startHeight = 0 // Uncomment if used
let aspectRatio = 1

const startResize = (e: PointerEvent) => {
    isResizing = true
    startX = e.clientX
    
    // Get current dimensions
    const img = (e.target as HTMLElement).closest('.relative')?.querySelector('img') as HTMLImageElement
    startWidth = img.clientWidth
    // startHeight = img.clientHeight
    aspectRatio = startWidth / img.clientHeight
    
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

<template>
  <node-view-wrapper 
    class="html-embed-wrapper"
    :style="{
      justifyContent: justifyContent,
      textAlign: node.attrs.align,
    }"
    :class="{
      'float-left': node.attrs.displayMode === 'float-left',
      'float-right': node.attrs.displayMode === 'float-right',
      'w-full': node.attrs.displayMode === 'block',
      'inline-block': node.attrs.displayMode === 'inline'
    }"
  >
    <div 
      class="html-embed-container"
      :class="{ 
        'is-selected': selected,
        'is-empty': !node.attrs.html
      }"
      :style="containerStyles"
      @dblclick="onDblClick"
    >
      <!-- Resize Handles (Only when selected) -->
      <template v-if="selected">
        <div
          class="resize-handle top-left"
          @mousedown.stop="startResize($event, 'tl')"
        />
        <div
          class="resize-handle top-right"
          @mousedown.stop="startResize($event, 'tr')"
        />
        <div
          class="resize-handle bottom-left"
          @mousedown.stop="startResize($event, 'bl')"
        />
        <div
          class="resize-handle bottom-right"
          @mousedown.stop="startResize($event, 'br')"
        />
        <div
          class="resize-handle left"
          @mousedown.stop="startResize($event, 'l')"
        />
        <div
          class="resize-handle right"
          @mousedown.stop="startResize($event, 'r')"
        />
      </template>

      <!-- Interact Overlay: Captures clicks when not in interactive mode -->
      <div 
        v-if="!isInteractive && node.attrs.html" 
        class="absolute inset-0 z-10 bg-transparent cursor-pointer"
        @click.stop="selectNode"
      />

      <!-- Interact Toggle Button (Visible when selected) -->
      <div
        v-if="selected || isInteractive"
        class="absolute top-2 right-2 z-50 flex gap-1 bg-background/80 backdrop-blur-sm p-1 rounded-md shadow-sm border border-border/50"
      >
        <button 
          class="p-1.5 bg-background text-foreground rounded-md shadow-sm border border-border/50 hover:bg-muted transition-colors"
          :title="t('publishing.editor.actions.fit')"
          type="button"
          @click="fitToContent"
        >
          <Maximize2 class="w-4 h-4" />
        </button>
        <div class="h-4 w-px bg-border/50 mx-1" />
        <button 
          v-if="!isInteractive"
          class="flex items-center gap-1.5 px-2 py-1.5 bg-background text-foreground rounded-md shadow-sm border border-border/50 hover:bg-muted transition-colors text-xs font-medium" 
          :title="t('publishing.editor.actions.interact')"
          type="button"
          @click="startInteraction"
        >
          <MousePointerClick class="w-3.5 h-3.5" />
          <span>{{ t('publishing.editor.actions.interactShort') }}</span>
        </button>
        <button 
          v-else
          class="flex items-center gap-1.5 px-2 py-1.5 bg-primary text-primary-foreground rounded-md shadow-sm hover:bg-primary/90 transition-colors text-xs font-medium" 
          :title="t('publishing.editor.actions.stopInteraction')"
          type="button"
          @click="stopInteraction"
        >
          <X class="w-3.5 h-3.5" />
          <span>{{ t('common.actions.close') }}</span>
        </button>
      </div>

      <SafeHtml
        v-if="node.attrs.html"
        class="html-embed-content"
        :class="{ 'is-interactive': isInteractive }"
        :html="node.attrs.html"
        mode="cms"
      />
      <div
        v-else
        class="flex flex-col items-center gap-2 text-muted-foreground select-none pointer-events-none"
      >
        <Code class="w-8 h-8 opacity-50" />
        <div class="text-sm font-medium">
          {{ t('publishing.editor.embed.empty') }}
        </div>
        <div class="text-xs opacity-75">
          {{ t('publishing.editor.embed.configure') }}
        </div>
      </div>
    </div>
  </node-view-wrapper>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3'
import {
  Code,
  Maximize2,
  MousePointerClick,
  X,
} from 'lucide-vue-next';
import SafeHtml from '@/modules/Core/System/components/ui/SafeHtml.vue';
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps(nodeViewProps)

const isInteractive = ref(false)

// Reset interaction when deselected
watch(() => props.selected, (isSelected) => {
    if (!isSelected) {
        isInteractive.value = false
    }
})

const startInteraction = () => {
    isInteractive.value = true
}

const stopInteraction = () => {
    isInteractive.value = false
}

// Select node manually if overlay clicked (redundant but safe)
const selectNode = () => {
    // Tiptap handles selection via DOM event, but overlay ensures it catches it
}

const fitToContent = () => {
    let html = props.node.attrs.html || ''
    // Try to extract original dimensions from HTML
    const widthMatch = html.match(/(?:width|WIDTH)=["']?(\d+)(?:px)?["']?/)
    const heightMatch = html.match(/(?:height|HEIGHT)=["']?(\d+)(?:px)?["']?/)
    
    if (widthMatch && widthMatch[1] && heightMatch && heightMatch[1]) {
        props.updateAttributes({
            width: `${widthMatch[1]}px`,
            height: `${heightMatch[1]}px`
        })
    } else {
        // Fallback or if no dimensions found, maybe reset to defaults?
        // Let's just reset to empty to allow auto-flow if nothing found
         props.updateAttributes({
            width: undefined, // undefined will remove attr? or use null
            height: undefined
        })
    }
}

// contentRef removed as it was unused

// Removed sanitizedHtml computed property as it is now handled by SafeHtml component

const justifyContent = computed(() => {
    if (props.node.attrs.displayMode === 'block') {
        switch (props.node.attrs.align) {
            case 'left': return 'flex-start'
            case 'right': return 'flex-end'
            case 'center': default: return 'center'
        }
    }
    return undefined
})

// Helper to ensure pixel units
const toPx = (val: string | number | undefined) => {
    if (!val) return undefined
    const str = String(val)
    return str.endsWith('px') ? str : `${str}px`
}

const containerStyles = computed(() => ({
    width: props.node.attrs.width,
    height: props.node.attrs.height,
    borderRadius: toPx(props.node.attrs.borderRadius),
    borderWidth: toPx(props.node.attrs.borderWidth),
    borderColor: props.node.attrs.borderColor,
    margin: toPx(props.node.attrs.margin),
    marginRight: props.node.attrs.displayMode === 'float-left' ? (props.node.attrs.margin ? toPx(props.node.attrs.margin) : '16px') : undefined,
    marginLeft: props.node.attrs.displayMode === 'float-right' ? (props.node.attrs.margin ? toPx(props.node.attrs.margin) : '16px') : undefined,
}))

// Resize Logic
let resizing = false
let startX = 0
let startY = 0
let startWidth = 0
let startHeight = 0
let aspectRatio = 1 // Store aspect ratio
let activeHandle = ''

const startResize = (e: MouseEvent, handle: string) => {
    if (isInteractive.value) return // Disable resize in interactive mode
    e.preventDefault() // Prevent text selection
    resizing = true
    activeHandle = handle
    startX = e.clientX
    startY = e.clientY
    
    // Get current dimensions
    const container = (e.target as HTMLElement).parentElement as HTMLElement
    const rect = container.getBoundingClientRect()
    startWidth = rect.width
    startHeight = rect.height
    aspectRatio = startWidth / startHeight

    document.addEventListener('mousemove', onResize)
    document.addEventListener('mouseup', stopResize)
}

const onResize = (e: MouseEvent) => {
    if (!resizing) return

    const dx = e.clientX - startX
    const dy = e.clientY - startY
    
    let newWidth = startWidth
    let newHeight = startHeight

    // Corner Handles (Preserve Aspect Ratio)
    if (['tl', 'tr', 'bl', 'br'].includes(activeHandle)) {
         // Logic to determine dominant axis or just use 'width' driven resize for simplicity?
         // Let's use width driven for left/right movement
         if (activeHandle.includes('r')) {
             newWidth = startWidth + dx
         } else {
             newWidth = startWidth - dx
         }
         
         // Enforce aspect ratio
         newHeight = newWidth / aspectRatio
    } 
    // Side Handles (Free Resize)
    else {
        if (activeHandle.includes('l')) newWidth = startWidth - dx
        if (activeHandle.includes('r')) newWidth = startWidth + dx
        if (activeHandle.includes('t')) newHeight = startHeight - dy
        if (activeHandle.includes('b')) newHeight = startHeight + dy
    }

    // Minimum constraints
    if (newWidth < 50) newWidth = 50
    if (newHeight < 50) newHeight = 50

    // Update attributes (pixels for now during resize)
    // We update eagerly for smooth feedback, might consider throttling if performance is issue
    props.updateAttributes({
        width: `${Math.round(newWidth)}px`,
        height: `${Math.round(newHeight)}px`
    })
}

const stopResize = () => {
    resizing = false
    activeHandle = ''
    document.removeEventListener('mousemove', onResize)
    document.removeEventListener('mouseup', stopResize)
}

// Double Click Handler - fallback if global handler misses (though global one handles it mostly)
const onDblClick = () => {
    // This is optional since we have a global handler, but good for robustness
}

</script>

<style scoped>
.html-embed-wrapper {
    display: flex;
    position: relative;
    user-select: none; /* Important for drag */
    line-height: 0;
    transition: all 0.2s ease;
    clear: both;
}

.html-embed-wrapper.float-left {
    float: left;
    display: block;
    clear: none;
}

.html-embed-wrapper.float-right {
    float: right;
    display: block;
    clear: none;
}

.html-embed-wrapper.inline-block {
    display: inline-block;
    vertical-align: middle;
}

.html-embed-container {
    position: relative; /* Anchor for handles */
    transition: box-shadow 0.2s ease;
    border: 1px solid transparent; /* default invisible border */
    min-width: 50px;
    min-height: 50px;
}

.html-embed-container.is-selected {
    outline: 2px solid hsl(var(--primary));
    outline-offset: 2px;
}

.html-embed-container.is-empty {
    border: 2px dashed hsl(var(--muted-foreground)/0.3);
    border-radius: 0.5rem;
    background: hsl(var(--muted)/0.3);
}

.html-embed-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    min-width: 200px;
    min-height: 120px;
    pointer-events: none; /* Let clicks pass through to container */
}

.html-embed-content {
    width: 100%;
    height: 100%;
    overflow: hidden; /* Prevent spillover */
}

/* Ensure iframes fill the container */
:deep(iframe) {
    width: 100%;
    height: 100%;
    border: none;
    pointer-events: none; /* Crucial: allows clicking on the container to select it, instead of interacting with iframe */
}

/* When interactive, allow pointer events on iframe */
.html-embed-content.is-interactive :deep(iframe) {
    pointer-events: auto;
}

/* Re-enable pointer events on iframe only when we might want to interact? 
   Actually for editor, it's better to disable iframe interaction so we can select/drag the node. 
   Interaction happens in preview/frontend or via property editing. 
   Or we can use a small overlay. 
*/
/* .is-selected :deep(iframe) handled by .is-interactive class now */


/* Resize Handles */
.resize-handle {
    position: absolute;
    width: 10px;
    height: 10px;
    background-color: hsl(var(--primary));
    border: 1px solid hsl(var(--background));
    border-radius: 50%; /* Circle handles */
    z-index: 50;
    display: none; /* Hidden by default */
}

.is-selected .resize-handle {
    display: block;
}

.resize-handle.top-left { top: -5px; left: -5px; cursor: nwse-resize; }
.resize-handle.top-right { top: -5px; right: -5px; cursor: nesw-resize; }
.resize-handle.bottom-left { bottom: -5px; left: -5px; cursor: nesw-resize; }
.resize-handle.bottom-right { bottom: -5px; right: -5px; cursor: nwse-resize; }

.resize-handle.left { 
    top: 50%; left: -5px; transform: translateY(-50%); 
    cursor: ew-resize; 
    border-radius: 2px; height: 16px; width: 6px;
}
.resize-handle.right { 
    top: 50%; right: -5px; transform: translateY(-50%); 
    cursor: ew-resize;
    border-radius: 2px; height: 16px; width: 6px;
}

</style>

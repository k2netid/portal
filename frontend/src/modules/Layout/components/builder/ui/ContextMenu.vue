<template>
  <div 
    v-if="visible" 
    class="context-menu"
    :style="menuStyle"
    @click.stop
  >
    <div class="menu-header" v-if="title">
        <span>{{ title }}</span>
        <span class="menu-type">{{ type }}</span>
    </div>
    <div class="menu-divider" v-if="title"></div>
    
    <!-- Module Mode Items -->
    <template v-if="mode === 'module'">
      <!-- Undo/Redo -->
      <div class="menu-item" :class="{ 'menu-item--disabled': !canUndo }" @click="handleAction('undo')">
         <Undo2 :size="14" />
         <span>{{ t('builder.fields.contextMenu.undo') }}</span>
         <span class="shortcut">⌘Z</span>
      </div>
      <div class="menu-item" :class="{ 'menu-item--disabled': !canRedo }" @click="handleAction('redo')">
         <Redo2 :size="14" />
         <span>{{ t('builder.fields.contextMenu.redo') }}</span>
         <span class="shortcut">⇧⌘Z</span>
      </div>

      <div class="menu-divider"></div>
      
      <!-- Module Actions -->
      <div class="menu-item" @click="handleAction('duplicate')">
         <CopyPlus :size="14" />
         <span>{{ t('builder.fields.contextMenu.duplicate', { type: typeLabel }) }}</span>
         <span class="shortcut">⌘D</span>
      </div>

      <div class="menu-item menu-item--danger" @click="handleAction('delete')">
         <Trash2 :size="14" />
         <span>{{ t('builder.fields.contextMenu.delete', { type: typeLabel }) }}</span>
         <span class="shortcut">Del</span>
      </div>

      <!-- Add Element (for containers only) -->
      <div v-if="isContainer" class="menu-item" @click="handleAction('add-element')">
         <Plus :size="14" />
         <span>{{ t('builder.fields.contextMenu.addElement') }}</span>
         <ChevronRight :size="12" class="submenu-arrow" />
      </div>

      <div class="menu-divider"></div>

      <!-- Copy/Paste -->
      <div class="menu-item" @click="handleAction('copy')">
         <Copy :size="14" />
         <span>{{ t('builder.fields.contextMenu.copy', { type: typeLabel }) }}</span>
         <span class="shortcut">⌘C</span>
      </div>

      <div class="menu-item" :class="{ 'menu-item--disabled': !hasClipboard }" @click="handleAction('paste')">
         <ClipboardPaste :size="14" />
         <span>{{ t('builder.fields.contextMenu.paste') }}</span>
         <span class="shortcut">⌘V</span>
      </div>

      <div class="menu-divider"></div>

      <!-- Styles -->
      <div class="menu-item" @click="handleAction('copy-style')">
         <Palette :size="14" />
         <span>{{ t('builder.fields.contextMenu.copyStyles') }}</span>
      </div>
      
      <div class="menu-item" :class="{ 'menu-item--disabled': !hasStyleClipboard }" @click="handleAction('paste-style')">
         <PaintBucket :size="14" />
         <span>{{ t('builder.fields.contextMenu.pasteStyles') }}</span>
      </div>

      <div class="menu-item" @click="handleAction('reset-styles')">
         <RotateCcw :size="14" />
         <span>{{ t('builder.fields.contextMenu.resetStyles') }}</span>
      </div>

      <div class="menu-divider"></div>
      
      <!-- Navigation -->
      <div v-if="hasParent" class="menu-item" @click="handleAction('parent')">
         <ArrowUpLeft :size="14" />
         <span>{{ t('builder.fields.contextMenu.goToParent') }}</span>
      </div>

      <div class="menu-item" @click="handleAction('go-to-layer')">
         <Layers :size="14" />
         <span>{{ t('builder.fields.contextMenu.goToLayer') }}</span>
      </div>

      <div class="menu-divider"></div>

      <!-- Settings -->
      <div class="menu-item" @click="handleAction('rename')">
         <Type :size="14" />
         <span>{{ t('builder.fields.contextMenu.renameLabel') }}</span>
      </div>

      <div class="menu-item" @click="handleAction('toggle-visibility')">
         <component :is="isDisabled ? Eye : EyeOff" :size="14" />
         <span>{{ isDisabled ? t('builder.fields.contextMenu.enable') : t('builder.fields.contextMenu.disable') }}</span>
      </div>

      <div v-if="canSaveToLibrary" class="menu-item" @click="handleAction('save-to-library')">
         <BookmarkPlus :size="14" />
         <span>{{ t('builder.fields.contextMenu.saveToLibrary') }}</span>
      </div>
    </template>

    <!-- Canvas Mode Items -->
    <template v-else-if="mode === 'canvas'">
      <div class="menu-item" @click="handleAction('canvas-settings')">
        <Settings :size="14" />
        <span>{{ t('builder.fields.contextMenu.canvasSettings') }}</span>
      </div>
      <div class="menu-item" @click="handleAction('export-canvas')">
        <Download :size="14" />
        <span>{{ t('builder.fields.contextMenu.exportCanvas') }}</span>
      </div>
      
      <div class="menu-divider"></div>
      
      <div class="menu-item" @click="handleAction('edit-canvas')">
        <Edit :size="14" />
        <span>{{ t('builder.fields.contextMenu.editCanvas') }}</span>
      </div>
      <div v-if="!isMainCanvas" class="menu-item" @click="handleAction('make-main-canvas')">
        <CheckCircle :size="14" />
        <span>{{ t('builder.fields.contextMenu.makeMainCanvas') }}</span>
      </div>
      <div class="menu-item" @click="handleAction('duplicate-canvas')">
        <Copy :size="14" />
        <span>{{ t('builder.fields.contextMenu.duplicateCanvas') }}</span>
      </div>
      <div v-if="!isMainCanvas" class="menu-item menu-item--danger" @click="handleAction('delete-canvas')">
        <Trash2 :size="14" />
        <span>{{ t('builder.fields.contextMenu.deleteCanvas') }}</span>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, computed, inject, type CSSProperties } from 'vue';
import { useI18n } from 'vue-i18n';
import Copy from 'lucide-vue-next/dist/esm/icons/copy.js';
import CopyPlus from 'lucide-vue-next/dist/esm/icons/copy-plus.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Type from 'lucide-vue-next/dist/esm/icons/type.js';
import ClipboardPaste from 'lucide-vue-next/dist/esm/icons/clipboard-paste.js';
import Undo2 from 'lucide-vue-next/dist/esm/icons/undo-2.js';
import Redo2 from 'lucide-vue-next/dist/esm/icons/redo-2.js';
import ArrowUpLeft from 'lucide-vue-next/dist/esm/icons/arrow-up-left.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import Edit from 'lucide-vue-next/dist/esm/icons/pen.js';
import CheckCircle from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js';
import Palette from 'lucide-vue-next/dist/esm/icons/palette.js';
import PaintBucket from 'lucide-vue-next/dist/esm/icons/paint-bucket.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import EyeOff from 'lucide-vue-next/dist/esm/icons/eye-off.js';
import BookmarkPlus from 'lucide-vue-next/dist/esm/icons/bookmark-plus.js';
import type { BuilderInstance } from '@/types/builder';

const { t } = useI18n();
const builder = inject<BuilderInstance>('builder');

interface Props {
    visible?: boolean;
    x?: number;
    y?: number;
    moduleId?: string;
    title?: string;
    type?: string;
    mode?: string;
}

const props = withDefaults(defineProps<Props>(), {
    visible: false,
    x: 0,
    y: 0,
    mode: 'module',
    moduleId: '',
    title: '',
    type: ''
});

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'action', action: string, moduleId?: string, mode?: string): void;
}>();

// Computed properties for menu state
const canUndo = computed(() => builder?.canUndo.value ?? false);
const canRedo = computed(() => builder?.canRedo.value ?? false);
const hasClipboard = computed(() => builder?.clipboard.value?.type === 'module');
const hasStyleClipboard = computed(() => builder?.clipboard.value?.type === 'styles');
const isMainCanvas = computed(() => props.type === 'Main');
const typeLabel = computed(() => props.type || 'Module');

const isContainer = computed(() => {
    const containerTypes = ['section', 'row', 'column'];
    return props.type ? containerTypes.includes(props.type.toLowerCase()) : false;
});

const hasParent = computed(() => {
    if (!props.moduleId || !builder) return false;
    const parent = builder.findParentById?.(builder.blocks.value, props.moduleId);
    return !!parent;
});

const canSaveToLibrary = computed(() => {
    const libraryTypes = ['section', 'row'];
    return props.type ? libraryTypes.includes(props.type.toLowerCase()) : false;
});

const isDisabled = computed(() => {
    if (!props.moduleId || !builder) return false;
    const module = builder.findModule?.(props.moduleId);
    return module?.settings?.disabled === true;
});

// Dynamic positioning to prevent overflow
const menuStyle = computed<CSSProperties>(() => {
    const style: CSSProperties = {
        top: `${props.y}px`,
        left: `${props.x}px`
    };
    
    // Adjust if menu would go off-screen (basic check)
    if (typeof window !== 'undefined') {
        const menuWidth = 220;
        const menuHeight = 400;
        
        if ((props.x || 0) + menuWidth > window.innerWidth) {
            style.left = `${(props.x || 0) - menuWidth}px`;
        }
        if ((props.y || 0) + menuHeight > window.innerHeight) {
            style.top = `${Math.max(10, window.innerHeight - menuHeight - 10)}px`;
        }
    }
    
    return style;
});

// Close on click outside
const handleClickOutside = (_e: Event) => {
    if (props.visible) {
        emit('close');
    }
};

const handleAction = (action: string) => {
    // Prevent disabled actions
    if (action === 'undo' && !canUndo.value) return;
    if (action === 'redo' && !canRedo.value) return;
    if (action === 'paste' && !hasClipboard.value) return;
    if (action === 'paste-style' && !hasStyleClipboard.value) return;
    
    emit('action', action, props.moduleId, props.mode);
    emit('close');
};

onMounted(() => {
    // Delay adding listeners to ensure we don't catch the same event that opened the menu
    setTimeout(() => {
        window.addEventListener('click', handleClickOutside, { capture: true });
        window.addEventListener('contextmenu', handleClickOutside); 
    }, 10);
});

onUnmounted(() => {
    window.removeEventListener('click', handleClickOutside, { capture: true });
    window.removeEventListener('contextmenu', handleClickOutside);
});
</script>

<style scoped>
.context-menu {
    position: fixed;
    z-index: 100010 !important;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    box-shadow: var(--shadow-xl);
    border-radius: var(--border-radius-md);
    padding: 4px;
    min-width: 220px;
    max-width: 280px;
    animation: fadeIn 0.1s ease-out;
}

.menu-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 12px;
    font-size: 11px;
    font-weight: 600;
    color: var(--builder-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: var(--builder-bg-secondary);
    border-radius: 4px;
    margin-bottom: 2px;
}

.menu-type {
    font-size: 9px;
    opacity: 0.7;
    background: var(--builder-border);
    padding: 1px 4px;
    border-radius: 2px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    font-size: 13px;
    color: var(--builder-text-primary);
    cursor: pointer;
    border-radius: 4px;
    transition: background 0.1s;
}

.menu-item:hover:not(.menu-item--disabled) {
    background: var(--builder-bg-tertiary);
    color: var(--builder-accent);
}

.menu-item--disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.menu-item--danger:hover:not(.menu-item--disabled) {
    background: rgba(239, 68, 68, 0.1);
    color: var(--builder-error);
}

.menu-divider {
    height: 1px;
    background: var(--builder-border);
    margin: 4px 0;
}

.shortcut {
    margin-left: auto;
    font-size: 10px;
    color: var(--builder-text-muted);
    opacity: 0.7;
}

.submenu-arrow {
    margin-left: auto;
    opacity: 0.5;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>

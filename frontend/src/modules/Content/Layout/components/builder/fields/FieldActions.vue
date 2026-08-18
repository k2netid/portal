<template>
  <div class="field-actions" :class="{ 'is-active': showMenu }">
    <!-- Reset Field Button -->
    <div v-if="showReset" class="action-icon" :title="$t('builder.fields.actions.reset')" @click="$emit('reset')">
       <RotateCcw :size="14" />
    </div>

    <!-- Info Icon (Question Mark) -->
    <div v-if="showInfo" class="action-icon" :class="{ 'is-active': showInfoPanel }" :title="$t('builder.fields.actions.info')" @click="toggleInfo">
        <HelpCircle :size="14" />
    </div>

    <!-- Copy/Duplicate Icon -->
    <div v-if="showDuplicate" class="action-icon" :title="$t('builder.items.duplicate')" @click="$emit('duplicate')">
        <Copy :size="14" />
    </div>

    <!-- Dynamic Data -->
    <div v-if="showDynamicData" class="action-icon" :title="$t('builder.fields.actions.dynamicData')" @click.stop="$emit('select-dynamic-data', $event.target)">
        <Database :size="14" />
    </div>

    <!-- Responsive Editor Button (Stacked Squares) - Desktop only -->
    <div v-if="showResponsive && activeDevice === 'desktop'" class="action-icon" :title="$t('builder.fields.actions.responsive')" @click="$emit('responsive')">
       <Layers :size="14" />
    </div>

    <!-- Active Device Indicator -->
    <div 
        v-if="responsive && activeDevice && activeDevice !== 'desktop'" 
        class="action-icon active-device-indicator" 
        :title="$t('builder.breakpoints.' + activeDevice)"
        @click="$emit('responsive')"
    >
        <component :is="getDeviceIcon(activeDevice)" :size="12" />
    </div>

    <!-- More Options -->
    <div v-if="showContextMenu" class="action-icon relative">
         <MoreVertical 
           ref="contextMenuIconRef"
           :size="14" 
           class="cursor-pointer" 
           :class="{ 'text-accent': showMenu }"
           @click="toggleMenu" 
         />
         
         <Teleport to="body">
            <div v-if="showMenu" class="field-menu context-menu" v-click-outside="closeMenu" :style="dropdownStyle">
                <div class="menu-item" @click="copyAttributes">{{ $t('builder.fields.actions.copyAttributes', { label: label }) }}</div>
                <div class="menu-item disabled" @click="extendAttributes">{{ $t('builder.fields.actions.extendAttributes', { label: label }) }}</div>
                <div class="menu-divider"></div>
                <div class="menu-item" @click="resetField">{{ $t('builder.fields.actions.resetAttributes', { label: label }) }}</div>
                <div class="menu-divider"></div>
                <div class="menu-item disabled" @click="findReplace">{{ $t('builder.fields.actions.findReplace') }}</div>
            </div>
         </Teleport>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, inject } from 'vue'
import type { BuilderInstance } from '@/types/builder'
import Smartphone from 'lucide-vue-next/dist/esm/icons/smartphone.js';
import Database from 'lucide-vue-next/dist/esm/icons/database.js';
import MoreVertical from 'lucide-vue-next/dist/esm/icons/ellipsis-vertical.js';
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js';
import HelpCircle from 'lucide-vue-next/dist/esm/icons/circle-question-mark.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';
import Copy from 'lucide-vue-next/dist/esm/icons/copy.js';
import Monitor from 'lucide-vue-next/dist/esm/icons/monitor.js';
import Tablet from 'lucide-vue-next/dist/esm/icons/tablet.js';
import MousePointer from 'lucide-vue-next/dist/esm/icons/mouse-pointer.js';

const builder = inject<BuilderInstance>('builder')!

defineProps<{
  label: string;
  responsive?: boolean;
  showResponsive?: boolean;
  activeDevice?: string;
  showReset?: boolean;
  showDuplicate?: boolean;
  showDynamicData?: boolean;
  showContextMenu?: boolean;
  showInfo?: boolean;
  showPresets?: boolean;
  infoContent?: string;
}>()

const getDeviceIcon = (device: string) => {
    switch (device) {
        case 'tablet': return Tablet
        case 'mobile': return Smartphone
        case 'hover': return MousePointer
        default: return Monitor
    }
}

const emit = defineEmits(['reset', 'responsive', 'select-dynamic-data', 'duplicate', 'toggle-info', 'reset-field', 'assign-preset'])

const showMenu = ref(false)
const showInfoPanel = ref(false)
const contextMenuIconRef = ref<{ $el?: HTMLElement } | null>(null)
const dropdownStyle = ref<Record<string, string>>({})

const toggleInfo = (e: Event) => {
    e.stopPropagation()
    showInfoPanel.value = !showInfoPanel.value
    emit('toggle-info', showInfoPanel.value)
}

const updateDropdownPosition = (anchorEl: HTMLElement | null) => {
    if (!anchorEl) return
    const rect = anchorEl.getBoundingClientRect()
    dropdownStyle.value = {
        position: 'fixed',
        top: `${rect.bottom + 4}px`,
        right: `${window.innerWidth - rect.right}px`
    }
}

const vClickOutside = {
  mounted(el: HTMLElement & { clickOutsideEvent?: (event: Event) => void }, binding: { value: (e: Event, el: HTMLElement) => void }) {
    el.clickOutsideEvent = function(event: Event) {
      if (!(el === event.target || el.contains(event.target as Node))) {
        binding.value(event, el);
      }
    };
    document.body.addEventListener('click', el.clickOutsideEvent);
  },
  unmounted(el: HTMLElement & { clickOutsideEvent?: (event: Event) => void }) {
    if (el.clickOutsideEvent) {
        document.body.removeEventListener('click', el.clickOutsideEvent);
    }
  }
}

const closeMenu = () => {
    showMenu.value = false
}

const toggleMenu = (e: Event) => {
    e.stopPropagation()
    showMenu.value = !showMenu.value

    if (showMenu.value) {
        const el = contextMenuIconRef.value?.$el || (contextMenuIconRef.value as unknown as HTMLElement)
        updateDropdownPosition(el instanceof HTMLElement ? el : null)
    }
}

const copyAttributes = () => {
    // Standardize: Link "Copy Attributes" to builder's copyStyles if a module is selected
    if (builder && builder.selectedModule.value) {
        builder.copyStyles(builder.selectedModule.value.id)
    }
    closeMenu()
}

const extendAttributes = () => {
    // TODO(agentic): [FEATURE] Implement extend attributes (styling inheritance)
    closeMenu()
}

const resetField = () => {
    emit('reset-field')
    closeMenu()
}

const findReplace = () => {
    // TODO(agentic): [FEATURE] Implement global find & replace for field values
    closeMenu()
}
</script>

<style scoped>
.field-actions {
  display: flex;
  align-items: center;
  gap: 2px;
  flex-shrink: 0;
}

.field-actions:hover,
.field-actions.is-active {
    opacity: 1;
}

.action-icon {
    color: var(--builder-text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2px;
    transition: color 0.2s;
}

.action-icon:hover,
.action-icon.is-active {
    color: var(--builder-accent);
}

.action-icon.is-active svg {
    color: var(--builder-accent);
}

.action-icon .text-accent {
    color: var(--builder-accent) !important;
}

.active-device-indicator {
    color: var(--builder-accent);
    background-color: rgba(32, 89, 234, 0.1);
    border-radius: 4px;
}

/* Dropdown Menu */
.field-menu {
    position: absolute;
    top: 100%;
    right: 0;
    width: 240px;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    box-shadow: var(--shadow-lg);
    z-index: 60;
    padding: 4px 0;
}

.context-menu {
    width: 280px;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    box-shadow: var(--shadow-xl);
    z-index: 100001 !important;
}

.menu-item {
    padding: 8px 12px;
    font-size: 12px;
    color: var(--builder-text-primary);
    cursor: pointer;
    transition: background-color 0.15s;
}

.menu-item:hover {
    background-color: var(--builder-bg-secondary);
}

.menu-item.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.menu-divider {
    height: 1px;
    background-color: var(--builder-border);
    margin: 4px 0;
}
</style>

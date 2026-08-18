<template>
  <aside class="right-panel">
    <!-- Header -->
    <header class="panel-header">
      <!-- Breadcrumb -->
      <div v-if="module && activePanel !== 'theme'" class="panel-breadcrumb">
        <!-- Collapse Button (Before Breadcrumb) -->
        <IconButton 
          :icon="ChevronsRight" 
          class="panel-collapse-btn"
          @click="$emit('close')" 
          :title="t('builder.rightPanel.collapse')" 
        />
        <template v-for="(item, index) in modulePath" :key="item.id">
          <button 
            class="breadcrumb-item"
            @click="selectModule(item.id)"
          >
            {{ getModuleTitle(item.type) }}
          </button>
          <span v-if="Number(index) < modulePath.length - 1" class="breadcrumb-separator">›</span>
        </template>
      </div>
      
      <!-- Title Row -->
      <div class="panel-title-row">
        <IconButton 
          v-if="modulePath.length > 1 && activePanel !== 'theme'" 
          :icon="ArrowLeft" 
          size="md" 
          @click="goBack" 
          :title="t('builder.rightPanel.back')"
        />
        <h3 class="panel-title">{{ activePanel === 'theme' ? t('builder.sidebars.theme') : moduleTitle }}</h3>
        
        <!-- New: Presets Dropdown (Moved to Header) -->
        <DesignPresetsSelector 
          v-if="module && activePanel !== 'theme'"
          style="margin-left: auto;"
          :type="module.type"
          @action="handlePresetAction"
        />
      </div>
    </header>
    
    <!-- Tabs -->
    <div v-if="module && activePanel !== 'theme'" class="panel-tabs">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        class="panel-tab"
        :class="{ 'panel-tab--active': activeTab === tab.id }"
        @click="activeTab = tab.id"
      >
        {{ t('builder.tabs.' + tab.id) }}
      </button>
      
      <div class="panel-tabs-actions">
<!-- Responsive Breakpoint Dropdown -->

        <div class="responsive-dropdown-wrapper">
          <BaseDropdown align="right" :width="160">
            <template #trigger="{ open }">
              <IconButton 
                :icon="currentBreakpointIcon" 
                :active="open" 
                :title="t('builder.rightPanel.responsiveMode')" 
              />
            </template>
            
            <template #default="{ close }">
              <button 
                v-for="bp in breakpoints" 
                :key="bp.id"
                class="dropdown-item"
                :class="{ 'active': currentBreakpoint === bp.id }"
                @click="selectBreakpoint(bp); close()"
              >
                <component :is="bp.icon" :size="16" />
                {{ t('builder.breakpoints.' + bp.id) }}
              </button>
              <BaseDivider :margin="4" />
              <button class="dropdown-item" @click="openSettings($event); close()">
                <Settings :size="16" />
                {{ t('builder.rightPanel.settings') }}
              </button>
            </template>
          </BaseDropdown>
        </div>
</div>
    </div>
    
    <!-- Search -->
    <div v-if="module && activePanel !== 'theme'" class="panel-search">
      <BaseInput 
        v-model="searchQuery"
        :placeholder="t('builder.rightPanel.searchPlaceholder')"
        @clear="searchQuery = ''"
      >
        <template #prefix>
          <Search :size="14" />
        </template>
        <template #suffix v-if="searchQuery">
          <IconButton :icon="X" size="sm" @click="searchQuery = ''" />
        </template>
      </BaseInput>
    </div>
    
    <!-- Content -->
    <div class="panel-content">
      <ThemeSettingsPanel 
        v-if="activePanel === 'theme'"
      />
      <SettingsPanel 
        v-else-if="module"
        :module="module"
        :active-tab="activeTab"
        :search-query="searchQuery"
        :device="currentBreakpoint"
      />
    </div>
  </aside>
  
  <!-- Responsive Breakpoints Popover -->
  <ResponsiveBreakpointsModal 
    v-if="showBreakpointsModal"
    :is-open="showBreakpointsModal"
    :trigger-rect="breakpointsTriggerRect"
    @close="showBreakpointsModal = false"
    @save="handleBreakpointsSave"
  />
</template>

<script setup lang="ts">
import { ref, computed, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';
import Smartphone from 'lucide-vue-next/dist/esm/icons/smartphone.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Monitor from 'lucide-vue-next/dist/esm/icons/monitor.js';
import Tablet from 'lucide-vue-next/dist/esm/icons/tablet.js';
import MousePointer from 'lucide-vue-next/dist/esm/icons/mouse-pointer.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import ChevronsRight from 'lucide-vue-next/dist/esm/icons/chevrons-right.js';
import { SETTINGS_TABS } from '@/components/builder/core/constants'
import ModuleRegistry from '@/components/builder/core/ModuleRegistry'
import SettingsPanel from '@/components/builder/settings/SettingsPanel.vue'
import ResponsiveBreakpointsModal from '@/components/builder/modals/ResponsiveBreakpointsModal.vue'
import { IconButton, BaseInput, BaseDropdown, BaseDivider } from '@/components/builder/ui'
import DesignPresetsSelector from '@/components/builder/settings/DesignPresetsSelector.vue'
import ThemeSettingsPanel from '@/components/builder/layout/panels/ThemeSettingsPanel.vue'
import type { BuilderInstance, BlockInstance, BuilderPreset } from '../../../types/builder'

const { t, te } = useI18n()

// Breakpoints config
const breakpoints = [
  { id: 'desktop', label: 'Desktop', icon: Monitor },
  { id: 'tablet', label: 'Tablet', icon: Tablet },
  { id: 'mobile', label: 'Phone', icon: Smartphone },
  { id: 'hover', label: 'Hover', icon: MousePointer }
]

// Props
const props = defineProps<{
  module: BlockInstance | null
}>()

// Emits
defineEmits<{
  (e: 'close'): void
}>()

// Inject
const builder = inject<BuilderInstance>('builder')!

// State
const activePanel = computed(() => builder?.activePanel?.value)

// State
const activeTab = ref('content')
const searchQuery = ref('')
const showBreakpointsModal = ref(false)
const breakpointsTriggerRect = ref<DOMRect | null>(null)

// Computed
const modulePath = computed(() => builder?.modulePath?.value || [])

const moduleTitle = computed(() => {
  if (!props.module) return ''
  
  const type = props.module.type
  if (te(`builder.modules.${type}`)) {
    return t(`builder.modules.${type}`)
  }

  const def = ModuleRegistry.get(type)
  return def?.title || type
})

const currentBreakpoint = computed(() => {
  return builder?.device?.value || 'desktop'
})

const currentBreakpointIcon = computed(() => {
  const bp = breakpoints.find(b => b.id === currentBreakpoint.value)
  return bp?.icon || Monitor
})

const tabs = Object.values(SETTINGS_TABS)

// Methods
const getModuleTitle = (type: string) => {
  if (te(`builder.modules.${type}`)) {
    return t(`builder.modules.${type}`)
  }
  const def = ModuleRegistry.get(type)
  return def?.title || type
}

const selectModule = (id: string) => {
  builder?.selectModule(id)
}

const goBack = () => {
  const path = modulePath.value
  if (path.length > 1) {
    const parentModule = path[path.length - 2]
    selectModule(parentModule.id)
  }
}

const selectBreakpoint = (bp: { id: string }) => {
  if (builder?.setDeviceMode) {
    builder.setDeviceMode(bp.id as 'desktop' | 'tablet' | 'mobile')
  }
}

const openSettings = (event: MouseEvent) => {
  const trigger = (event?.target as HTMLElement)?.closest('.dropdown-item') || (event?.target as HTMLElement)
  if (trigger) {
    breakpointsTriggerRect.value = trigger.getBoundingClientRect()
  }
  showBreakpointsModal.value = true
}

const handleBreakpointsSave = (_breakpointsData: unknown) => {
  // Logic to save breakpoints settings
}

const handlePresetAction = (payload: { type: string, data: unknown }) => {
  const { type, data } = payload
  if (type === 'addNew' || type === 'newFromCurrent') {
    if (props.module) {
      builder?.openSavePresetModal?.(props.module.id)
    }
  } else if (type === 'apply' && data) {
    if (props.module) {
      builder?.applyPreset(props.module.id, data as BuilderPreset)
    }
  }
}
</script>

<style scoped>
.right-panel {
  display: flex;
  flex-direction: column;
  width: var(--panel-width);
  background: var(--builder-bg-primary);
  border-left: 1px solid var(--builder-border-layout);
  border-bottom: 1px solid var(--builder-border-layout);
}

/* Header */
.panel-header {
  padding: 16px 20px;
  border-bottom: 1px solid var(--builder-border);
  background: var(--builder-bg-primary);
}

.panel-breadcrumb {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 6px;
  margin-bottom: 12px;
}

.breadcrumb-item {
  padding: 0;
  background: none;
  border: none;
  color: var(--builder-text-muted);
  font-size: 11px;
  cursor: pointer;
  font-weight: 500;
  transition: color 0.2s;
}

.breadcrumb-item:hover {
  color: var(--builder-text-primary);
}

.breadcrumb-separator {
  color: var(--builder-text-muted);
  font-size: 10px;
  opacity: 0.6;
}


.panel-collapse-btn {
  background: transparent;
  border: none;
  color: var(--builder-text-muted);
  width: 24px !important;
  height: 24px !important;
  flex-shrink: 0;
  margin-right: 6px;
  margin-left: -4px; /* Flush against left edge */
  padding: 0;
}

.panel-collapse-btn:hover {
  color: var(--builder-accent);
  background: transparent;
}

.panel-title-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.panel-title {
  flex: 1;
  margin: 0;
  font-size: 15px;
  font-weight: 700;
  color: var(--builder-text-primary);
  letter-spacing: -0.01em;
}

/* Tabs */
.panel-tabs {
  display: flex;
  align-items: center;
  padding: 0 20px;
  border-bottom: 1px solid var(--builder-border);
  background: var(--builder-bg-primary);
  gap: 20px;
}

.panel-tab {
  padding: 14px 0;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  color: var(--builder-text-secondary);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
  top: 1px; /* Sit on line */
}

.panel-tab:hover {
  color: var(--builder-text-primary);
}

.panel-tab--active {
  color: var(--builder-accent);
  border-bottom-color: var(--builder-accent);
}

.panel-tabs-actions {
  display: flex;
  margin-left: auto;
  gap: 4px;
}

/* Search */
.panel-search {
  display: flex;
  align-items: center;
  margin: 16px 20px;
}

/* Content */
.panel-content {
  flex: 1;
  overflow-y: auto;
  background: var(--builder-bg-primary);
}

/* Responsive Dropdown */
.responsive-dropdown-wrapper {
  position: relative;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 8px 12px;
  background: none;
  border: none;
  color: var(--builder-text-secondary);
  font-size: 13px;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.15s;
  text-align: left;
}

.dropdown-item:hover {
  background: var(--builder-bg-tertiary);
  color: var(--builder-text-primary);
}

.dropdown-item.active {
  background: var(--builder-accent);
  color: white;
}

@media (max-width: 768px) {
  .right-panel {
    position: fixed;
    top: var(--toolbar-height);
    bottom: 0;
    right: 0;
    z-index: 1000;
    width: 90% !important;
    max-width: 360px;
    box-shadow: var(--shadow-xl);
    border-left: 1px solid var(--builder-border);
  }
}
</style>

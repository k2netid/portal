<template>
  <aside 
    class="left-panel" 
    :class="{ 'left-panel--visible': visible }"
    :style="{ width: visible ? currentWidth : '0px' }"
  >
    <div class="panel-inner" :style="{ width: currentWidth }">
      <header class="panel-header">
        <h3 class="panel-title">{{ title }}</h3>

        <!-- View Mode Toggles (Only for Layers) -->
        <div v-if="activePanel === 'layers'" class="panel-actions">
            <button 
                class="panel-action-btn" 
                :class="{ 'panel-action-btn--active': builder.gridViewMode.value }"
                @click="builder.gridViewMode.value = !builder.gridViewMode.value"
                :title="t('builder.toolbar.gridView', 'Grid View')"
            >
                <component :is="icons.LayoutGrid" :size="16" />
            </button>
            <button 
                class="panel-action-btn" 
                :class="{ 'panel-action-btn--active': builder.wireframeMode.value }"
                @click="builder.wireframeMode.value = !builder.wireframeMode.value"
                :title="t('builder.toolbar.wireframe', 'Wireframe Mode')"
            >
                <component :is="icons.Eye" :size="16" />
            </button>
        </div>
        <button class="panel-close" @click="$emit('close')">
          <component :is="icons.ChevronsLeft" :size="20" />
        </button>
      </header>
      
      <div class="panel-content">
        <!-- Layers Panel -->
        <template v-if="activePanel === 'layers'">
          <div class="layers-controls">
            <div class="search-layout">
              <component :is="icons.Search" :size="14" class="search-icon" />
              <input 
                type="text" 
                v-model="searchTerm" 
                :placeholder="t('builder.panels.layers.searchPlaceholder', 'Search Layout')"
                class="search-input"
              />
              <component :is="icons.Filter" :size="14" class="filter-icon" />
            </div>
            <button class="close-all-btn" @click="collapseAll">
              {{ t('builder.panels.layers.closeAll', 'Close All') }}
            </button>
          </div>

          <div class="layers-tree-container">
            <div v-if="blocks.length === 0" class="empty-state">
            <p class="mb-2 text-muted text-center text-xs">{{ t('builder.panels.layers.empty') }}</p>
            <button class="btn-link text-xs text-accent flex items-center justify-center gap-1 mx-auto" @click="builder.insertModule('section')">
              <component :is="icons.Plus || 'span'" :size="12" />
              {{ t('builder.actions.addSection', 'Add Section') }}
            </button>
          </div>
            <LayersTree 
              v-else
              :blocks="blocks"
              :selected-id="selectedModuleId"
              :search-term="searchTerm"
              :collapse-signal="collapseSignal"
              @select="selectModule"
            />
          </div>
        </template>

        <!-- History Panel -->
        <HistoryPanel v-else-if="activePanel === 'history'" />

        <!-- Page Settings Panel -->
        <PageSettingsPanel v-else-if="activePanel === 'settings'" />

        <!-- Pages Panel -->
        <PagesPanel v-else-if="activePanel === 'pages'" />

        <!-- Layouts Panel -->
        <LayoutsPanel v-else-if="activePanel === 'layouts'" />

        <!-- Presets Panel -->
        <PresetsPanel v-else-if="activePanel === 'presets'" />

        <!-- Portability Panel -->
        <PortabilityPanel v-else-if="activePanel === 'portability'" />

        <!-- Help Panel -->
        <HelpPanel v-else-if="activePanel === 'help'" />
        
        <!-- Preferences Panel -->
        <PreferencesPanel v-else-if="activePanel === 'preferences'" />
        
        <!-- Global Variables Panel -->
        <GlobalVariablesPanel v-else-if="activePanel === 'global_variables'" />
        
        <!-- Theme Panel -->
        <ThemeListPanel v-else-if="activePanel === 'theme'" />

        <!-- Templates Panel -->
        <TemplatesPanel v-else-if="activePanel === 'templates'" />
        
        <!-- Fallback -->
        <div v-else class="panel-placeholder">
          <component :is="icons.Construction" :size="32" />
          <p>{{ title }} {{ t('builder.panels.comingSoon') }}</p>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { ref, computed, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import Construction from 'lucide-vue-next/dist/esm/icons/construction.js';
import ChevronsLeft from 'lucide-vue-next/dist/esm/icons/chevrons-left.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import LayoutGrid from 'lucide-vue-next/dist/esm/icons/layout-grid.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Filter from 'lucide-vue-next/dist/esm/icons/list-filter.js';
import MoreVertical from 'lucide-vue-next/dist/esm/icons/ellipsis-vertical.js';import { SIDEBAR_PANELS } from '../core/constants'
import LayersTree from './LayersTree.vue'
import HistoryPanel from './panels/HistoryPanel.vue'
import PageSettingsPanel from './panels/PageSettingsPanel.vue'
import PagesPanel from './panels/PagesPanel.vue'
import LayoutsPanel from './panels/LayoutsPanel.vue'
import PresetsPanel from './panels/PresetsPanel.vue'
import PortabilityPanel from './panels/PortabilityPanel.vue'
import HelpPanel from './panels/HelpPanel.vue'
import PreferencesPanel from './panels/PreferencesPanel.vue'
import GlobalVariablesPanel from './panels/GlobalVariablesPanel.vue'
import ThemeListPanel from './panels/ThemeListPanel.vue'
import TemplatesPanel from './panels/TemplatesPanel.vue'
import type { BuilderInstance, BlockInstance } from '../../../types/builder'

import type { Component } from 'vue';

const icons: Record<string, Component> = { X, Construction, ChevronsLeft, Plus, LayoutGrid, Eye, Search, Filter, MoreVertical }

interface Props {
  activePanel: string;
  visible?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  visible: false
})

defineEmits<{
  (e: 'close'): void;
}>()

const builder = inject<BuilderInstance>('builder')!
const { t } = useI18n()
const blocks = computed<BlockInstance[]>(() => builder?.blocks.value || [])
const selectedModuleId = computed(() => builder?.selectedModuleId.value)

const searchTerm = ref('')
const collapseSignal = ref(0)

const collapseAll = () => {
    collapseSignal.value++
}

const title = computed(() => {
  if (props.activePanel === 'preferences') return t('builder.toolbar.preferences')
  const panel = SIDEBAR_PANELS.find(p => p.id === props.activePanel)
  return panel ? t('builder.sidebars.' + panel.id) : t('builder.panels.fallbackTitle')
})

// Dynamic width based on active panel
const currentWidth = computed(() => {
    if (props.activePanel === 'global_variables') return '500px'
    if (props.activePanel === 'theme' || props.activePanel === 'templates') return '360px'
    return '300px'
})

const selectModule = (id: string) => {
  builder?.selectModule(id)
}
</script>

<style scoped>
.left-panel {
  width: 0;
  height: 100%;
  border-right: 1px solid var(--builder-border-layout);
  border-bottom: 1px solid var(--builder-border-layout);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
  opacity: 0;
  flex-shrink: 0;
}

.left-panel--visible {
  opacity: 1;
  border-right-width: 1px;
}

.panel-inner {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-md);
  border-bottom: 1px solid var(--builder-border);
}

.panel-title {
  margin: 0;
  font-size: var(--font-size-md);
  font-weight: 600;
  color: var(--builder-text-primary);
}

.panel-actions {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-left: auto;
    margin-right: 8px;
}

.panel-action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: 1px solid transparent;
    border-radius: 4px;
    background: transparent;
    color: var(--builder-text-muted);
    cursor: pointer;
    transition: all 0.2s;
}

.panel-action-btn:hover {
    background: var(--builder-bg-secondary);
    color: var(--builder-text-primary);
}

.panel-action-btn--active {
    background: var(--builder-accent-light);
    color: var(--builder-accent);
    border-color: var(--builder-accent);
}

.panel-close {
  background: none;
  border: none;
  color: var(--builder-text-muted);
  cursor: pointer;
  padding: 4px;
}

.panel-close:hover {
  color: var(--builder-text-primary);
}

.panel-content {
  flex: 1;
  min-height: 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.panel-content > * {
  flex: 1;
  min-height: 0;
  display: flex;
  flex-direction: column;
}

.layers-controls {
    flex: none;
    padding: 8px 12px;
    border-bottom: 1px solid var(--builder-border);
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.layers-tree-container {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: var(--spacing-sm);
}

.search-layout {
    display: flex;
    align-items: center;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    padding: 0 8px;
    height: 32px;
}

.search-icon {
    color: var(--builder-text-muted);
    margin-right: 8px;
}

.filter-icon {
    color: var(--builder-text-muted);
    cursor: pointer;
}

.search-input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 12px;
    color: var(--builder-text-primary);
    outline: none;
    padding: 0;
}

.close-all-btn {
    align-self: flex-start;
    background: none;
    border: none;
    color: var(--builder-text-primary);
    font-size: 11px;
    font-weight: 500;
    cursor: pointer;
    padding: 0;
}

.close-all-btn:hover {
    opacity: 0.8;
}

.empty-state {
  text-align: center;
  color: var(--builder-text-muted);
  font-size: var(--font-size-sm);
  padding: var(--spacing-xl);
}

.panel-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: var(--builder-text-muted);
  gap: var(--spacing-md);
}

@media (max-width: 768px) {
  .left-panel--visible {
    position: fixed;
    top: var(--toolbar-height); 
    bottom: 0;
    left: 0;
    z-index: 900; 
    width: 85% !important; 
    max-width: 320px;
    background: var(--builder-bg-primary) !important;
    box-shadow: var(--shadow-xl);
    border-right: 1px solid var(--builder-border);
  }
  
  .panel-inner {
    width: 100% !important;
  }
}
</style>

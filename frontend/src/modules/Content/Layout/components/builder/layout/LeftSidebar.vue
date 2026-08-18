<template>
  <aside class="left-sidebar">
    <nav class="sidebar-nav">
      <button
        v-for="panel in filteredPanels"
        :key="panel.id"
        class="sidebar-btn"
        :class="{ 'sidebar-btn--active': activePanel === panel.id }"
        @click="$emit('change-panel', panel.id)"
        :title="t('builder.sidebars.' + panel.id)"
      >
        <component :is="getIcon(panel.icon)" :size="20" />
      </button>
    </nav>
    
    <div class="sidebar-bottom">
      <button 
        class="sidebar-btn" 
        :class="{ 'sidebar-btn--active': activePanel === 'preferences' }"
        @click="$emit('change-panel', 'preferences')"
        :title="t('builder.toolbar.preferences')"
      >
        <component :is="icons.Settings2" :size="20" />
      </button>
    </div>
  </aside>
</template>

<script setup lang="ts">
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import Layout from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import Palette from 'lucide-vue-next/dist/esm/icons/palette.js';
import Sparkles from 'lucide-vue-next/dist/esm/icons/sparkles.js';
import Share2 from 'lucide-vue-next/dist/esm/icons/share-2.js';
import CircleQuestion from 'lucide-vue-next/dist/esm/icons/circle-question-mark.js';
import Settings2 from 'lucide-vue-next/dist/esm/icons/settings-2.js';
import Database from 'lucide-vue-next/dist/esm/icons/database.js';
import Grid from 'lucide-vue-next/dist/esm/icons/grid-2x2.js';
import { inject, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { SIDEBAR_PANELS } from '@/components/builder/core/constants'
import type { BuilderInstance } from '@/types/builder'

// Props
interface Props {
  activePanel?: string;
}

withDefaults(defineProps<Props>(), {
  activePanel: 'layers'
})

// Emits
defineEmits<{
  (e: 'change-panel', panelId: string): void;
}>()

const { t } = useI18n()

import type { Component } from 'vue';

// Icons map
const icons: Record<string, Component> = {
  Layers, FileText, Clock, Layout, Settings, Palette,
  Sparkles, Share2, HelpCircle: CircleQuestion, Settings2, Database, Grid
}

// Inject builder state
const builder = inject<BuilderInstance>('builder')!
const panels = SIDEBAR_PANELS

const filteredPanels = computed(() => {
  if (builder?.mode.value === 'page') {
    // Hidden panels in page mode (focused editing)
    const hidden = ['pages', 'portability', 'theme', 'global_variables', 'templates']
    return panels.filter(p => !hidden.includes(p.id))
  }
  return panels
})

const getIcon = (iconName: string) => {
  return icons[iconName] || icons.Layers
}
</script>

<style scoped>
.left-sidebar {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  width: var(--sidebar-width);
  background: var(--builder-bg-sidebar);
  border-right: 1px solid var(--builder-border);
  padding: var(--spacing-sm) 0;
  flex-shrink: 0;
  z-index: 10;
  /* Prevent flash on theme switch */
  transition: background-color 0.3s ease;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--spacing-xs);
}

.sidebar-bottom {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--spacing-xs);
  padding-bottom: var(--spacing-sm);
}

.sidebar-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  padding: 0;
  background: transparent;
  border: none;
  border-radius: var(--border-radius-sm);
  color: var(--builder-text-muted);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.sidebar-btn:hover {
  background: var(--builder-bg-tertiary);
  color: var(--builder-text-secondary);
}

.sidebar-btn--active {
  background: var(--builder-accent);
  color: white;
}

.sidebar-btn--active:hover {
  background: var(--builder-accent);
  color: white;
}
</style>

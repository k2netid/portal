<template>
  <BaseModal
    :is-open="true"
    :title="t('builder.insertModal.title')"
    :width="700"
    draggable
    placement="center"
    @close="$emit('close')"
  >
    <template #header>
      <Tabs v-model="activeTab" class="modal-tabs-container" @update:model-value="handleTabChange">
        <TabsList class="modal-tabs-list">
          <TabsTrigger 
            v-for="tab in tabs" 
            :key="tab.id" 
            :value="tab.id"
          >
            {{ te('builder.insertModal.tabs.' + tab.id) ? t('builder.insertModal.tabs.' + tab.id) : tab.label }}
          </TabsTrigger>
        </TabsList>
      </Tabs>
    </template>

    <div class="modal-body-content">
      <!-- Search (Module Tab Only) -->
      <div v-if="activeTab === 'module' || activeTab === 'presets' || activeTab === 'library'" class="modal-search-wrapper">
        <BaseInput 
          v-model="searchQuery"
          :placeholder="$t('builder.insertModal.searchPlaceholder')"
          autofocus
        >
          <template #prefix>
             <Search :size="16" />
          </template>
        </BaseInput>
      </div>
      
      <!-- Content -->
      <div class="modal-content">
        <!-- New Module Tab -->
        <div v-if="activeTab === 'module'" class="module-content">
          <div v-for="(categoryModules, category) in groupedModules" :key="category" class="module-group">
            <h4 class="group-title"><span>{{ te('builder.categories.' + category.toLowerCase()) ? $t('builder.categories.' + category.toLowerCase()) : category }}</span></h4>
            <div class="module-grid">
              <button
                v-for="module in categoryModules"
                :key="module.name"
                class="module-card"
                @click="module.name && selectModule(module.name!)"
              >
                <div class="module-icon">
                  <component :size="24" :is="getIcon(module.icon)" />
                </div>
                <span class="module-name">{{ (module.name && te('builder.modules.' + module.name)) ? $t('builder.modules.' + module.name) : module.title }}</span>
              </button>
            </div>
          </div>
          
          <div v-if="Object.keys(groupedModules).length === 0" class="no-results">
            {{ t('builder.insertModal.noResults', { query: searchQuery }) }}
          </div>
        </div>
        
        <!-- New Row Tab -->
        <div v-if="activeTab === 'row'" class="layout-content">
<!-- Unified Layout Groups Loop -->
          <div v-for="group in allGroups" :key="group.id" class="layout-group">
            <h4 class="group-title">
              <span :class="['category-badge', `category-badge--${group.type}`]">
                {{ group.type === 'flex' ? t('builder.fields.layoutTypes.flex') : t('builder.fields.layoutTypes.grid') }}
              </span>
              {{ te('builder.insertModal.layoutGroups.' + group.id) ? t('builder.insertModal.layoutGroups.' + group.id) : group.title }}
            </h4>
            
            <div class="layout-grid">
              <button 
                v-for="(preset, i) in group.items" 
                :key="group.id + '-' + i"
                class="layout-card"
                @click="selectLayout(preset)"
                :title="preset.name"
              >
                <!-- Specialty/Nested Preview -->
                <div v-if="preset.cols" class="layout-preview specialty-preview">
                  <div v-for="(col, cIdx) in preset.cols" :key="cIdx" class="preview-specialty-col" :style="{ flex: col.width }">
                    <template v-if="col.rows">
                       <div v-for="(r, rIdx) in col.rows" :key="rIdx" class="preview-specialty-row">
                          <div v-for="(w, wIdx) in r.widths" :key="wIdx" class="preview-col" :style="{ flex: w }"></div>
                       </div>
                    </template>
                    <div v-else class="preview-col full-height"></div>
                  </div>
                </div>

                <!-- Multi-Row Preview -->
                <div v-else-if="preset.rows" class="layout-preview-stacked">
                  <div v-for="(row, rIdx) in preset.rows" :key="rIdx" class="preview-row">
                      <div 
                        v-for="(width, cIdx) in row.widths" 
                        :key="cIdx" 
                        class="preview-col"
                        :style="{ flex: width }"
                      ></div>
                  </div>
                </div>

                <!-- Standard/Equal Preview -->
                <div v-else class="layout-preview">
                  <div 
                    v-for="(width, idx) in preset.widths" 
                    :key="idx" 
                    class="preview-col"
                    :style="{ flex: width }"
                  ></div>
                </div>
              </button>
            </div>
          </div>
</div>
        
        <div v-if="activeTab === 'presets'" class="library-content">
          <div v-if="loadingPresets" class="no-results">
            <div class="loading-spinner"></div>
            <p>{{ t('builder.messages.loading') }}</p>
          </div>
          <div v-else-if="filteredPresets.length === 0" class="no-results">
            {{ t('builder.insertModal.noResults', { query: searchQuery }) }}
          </div>
          <div v-else class="module-content">
            <div v-for="(typePresets, type) in groupedPresets" :key="type" class="module-group">
              <h4 class="group-title"><span>{{ type }}</span></h4>
              <div class="module-grid">
                <button
                  v-for="preset in typePresets"
                  :key="preset.id"
                  class="module-card"
                  @click="selectPreset(preset)"
                >
                  <div class="module-icon">
                    <component :size="24" :is="getIcon(ModuleRegistry.get(preset.type)?.icon)" />
                  </div>
                  <span class="module-name">{{ preset.name }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <div v-if="activeTab === 'library'" class="library-content">
          <div class="no-results">
            {{ t('builder.insertModal.libraryEmpty') }}
          </div>
        </div>
      </div>
    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed, inject, onMounted, type Component } from 'vue';
import { useI18n } from 'vue-i18n';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Type from 'lucide-vue-next/dist/esm/icons/type.js';
import Heading from 'lucide-vue-next/dist/esm/icons/heading.js';
import Image from 'lucide-vue-next/dist/esm/icons/image.js';
import MousePointer from 'lucide-vue-next/dist/esm/icons/mouse-pointer.js';
import Video from 'lucide-vue-next/dist/esm/icons/video.js';
import Layout from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import AlignJustify from 'lucide-vue-next/dist/esm/icons/align-horizontal-justify-center.js';
import Square from 'lucide-vue-next/dist/esm/icons/square.js';
import Columns from 'lucide-vue-next/dist/esm/icons/columns-2.js';
import MessageSquare from 'lucide-vue-next/dist/esm/icons/message-square.js';
import Quote from 'lucide-vue-next/dist/esm/icons/quote.js';
import Star from 'lucide-vue-next/dist/esm/icons/star.js';
import Play from 'lucide-vue-next/dist/esm/icons/play.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import AlertCircle from 'lucide-vue-next/dist/esm/icons/circle-alert.js';
import Code from 'lucide-vue-next/dist/esm/icons/code.js';
import Users from 'lucide-vue-next/dist/esm/icons/users.js';
import Circle from 'lucide-vue-next/dist/esm/icons/circle.js';
import List from 'lucide-vue-next/dist/esm/icons/list.js';
import MapPin from 'lucide-vue-next/dist/esm/icons/map-pin.js';
import Music from 'lucide-vue-next/dist/esm/icons/music.js';
import SplitSquareHorizontal from 'lucide-vue-next/dist/esm/icons/split.js';
import Film from 'lucide-vue-next/dist/esm/icons/film.js';
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Phone from 'lucide-vue-next/dist/esm/icons/phone.js';
import Share2 from 'lucide-vue-next/dist/esm/icons/share-2.js';
import Award from 'lucide-vue-next/dist/esm/icons/award.js';
import LayoutGrid from 'lucide-vue-next/dist/esm/icons/layout-grid.js';
import CreditCard from 'lucide-vue-next/dist/esm/icons/credit-card.js';
import Menu from 'lucide-vue-next/dist/esm/icons/menu.js';
import Folder from 'lucide-vue-next/dist/esm/icons/folder.js';
import Box from 'lucide-vue-next/dist/esm/icons/box.js';
import BarChart2 from 'lucide-vue-next/dist/esm/icons/chart-bar.js';
import Newspaper from 'lucide-vue-next/dist/esm/icons/newspaper.js';
import MessageCircle from 'lucide-vue-next/dist/esm/icons/message-circle.js';
import Timer from 'lucide-vue-next/dist/esm/icons/timer.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import Mail from 'lucide-vue-next/dist/esm/icons/mail.js';
import Calendar from 'lucide-vue-next/dist/esm/icons/calendar.js';
import Bookmark from 'lucide-vue-next/dist/esm/icons/bookmark.js';
import User from 'lucide-vue-next/dist/esm/icons/user.js';
import Hash from 'lucide-vue-next/dist/esm/icons/hash.js';
import Percent from 'lucide-vue-next/dist/esm/icons/percent.js';
import Sparkles from 'lucide-vue-next/dist/esm/icons/sparkles.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import SquareCheck from 'lucide-vue-next/dist/esm/icons/square-check.js';
import Disc from 'lucide-vue-next/dist/esm/icons/disc.js';
import TextAlignStart from 'lucide-vue-next/dist/esm/icons/text-align-start.js';
import { BaseModal, BaseInput, Tabs, TabsList, TabsTrigger } from '@/components/builder/ui';
import ModuleRegistry from '@/components/builder/core/ModuleRegistry';
import type { BuilderInstance, BlockInstance, BuilderPreset, ModuleDefinition } from '@/types/builder';

import { 
    equalLayouts, 
    offsetLayouts, 
    flexMultiRowPresets, 
    flexMultiColumnPresets, 
    gridMultiRowPresets,
    masonryPresets,
    sidebarPresets,
    type LayoutPreset
} from '@/components/builder/constants/layouts';


const icons: Record<string, Component> = { 
  X, Search, Type, Heading, Image, MousePointer, Video, Layout,
  AlignJustify, Square, Columns, MessageSquare, Quote, Star,
  Play, Clock, AlertCircle, Code, Users, Circle, List,
  MapPin, Music, SplitSquareHorizontal, Film, Layers, FileText, Phone,
  Share2, Award, LayoutGrid, CreditCard, Menu, Folder,
  Box, BarChart2, Newspaper, MessageCircle, Timer,
  Download, Mail, Calendar, Bookmark, User, Hash, Percent, Sparkles,
  ChevronDown, SquareCheck, Disc, TextAlignStart
};

// Emits
const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'insert', type: string, payload?: unknown): void;
}>();

// Props/Injections
const builder = inject<BuilderInstance>('builder');

// State
const activeTab = ref('module');
const searchQuery = ref('');
const { t, te } = useI18n();

// Tabs
const tabs = [
  { id: 'module', label: 'Module' },
  { id: 'row', label: 'Row' },
  { id: 'presets', label: 'Preset' },
  { id: 'library', label: 'Library' }
];

// Group all presets for a unified loop
const allGroups = computed(() => [
  { id: 'equal', type: 'flex', title: 'Equal Columns', items: equalLayouts },
  { id: 'offset', type: 'flex', title: 'Offset Columns', items: offsetLayouts },
  { id: 'multiRow', type: 'flex', title: 'Multi-Row', items: flexMultiRowPresets },
  { id: 'multiCol', type: 'flex', title: 'Multi-Column', items: flexMultiColumnPresets },
  { id: 'grid-multi-row', type: 'grid', title: 'Multi-Row', items: gridMultiRowPresets },
  { id: 'masonry', type: 'grid', title: 'Masonry', items: masonryPresets },
  { id: 'sidebar', type: 'grid', title: 'Sidebar', items: sidebarPresets }
]);

// Context-aware suggestions
const suggestedModules = computed(() => {
  if (!builder || !builder.insertTargetId.value) return [];
  
  const targetId = builder.insertTargetId.value;
  const blocks = builder.blocks.value;
  
  // Find full path of the insert target
  const targetPath = builder.getModulePath(blocks, targetId);
  
  // Check for specialized contexts (primarily forms for now)
  const isFormContext = targetPath.some((m: BlockInstance) => 
    (m.type && ['contactform', 'login', 'signup', 'newsletter'].includes(m.type)) ||
    (m.type && builder.getModuleDefinition?.(m.type as string)?.category === 'forms')
  );

  if (isFormContext) {
    // Form-related components
    return ModuleRegistry.getAll().filter(m => 
        m.category === 'forms' || 
        (m.name && ['button', 'text', 'heading'].includes(m.name))
    ).filter(m => m.name && !['contactform', 'login', 'signup', 'newsletter'].includes(m.name));
  }

  return [];
});

// Modules
const modules = computed(() => ModuleRegistry.getContentModules());

const groupedModules = computed(() => {
  let filtered = modules.value;
  
  // Filter by search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter((m: ModuleDefinition) => 
      (m.title || '').toLowerCase().includes(query) ||
      (m.name || '').toLowerCase().includes(query)
    );
  }

  // Group by category
  const groups: Record<string, ModuleDefinition[]> = {};
  
  // Add suggested first if no search is active
  if (!searchQuery.value && suggestedModules.value.length > 0) {
    groups[t('builder.categories.suggested')] = suggestedModules.value;
  }

  filtered.forEach((module: ModuleDefinition) => {
     const catKey = (module.category || 'content').toLowerCase();
     const catTitle = te('builder.categories.' + catKey) ? t('builder.categories.' + catKey) : (module.category || 'Content');
     
     if (!groups[catTitle]) {
       groups[catTitle] = [];
     }
     groups[catTitle].push(module);
  });
  
  return groups;
});

// Presets - builder.presets is already a reactive Proxy, not a ref
const loadingPresets = computed(() => builder?.loadingPresets?.value || false);
const presets = computed(() => builder?.presets?.value || []);

const filteredPresets = computed(() => {
  const query = searchQuery.value.toLowerCase();
  return presets.value.filter((p: BuilderPreset) => p.name.toLowerCase().includes(query));
});

const groupedPresets = computed(() => {
  const groups: Record<string, BuilderPreset[]> = {};
  filteredPresets.value.forEach((preset: BuilderPreset) => {
    const type = preset.type.charAt(0).toUpperCase() + preset.type.slice(1);
    if (!groups[type]) groups[type] = [];
    groups[type].push(preset);
  });
  return groups;
});

// Methods
const getIcon = (icon?: string | Component) => {
  if (!icon) return icons.Layout;
  if (typeof icon === 'string') return icons[icon] || icons.Layout;
  return icon;
};

const selectModule = (name?: string) => {
  if (name) emit('insert', name);
};

const selectLayout = (layout: LayoutPreset) => {
  emit('insert', 'row', layout);
};

const selectPreset = (preset: BuilderPreset) => {
  emit('insert', 'preset', preset);
};

const handleTabChange = (tabId: string | number) => {
  activeTab.value = tabId.toString();
  if (activeTab.value === 'presets' && presets.value.length === 0) {
    builder?.fetchPresets();
  }
};

onMounted(() => {
  if (activeTab.value === 'presets' || activeTab.value === 'library') {
    builder?.fetchPresets();
  }
});
</script>

<style scoped>
.modal-tabs-container {
  margin-left: 16px;
}

.modal-tabs-list {
  background: transparent !important;
  border: none !important;
  padding: 0 !important;
  gap: 4px !important;
}

.modal-tabs-list :deep([data-radix-collection-item]) {
  background: transparent !important;
  border: none !important;
  padding: 8px 16px !important;
  font-size: 13px !important;
  font-weight: 500 !important;
  color: var(--builder-text-secondary) !important;
  border-radius: 6px !important;
  transition: all 0.15s ease !important;
}

.modal-tabs-list :deep([data-radix-collection-item]:hover) {
  color: var(--builder-text-primary) !important;
  background: var(--builder-bg-secondary) !important;
}

.modal-tabs-list :deep([data-radix-collection-item][data-state="active"]) {
  color: var(--builder-accent) !important;
  background: rgba(var(--builder-accent-rgb, 32, 89, 234), 0.1) !important;
  font-weight: 600 !important;
}

.modal-search-wrapper {
  padding-bottom: 16px;
}
/* .modal-body-content removed - BaseModal handles scroll and container size */

.modal-content {
  padding: 0;
}

.group-title {
  margin: 24px 0 16px 0;
  font-size: 12px;
  color: var(--builder-text-primary);
  display: flex;
  align-items: center;
  font-weight: 600;
}

.group-title:first-child {
  margin-top: 0;
}

.category-badge {
  display: inline-block;
  padding: 2px 6px;
  font-size: 10px;
  font-weight: 600;
  border-radius: 3px;
  letter-spacing: 0.5px;
  margin-right: 6px;
}

.category-badge--flex {
  color: var(--builder-accent);
  background: rgba(var(--builder-accent-rgb, 32, 89, 234), 0.1);
  border: 1px solid var(--builder-accent);
}

.category-badge--grid {
  color: var(--builder-success);
  background: rgba(24, 183, 147, 0.1);
  border: 1px solid var(--builder-success);
}

.group-title::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--builder-border);
  margin-left: 10px;
  opacity: 0.5;
}

.module-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}

.module-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  padding: 16px 8px;
  background: transparent;
  border: 1px solid var(--builder-border);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.module-card:hover {
  border-color: var(--builder-accent);
  background: var(--builder-bg-secondary);
  box-shadow: var(--shadow-sm);
}

.module-icon {
  color: var(--builder-accent);
  display: flex;
  align-items: center;
  justify-content: center;
}

.module-name {
  font-size: 12px;
  color: var(--builder-text-primary);
  text-align: center;
  font-weight: 500;
  line-height: 1.3;
}

.no-results {
  text-align: center;
  color: var(--builder-text-muted);
  padding: 40px;
}

.layout-group {
    margin-bottom: 24px;
}

.layout-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}

.layout-card {
  padding: 12px;
  background: transparent;
  border: 1px solid var(--builder-border);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.layout-card:hover {
  border-color: var(--builder-accent);
  background: var(--builder-bg-secondary);
}

.layout-preview {
  display: flex;
  height: 30px;
  gap: 4px;
}

.preview-col {
  background: var(--builder-text-muted);
  opacity: 0.4;
  border-radius: 2px;
  flex: 1;
}

.preview-col.full-height {
  height: 100%;
}

.layout-card:hover .preview-col {
  background: var(--builder-accent);
  opacity: 0.8;
}

.layout-preview-stacked {
  display: flex;
  flex-direction: column;
  height: 30px;
  gap: 3px;
  width: 100%;
}

.preview-row {
  display: flex;
  flex: 1;
  gap: 3px;
}

.specialty-preview {
  gap: 4px;
}

.preview-specialty-col {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.preview-specialty-row {
  flex: 1;
  display: flex;
  gap: 3px;
}

.library-empty {
  text-align: center;
  color: var(--builder-text-muted);
  padding: 40px;
}
</style>

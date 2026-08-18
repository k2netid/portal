<template>
  <BaseModal
    :is-open="true"
    :title="t('builder.insertSectionModal.title')"
    :width="650"
    draggable
    placement="center"
    @close="$emit('close')"
  >
    <template #header>
      <div class="modal-tabs">
        <button 
          v-for="tab in tabs"
          :key="tab.id"
          class="modal-tab"
          :class="{ 'modal-tab--active': activeTab === tab.id }"
          @click="handleTabChange(tab.id)"
        >
          {{ te('builder.insertModal.tabs.' + tab.id) ? t('builder.insertModal.tabs.' + tab.id) : (te('builder.insertSectionModal.tabs.' + tab.id) ? t('builder.insertSectionModal.tabs.' + tab.id) : tab.label) }}
        </button>
      </div>
    </template>

    <div class="modal-body-content">
      <!-- Search (Presets) -->
      <div v-if="activeTab === 'presets'" class="modal-search-wrapper" style="padding-bottom: 16px;">
        <BaseInput 
          v-model="searchQuery"
          :placeholder="t('builder.insertModal.searchPlaceholder')"
          autofocus
        >
          <template #prefix>
             <Search :size="16" />
          </template>
        </BaseInput>
      </div>

      <div class="modal-content">
        <div v-if="activeTab === 'section'" class="layout-wrapper">
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
                    <component :is="getPreviewIcon({ category: 'hero' })" :size="24" />
                  </div>
                  <span class="module-name">{{ preset.name }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Library Tab - Section Templates -->
        <div v-else-if="activeTab === 'library'" class="library-wrapper">
          <div v-for="group in templateGroups" :key="group.id" class="template-group">
            <h4 class="group-title">
              <span class="category-badge category-badge--template">
                {{ group.icon }}
              </span>
              {{ group.title }}
            </h4>
            
            <div class="template-grid">
              <button 
                v-for="template in group.templates" 
                :key="template.id"
                class="template-card"
                @click="insertTemplate(template)"
                :title="template.description"
              >
                <div class="template-preview" :class="[`preview-${template.thumbnail}`, `accent-${template.category}`]">
                  <div class="preview-placeholder">
                    <component :is="getPreviewIcon(template)" class="preview-icon" />
                  </div>
                </div>
                <div class="template-info">
                  <h5 class="template-name">{{ template.name }}</h5>
                  <p class="template-desc">{{ template.description }}</p>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed, inject, type Component } from 'vue';
import { useI18n } from 'vue-i18n';
import { BaseModal, BaseInput } from '@/components/builder/ui';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import ModuleRegistry from '@/components/builder/core/ModuleRegistry';
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
import { sectionTemplates, type SectionTemplate } from '@/components/builder/templates/SectionTemplates';
import { pageTemplates, type PageTemplate } from '@/components/builder/templates/PageTemplates';
import Sparkles from 'lucide-vue-next/dist/esm/icons/sparkles.js';
import Layout from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import Users from 'lucide-vue-next/dist/esm/icons/users.js';
import MessageSquare from 'lucide-vue-next/dist/esm/icons/message-square.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Megaphone from 'lucide-vue-next/dist/esm/icons/megaphone.js';
import LayoutTemplate from 'lucide-vue-next/dist/esm/icons/layout-template.js';
import type { BuilderInstance, BlockInstance } from '@/types/builder';

interface Props {
  targetIndex?: number;
}

const props = withDefaults(defineProps<Props>(), {
  targetIndex: -1
});

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'inserted', type?: string, payload?: unknown): void;
}>();

const builder = inject<BuilderInstance>('builder');
const activeTab = ref('section');
const { t, te } = useI18n();

const tabs = [
  { id: 'section', label: 'New Section' },
  { id: 'presets', label: 'Design Presets' },
  { id: 'library', label: 'Add From Library' }
];

// Search/Filter logic
const searchQuery = ref('');
const loadingPresets = computed(() => builder?.loadingPresets?.value || false);
const presets = computed(() => builder?.presets?.value || []);

const filteredPresets = computed(() => {
  const allPresets = presets.value.filter(p => p.type === 'section');
  if (!searchQuery.value) return allPresets;
  const query = searchQuery.value.toLowerCase();
  return allPresets.filter(p => p.name.toLowerCase().includes(query));
});

const groupedPresets = computed(() => {
  const groups: Record<string, import('@/types/builder').BuilderPreset[]> = {};
  filteredPresets.value.forEach(preset => {
    const type = preset.type.charAt(0).toUpperCase() + preset.type.slice(1);
    if (!groups[type]) groups[type] = [];
    groups[type].push(preset);
  });
  return groups;
});

const selectPreset = (preset: import('@/types/builder').BuilderPreset) => {
  emit('inserted', 'preset', preset);
  emit('close');
};

const handleTabChange = (tabId: string) => {
  activeTab.value = tabId;
  if (tabId === 'presets' && presets.value.length === 0) {
    builder?.fetchPresets();
  }
};

const allGroups = computed(() => [
  { id: 'equal', type: 'flex', title: 'Equal Columns', items: equalLayouts },
  { id: 'offset', type: 'flex', title: 'Offset Columns', items: offsetLayouts },
  { id: 'multiRow', type: 'flex', title: 'Multi-Row', items: flexMultiRowPresets },
  { id: 'multiCol', type: 'flex', title: 'Multi-Column', items: flexMultiColumnPresets },
  { id: 'grid-multi-row', type: 'grid', title: 'Multi-Row', items: gridMultiRowPresets },
  { id: 'masonry', type: 'grid', title: 'Masonry', items: masonryPresets },
  { id: 'sidebar', type: 'grid', title: 'Sidebar', items: sidebarPresets }
]);

// Extract unique categories including Pages


type LocalTemplate = (SectionTemplate | (PageTemplate & { category: 'pages' })) & { templateType: 'section' | 'page' };

// Template groups for library tab
const templateGroups = computed(() => {
    const categoriesMap: Record<string, { id: string, title: string, icon: string, templates: LocalTemplate[] }> = {
        pages: { id: 'pages', title: 'Full Pages', icon: '📄', templates: [] },
        hero: { id: 'hero', title: 'Hero Sections', icon: '🚀', templates: [] },
        features: { id: 'features', title: 'Features', icon: '✨', templates: [] },
        content: { id: 'content', title: 'Content', icon: '📝', templates: [] },
        cta: { id: 'cta', title: 'Call to Action', icon: '📢', templates: [] },
        team: { id: 'team', title: 'Team', icon: '👥', templates: [] },
        contact: { id: 'contact', title: 'Contact', icon: '📞', templates: [] }
    };
    
    // Add sections
    sectionTemplates.forEach(tpl => {
        const cat = categoriesMap[tpl.category] || categoriesMap.content;
        cat.templates.push({ ...tpl, templateType: 'section' } as LocalTemplate);
    });

    // Add pages
    pageTemplates.forEach(tpl => {
        categoriesMap.pages.templates.push({ ...tpl, category: 'pages', templateType: 'page' } as LocalTemplate);
    });
    
    return Object.values(categoriesMap).filter(c => c.templates.length > 0);
});

const getPreviewIcon = (template: Partial<LocalTemplate>) => {
    if (template.templateType === 'page') return LayoutTemplate;
    
    const iconMap: Record<string, Component> = {
        hero: Sparkles,
        features: Layout,
        content: FileText,
        cta: Megaphone,
        team: Users,
        header: FileText,
        contact: MessageSquare
    };
    return iconMap[template.category || ''] || Layout;
};

const insertTemplate = async (template: LocalTemplate) => {
    if (!builder || !template.factory) return;
    
    // Handle Page Templates (Full Content Replacement)
    if (template.templateType === 'page') {
        const confirmed = await builder?.confirm({
            title: t('builder.modals.confirm.resetLayout'),
            message: t('builder.modals.confirm.resetLayoutDesc'),
            confirmText: t('builder.modals.confirm.confirm'),
            cancelText: t('builder.modals.confirm.cancel'),
            type: 'warning'
        });
        if (confirmed) {
            const blocks = template.factory() as import('@/types/builder').BlockInstance[];
            
            // Regenerate all IDs for safety
            const regenerateAll = (nodes: import('@/types/builder').BlockInstance[]) => {
                nodes.forEach(node => {
                    node.id = ModuleRegistry.generateId();
                    if (node.children) regenerateAll(node.children);
                });
            };
            regenerateAll(blocks);
            
            builder.blocks.value = blocks;
            builder.takeSnapshot();
            emit('inserted');
            emit('close');
        }
        return;
    }

    // Handle Section Templates
    const clonedSection = template.factory() as import('@/types/builder').BlockInstance;
    const regenerateIds = (node: import('@/types/builder').BlockInstance) => {
        node.id = ModuleRegistry.generateId();
        if (node.children) {
            node.children.forEach(regenerateIds);
        }
    };
    regenerateIds(clonedSection);
    
    if (!builder.blocks.value) builder.blocks.value = [];
    
    const targetIndex = props.targetIndex !== -1 ? props.targetIndex : builder.blocks.value.length;
    
    builder.blocks.value.splice(targetIndex, 0, clonedSection);
    builder.takeSnapshot();
    builder.selectModule(clonedSection.id);
    
    emit('inserted');
    emit('close');
};

const selectLayout = (layout: LayoutPreset) => {
    if (!builder) return;
    
    // 1. Create Section
    const section = builder.insertModule('section', null, props.targetIndex);
    if (!section) return;

    // Helper to create row
    const createRow = (config: LayoutPreset, parent: BlockInstance = section) => {
        const row = ModuleRegistry.createInstance('row');
        if (!row) return;

        row.id = ModuleRegistry.generateId();
        if (!parent.children) parent.children = [];
        parent.children.push(row);
        
        if (!row.children) row.children = [];
        
        if (config.cols) {
            config.cols.forEach((colConfig) => {
                const col = ModuleRegistry.createInstance('column');
                if (!col) return;
                
                col.id = ModuleRegistry.generateId();
                col.settings.flexGrow = colConfig.width;
                row.children!.push(col);
                
                if (colConfig.rows) {
                    colConfig.rows.forEach((nestedRowConfig) => {
                        createRow(nestedRowConfig, col);
                    });
                }
            });
        } else {
            const widths = config.widths || [1];
            widths.forEach((width: number) => {
                const col = ModuleRegistry.createInstance('column');
                if (!col) return;

                col.id = ModuleRegistry.generateId();
                col.settings.flexGrow = width;
                row.children!.push(col);
            });
        }
    };

    if (layout.rows) {
        layout.rows.forEach((rowConfig) => createRow(rowConfig));
    } else {
        createRow(layout);
    }
    
    builder.takeSnapshot();
    builder.selectModule(section.id);
    
    emit('inserted');
    emit('close');
};
</script>

<style scoped>
.modal-tabs {
  display: flex;
  margin-left: 20px;
}

.modal-tab {
  background: none;
  border: none;
  padding: 12px 16px;
  color: var(--builder-text-secondary);
  cursor: pointer;
  border-bottom: 2px solid transparent;
  font-weight: 500;
  font-size: 14px;
}

.modal-tab:hover {
  color: var(--builder-text-primary);
}

.modal-tab--active {
  color: var(--builder-accent);
  border-bottom-color: var(--builder-accent);
}
/* .modal-body-content removed - BaseModal handles scroll and container size */

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

.group-title {
  margin: 0 0 12px 0;
  font-size: 12px;
  color: var(--builder-text-primary);
  display: flex;
  align-items: center;
  font-weight: 600;
}

.group-title::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--builder-border);
  margin-left: 10px;
  opacity: 0.5;
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

.library-wrapper {
    padding: 0;
}

.template-group {
    margin-bottom: 24px;
}

.template-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.template-card {
    display: flex;
    flex-direction: column;
    text-align: left;
    padding: 0;
    background: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    overflow: hidden;
}

.template-card:hover {
    border-color: var(--builder-accent);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.template-preview {
    height: 80px;
    background: linear-gradient(135deg, var(--builder-accent-light, rgba(32, 89, 234, 0.1)) 0%, var(--builder-bg-tertiary) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    border-bottom: 1px solid var(--builder-border);
}

.preview-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: var(--builder-bg-primary);
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-icon {
    width: 20px;
    height: 20px;
    color: var(--builder-accent);
}

.template-card:hover .preview-icon {
    color: var(--builder-accent);
}

.template-info {
    padding: 12px;
}

.template-name {
    margin: 0 0 4px 0;
    font-size: 13px;
    font-weight: 600;
    color: var(--builder-text-primary);
}

.template-desc {
    margin: 0;
    font-size: 11px;
    color: var(--builder-text-muted);
    line-height: 1.4;
}

.category-badge--template {
    font-size: 14px;
    background: transparent;
    border: none;
    padding: 0;
    margin-right: 8px;
}
.module-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}

.module-card {
  display: flex;
  flex-direction: column;
  text-align: center;
  align-items: center;
  gap: 10px;
  padding: 16px 8px;
  background: transparent;
  border: 1px solid var(--builder-border);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  width: 100%;
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
  font-size: 11px;
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
</style>

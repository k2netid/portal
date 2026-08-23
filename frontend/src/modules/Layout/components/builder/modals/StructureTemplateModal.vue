<template>
  <BaseModal
    :is-open="true"
    :title="$t('builder.structureTemplateModal.title')"
    :width="650"
    draggable
    placement="center"
    @close="$emit('close')"
  >
    <div class="modal-body-content">
      <div class="modal-content">
        <div class="layout-wrapper">
<!-- Filtered Layout Groups Loop -->
          <div v-for="group in filteredGroups" :key="group.id" class="layout-group">
            <h4 class="group-title">
              <span :class="['category-badge', `category-badge--${group.type}`]">
                {{ group.type === 'flex' ? t('builder.fields.layoutTypes.flex') : t('builder.fields.layoutTypes.grid') }}
              </span>
              {{ t('builder.insertModal.layoutGroups.' + group.id.replace('flex-', '').replace('grid-', '')) }}
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

          <div v-if="filteredGroups.length === 0" class="no-results">
            {{ t('builder.structureTemplateModal.noResults') }}
</div>
      </div>
      </div>
    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { BaseModal } from '@/components/builder/ui';
import { 
    equalLayouts, 
    offsetLayouts, 
    flexMultiRowPresets, 
    flexMultiColumnPresets, 
    gridMultiRowPresets,
    masonryPresets,
    sidebarPresets,
    type LayoutPreset
} from '@/components/builder/constants/layouts.js';

interface Props {
  targetType: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'insert', layout: LayoutPreset): void;
}>();

const { t } = useI18n();

// Define all groups
const allGroups = [
  { id: 'flex-equal', type: 'flex', title: 'Equal Columns', items: equalLayouts },
  { id: 'flex-offset', type: 'flex', title: 'Offset Columns', items: offsetLayouts },
  { id: 'flex-multi-row', type: 'flex', title: 'Multi-Row', items: flexMultiRowPresets },
  { id: 'flex-multi-col', type: 'flex', title: 'Multi-Column', items: flexMultiColumnPresets },
  { id: 'grid-multi-row', type: 'grid', title: 'Multi-Row', items: gridMultiRowPresets },
  { id: 'grid-masonry', type: 'grid', title: 'Masonry', items: masonryPresets },
  { id: 'grid-sidebar', type: 'grid', title: 'Sidebar', items: sidebarPresets }
];

// Filter groups based on targetType
// row: show all (Flex + Grid)
// section/column: show only Grid items
const filteredGroups = computed(() => {
  if (props.targetType === 'row') {
    return allGroups;
  }
  // For section and column, show ONLY grid typet
  return allGroups.filter(g => g.type === 'grid');
});

const selectLayout = (layout: LayoutPreset) => {
  emit('insert', layout);
};
</script>

<style scoped>
.modal-body-content {
  padding: 20px;
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

.no-results {
  padding: 40px;
  text-align: center;
  color: var(--builder-text-muted);
}
</style>

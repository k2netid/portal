<template>
  <div class="pages-panel">
    <div class="pages-search">
      <BaseInput 
        v-model="searchQuery"
        :placeholder="t('builder.panels.pages.searchPlaceholder')"
      >
        <template #prefix>
          <Search :size="14" />
        </template>
      </BaseInput>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="pages-loading">
      <div class="spinner"></div>
      <span>{{ t('builder.panels.pages.loading') }}</span>
    </div>

    <!-- List -->
    <template v-else>
      <div class="pages-list">
        <div 
          v-for="(page, index) in filteredPages" 
          :key="page.id || `page-${index}`" 
          class="page-item" 
          :class="{ 'page-item--active': currentPageId === page.id }"
          @click="selectPage(page.id as string | number)"
        >
          <div class="page-info">
            <span class="page-title">{{ page.title }}</span>
            <span class="page-slug">/{{ page.slug }}</span>
            <span v-if="page.status === 'draft'" class="page-status-badge">{{ page.status }}</span>
          </div>
          <div v-if="builder?.mode.value === 'site'" class="page-actions">
            <!-- Normal edit button (switching page in builder) -->
            <button class="action-btn" :title="t('builder.panels.pages.actions.edit')" @click.stop="selectPage(page.id as string | number)">
              <Edit2 :size="14" />
            </button>
            <button class="action-btn" :title="t('builder.panels.pages.actions.delete')" @click.stop="handleDelete(page)">
              <Trash2 :size="14" />
            </button>
          </div>
        </div>
        
        <div v-if="filteredPages.length === 0" class="empty-results">
          {{ t('builder.panels.pages.empty') }}
        </div>
      </div>
      
      <!-- Add Button -->
      <div v-if="builder?.mode.value === 'site'" class="panel-footer">
          <button class="add-page-btn" @click="handleCreate">
            <Plus :size="16" />
            <span>{{ t('builder.panels.pages.addNew') }}</span>
          </button>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, inject, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import Edit2 from 'lucide-vue-next/dist/esm/icons/pen-line.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import { BaseInput } from '@/components/builder/ui';
import type { BuilderInstance, PageMetadata } from '@/types/builder';

const { t } = useI18n();
const builder = inject<BuilderInstance>('builder');
// Fix types for reactive values coming from builder
const pages = computed(() => (builder?.pages?.value || []) as PageMetadata[]);
const currentPageId = computed(() => builder?.currentPageId?.value);
const loading = computed(() => builder?.pagesLoading?.value || false);

const searchQuery = ref('');

const filteredPages = computed(() => {
  if (!searchQuery.value) return pages.value;
  const query = searchQuery.value.toLowerCase();
  return pages.value.filter(p => 
    p.title.toLowerCase().includes(query) || 
    p.slug.toLowerCase().includes(query)
  );
});

const selectPage = (id: number | string) => {
  if (builder?.switchCanvas) {
      builder.switchCanvas(String(id)); // Assuming switchCanvas handles page switching logic if mapped
  }
  builder?.setCurrentPage(id);
};

const handleCreate = () => {
  const title = prompt(t('builder.panels.pages.promptTitle'));
  if (title) {
     builder?.addPage(title);
  }
};

const handleDelete = async (page: PageMetadata) => {
    const confirmed = await builder?.confirm?.({
        title: t('builder.modals.confirm.deletePage'),
        message: t('builder.modals.confirm.deletePageDesc'),
        confirmText: t('builder.modals.confirm.delete'),
        cancelText: t('builder.modals.confirm.cancel'),
        type: 'delete'
    });
    if (confirmed) {
        try {
            await builder?.deletePage(page.id as string | number);
        } catch (error) {
            logger.error('Delete failed:', error);
        }
    }
};

onMounted(() => {
    builder?.fetchPages();
});
</script>

<style scoped>
.pages-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.pages-search {
  padding: var(--spacing-sm);
  position: relative;
  z-index: 2;
  background: var(--builder-bg-primary);
}

.pages-list {
  flex: 1;
  overflow-y: auto;
  padding: 0 var(--spacing-sm) var(--spacing-sm);
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.page-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  border-radius: var(--border-radius-sm);
  cursor: pointer;
  transition: background 0.2s;
  border-left: 3px solid transparent;
}

.page-item:hover {
  background: var(--builder-bg-secondary);
}

.page-item--active {
  background: var(--builder-bg-secondary);
  border-left-color: var(--builder-accent);
}

.page-info {
  display: flex;
  flex-direction: column;
}

.page-title {
  font-size: var(--font-size-sm);
  font-weight: 500;
  color: var(--builder-text-primary);
}

.page-slug {
  font-size: 11px;
  color: var(--builder-text-muted);
}

.page-status-badge {
  font-size: 10px;
  text-transform: uppercase;
  background: var(--builder-bg-secondary);
  color: var(--builder-text-muted);
  padding: 2px 6px;
  border-radius: 10px;
  width: fit-content;
  margin-top: 2px;
}

.page-actions {
  display: flex;
  gap: 4px;
  opacity: 0;
  transition: opacity 0.2s;
}

.page-item:hover .page-actions {
  opacity: 1;
}

.action-btn {
  padding: 4px;
  background: transparent;
  border: none;
  color: var(--builder-text-muted);
  cursor: pointer;
  border-radius: var(--border-radius-sm);
}

.action-btn:hover {
  background: var(--builder-bg-secondary);
  color: var(--builder-text-primary);
}

.empty-results {
  padding: var(--spacing-md);
  text-align: center;
  color: var(--builder-text-muted);
  font-size: var(--font-size-sm);
}

.panel-footer {
    padding: var(--spacing-sm);
    border-top: 1px solid var(--builder-border);
}

.add-page-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 8px;
  width: 100%;
  background: var(--builder-bg-primary);
  border: 1px dashed var(--builder-border);
  border-radius: var(--border-radius-sm);
  color: var(--builder-text-secondary);
  font-size: var(--font-size-sm);
  cursor: pointer;
  transition: all 0.2s;
}

.add-page-btn:hover {
  border-color: var(--builder-accent);
  color: var(--builder-accent);
  background: var(--builder-bg-primary);
}

.pages-loading {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  color: var(--builder-text-muted);
  font-size: var(--font-size-sm);
}

.spinner {
  width: 24px;
  height: 24px;
  border: 2px solid var(--builder-border);
  border-top-color: var(--builder-accent);
  border-radius: 50%;
  animation: rotate 0.8s linear infinite;
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>

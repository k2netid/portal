<template>
  <div class="pages-panel">
    <!-- Header Search & Filter Tabs -->
    <div class="pages-header">
      <BaseInput 
        v-model="searchQuery"
        :placeholder="t('builder.panels.pages.searchPlaceholder', 'Search pages or posts...')"
      >
        <template #prefix>
          <Search :size="14" />
        </template>
      </BaseInput>

      <!-- Filter Tabs -->
      <div class="type-filter-tabs">
        <button
          type="button"
          class="type-tab-btn"
          :class="{ 'type-tab-btn--active': activeTypeFilter === 'all' }"
          @click="activeTypeFilter = 'all'"
        >
          All ({{ allContentList.length }})
        </button>
        <button
          type="button"
          class="type-tab-btn"
          :class="{ 'type-tab-btn--active': activeTypeFilter === 'page' }"
          @click="activeTypeFilter = 'page'"
        >
          Pages ({{ pageCount }})
        </button>
        <button
          type="button"
          class="type-tab-btn"
          :class="{ 'type-tab-btn--active': activeTypeFilter === 'post' }"
          @click="activeTypeFilter = 'post'"
        >
          Posts ({{ postCount }})
        </button>
        <button
          type="button"
          class="type-tab-btn"
          :class="{ 'type-tab-btn--active': activeTypeFilter === 'theme' }"
          @click="activeTypeFilter = 'theme'"
        >
          Theme ({{ themeTemplates.length }})
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="pages-loading">
      <div class="spinner"></div>
      <span>{{ t('builder.panels.pages.loading', 'Loading pages...') }}</span>
    </div>

    <!-- List -->
    <template v-else>
      <div class="pages-list">
        <div 
          v-for="(page, index) in filteredItems" 
          :key="page.id || `item-${index}`" 
          class="page-item" 
          :class="{ 'page-item--active': String(currentPageId) === String(page.id) || (page.isThemeTemplate && builder?.activeThemePage?.value === page.themePage) }"
          @click="selectItem(page)"
        >
          <div class="page-info">
            <div class="flex items-center gap-1.5 mb-0.5">
              <span class="page-title">{{ page.title }}</span>
              <span
                class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.2 rounded leading-tight"
                :class="page.isThemeTemplate ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : (page.type === 'post' ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400' : 'bg-primary/10 text-primary')"
              >
                {{ page.isThemeTemplate ? 'Theme' : (page.type || 'page') }}
              </span>
            </div>
            <div class="flex items-center gap-2 text-xs">
              <span class="page-slug">/{{ page.slug }}</span>
              <span v-if="page.status === 'draft'" class="page-status-badge">draft</span>
            </div>
          </div>

          <div v-if="builder?.mode.value === 'site' && !page.isThemeTemplate" class="page-actions">
            <button class="action-btn" :title="t('builder.panels.pages.actions.edit', 'Edit')" @click.stop="selectItem(page)">
              <Edit2 :size="14" />
            </button>
            <button class="action-btn" :title="t('builder.panels.pages.actions.delete', 'Delete')" @click.stop="handleDelete(page)">
              <Trash2 :size="14" />
            </button>
          </div>
        </div>
        
        <div v-if="filteredItems.length === 0" class="empty-results">
          {{ t('builder.panels.pages.empty', 'No content found') }}
        </div>
      </div>
      
      <!-- Add Button -->
      <div v-if="builder?.mode.value === 'site'" class="panel-footer">
        <button class="add-page-btn" @click="handleCreate">
          <Plus :size="16" />
          <span>{{ t('builder.panels.pages.addNew', 'Add New Page') }}</span>
        </button>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, inject, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import Edit2 from 'lucide-vue-next/dist/esm/icons/pen-line.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import { BaseInput } from '@/modules/Layout/components/builder/ui';
import type { BuilderInstance, PageMetadata } from '@/modules/Layout/types/builder';

interface ContentItem extends PageMetadata {
  type?: string
  isThemeTemplate?: boolean
  themePage?: string
}

const { t } = useI18n();
const builder = inject<BuilderInstance>('builder');

const pages = computed(() => (builder?.pages?.value || []) as ContentItem[]);
const currentPageId = computed(() => builder?.currentPageId?.value);
const loading = computed(() => builder?.pagesLoading?.value || false);

const searchQuery = ref('');
const activeTypeFilter = ref<'all' | 'page' | 'post' | 'theme'>('all');

// Standard theme page templates (slug matches public.ts; themePage → ThemePageResolver)
const themeTemplates: ContentItem[] = [
  { id: 'theme-home', title: 'Beranda (Home)', slug: 'home', type: 'page', isThemeTemplate: true, status: 'published', themePage: 'pages/Home' },
  { id: 'theme-about', title: 'Tentang Kami (About)', slug: 'about', type: 'page', isThemeTemplate: true, status: 'published', themePage: 'pages/About' },
  { id: 'theme-tim', title: 'Tim Kami (Team)', slug: 'tim', type: 'page', isThemeTemplate: true, status: 'published', themePage: 'pages/Tim' },
  { id: 'theme-pricing', title: 'Harga & Paket (Pricing)', slug: 'pricing', type: 'page', isThemeTemplate: true, status: 'published', themePage: 'pages/Pricing' },
  { id: 'theme-solusi', title: 'Produk & Solusi', slug: 'solusi', type: 'page', isThemeTemplate: true, status: 'published', themePage: 'pages/Solusi' },
  { id: 'theme-contact', title: 'Hubungi Kami (Contact)', slug: 'contact', type: 'page', isThemeTemplate: true, status: 'published', themePage: 'pages/Contact' },
  { id: 'theme-blog', title: 'Arsip Berita (Blog)', slug: 'blog', type: 'page', isThemeTemplate: true, status: 'published', themePage: 'pages/Blog' },
  { id: 'theme-career', title: 'Pusat Karier (Careers)', slug: 'career', type: 'page', isThemeTemplate: true, status: 'published', themePage: 'pages/CareerCenter' },
  { id: 'theme-achievement', title: 'Sorotan & Prestasi', slug: 'achievement', type: 'page', isThemeTemplate: true, status: 'published', themePage: 'pages/Achievement' },
  { id: 'theme-search', title: 'Pencarian (Search)', slug: 'search', type: 'page', isThemeTemplate: true, status: 'published', themePage: 'pages/Search' },
];

const allContentList = computed<ContentItem[]>(() => {
  return [...pages.value];
});

const pageCount = computed(() => pages.value.filter(p => p.type === 'page' || !p.type).length);
const postCount = computed(() => pages.value.filter(p => p.type === 'post').length);

const filteredItems = computed<ContentItem[]>(() => {
  let list: ContentItem[] = [];

  if (activeTypeFilter.value === 'all') {
    list = allContentList.value;
  } else if (activeTypeFilter.value === 'page') {
    list = pages.value.filter(p => p.type === 'page' || !p.type);
  } else if (activeTypeFilter.value === 'post') {
    list = pages.value.filter(p => p.type === 'post');
  } else if (activeTypeFilter.value === 'theme') {
    list = themeTemplates;
  }

  if (!searchQuery.value) return list;
  const query = searchQuery.value.toLowerCase();
  return list.filter(p => 
    p.title.toLowerCase().includes(query) || 
    p.slug.toLowerCase().includes(query)
  );
});

const selectItem = async (item: ContentItem) => {
  if (item.isThemeTemplate) {
    const themePage = item.themePage || `pages/${item.slug.charAt(0).toUpperCase()}${item.slug.slice(1)}`;
    const existing = pages.value.find((p) => p.slug === item.slug && !p.isThemeTemplate);

    // Prefer CMS document only when it already has builder blocks to edit.
    if (existing?.id && builder?.setCurrentPage && builder?.loadContent) {
      await builder.setCurrentPage(existing.id);
      if ((builder.blocks.value?.length ?? 0) > 0) {
        return;
      }
    }

    // Otherwise show the live theme Vue page (do not create an empty draft).
    builder?.openThemePage?.({
      slug: item.slug,
      themePage,
      title: item.title,
    });
    return;
  }

  if (item.id === undefined || item.id === null) return;
  if (builder?.setCurrentPage) {
    await builder.setCurrentPage(item.id);
  }
};

const handleCreate = () => {
  const title = prompt(t('builder.panels.pages.promptTitle', 'Enter page title:'));
  if (title) {
    builder?.addPage(title);
  }
};

const handleDelete = async (page: ContentItem) => {
  const confirmed = await builder?.confirm?.({
    title: t('builder.modals.confirm.deletePage', 'Delete Page'),
    message: t('builder.modals.confirm.deletePageDesc', 'Are you sure you want to delete this page?'),
    confirmText: t('builder.modals.confirm.delete', 'Delete'),
    cancelText: t('builder.modals.confirm.cancel', 'Cancel'),
    type: 'delete'
  });
  if (confirmed) {
    try {
      await builder?.deletePage(page.id as string | number);
    } catch (error) {
      logger.error('Delete failed:', error instanceof Error ? error.message : String(error));
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
}

.pages-header {
  padding: 10px 12px;
  background: var(--builder-bg-primary);
  border-bottom: 1px solid var(--builder-border);
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.type-filter-tabs {
  display: flex;
  gap: 4px;
  background: var(--builder-bg-secondary);
  padding: 2px;
  border-radius: 6px;
  overflow-x: auto;
}

.type-tab-btn {
  flex: 1;
  padding: 4px 6px;
  font-size: 10px;
  font-weight: 600;
  text-align: center;
  border-radius: 4px;
  border: none;
  background: transparent;
  color: var(--builder-text-muted);
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.15s ease;
}

.type-tab-btn:hover {
  color: var(--builder-text-primary);
}

.type-tab-btn--active {
  background: var(--builder-bg-primary);
  color: var(--builder-text-primary);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.pages-list {
  flex: 1;
  overflow-y: auto;
  padding: 8px 12px;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.page-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 10px;
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
  min-width: 0;
}

.page-title {
  font-size: var(--font-size-sm);
  font-weight: 600;
  color: var(--builder-text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 180px;
}

.page-slug {
  font-size: 11px;
  color: var(--builder-text-muted);
  font-family: monospace;
}

.page-status-badge {
  font-size: 9px;
  text-transform: uppercase;
  background: var(--builder-bg-secondary);
  color: var(--builder-text-muted);
  padding: 1px 5px;
  border-radius: 8px;
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
  padding: 12px;
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

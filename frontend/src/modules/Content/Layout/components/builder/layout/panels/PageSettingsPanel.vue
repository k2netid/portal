<template>
  <div class="page-settings-panel">
    <!-- Main Container -->
    <div class="settings-container">
      <!-- Publishing Section -->
      <div class="sidebar-section">
        <button 
          type="button"
          @click="sections.publishing = !sections.publishing"
          class="section-header"
        >
          <div class="section-header-left">
            <div class="section-icon section-icon--green">
              <FileCheck class="w-3.5 h-3.5" />
            </div>
            <span class="section-title-text">{{ t('features.content.form.publishing') || 'Publishing' }}</span>
          </div>
          <ChevronDown 
            class="section-chevron"
            :class="{ 'rotate-180': sections.publishing }"
          />
        </button>
        <div v-show="sections.publishing" class="section-content">
          <div class="field-group">
            <label class="field-label">{{ t('features.content.form.status') }}</label>
            <select v-model="content.status" class="setting-select">
              <option value="draft">{{ t('features.content.status.draft') }}</option>
              <option value="published">{{ t('features.content.status.published') }}</option>
              <option value="pending">{{ t('builder.panels.pageSettings.status.pending') || 'Pending' }}</option>
              <option value="archived">{{ t('features.content.status.archived') }}</option>
            </select>
          </div>

          <div v-if="content.status === 'published'" class="field-group">
            <label class="field-label">{{ t('features.content.form.publishDate') || 'Publish Date' }}</label>
            <input 
              type="datetime-local" 
              v-model="content.published_at" 
              class="setting-input"
            />
          </div>

          <div class="field-group">
            <label class="field-label">{{ t('features.content.form.slug') }}</label>
            <div class="slug-input-wrapper">
              <span class="slug-prefix">/</span>
              <input 
                type="text" 
                v-model="content.slug" 
                class="setting-input slug-input" 
                :placeholder="t('features.content.form.slugPlaceholder') || 'auto-generated-from-title'" 
              />
            </div>
          </div>

          <div class="field-group">
            <label class="field-label">{{ t('features.content.form.type') || 'Type' }}</label>
            <select v-model="content.type" class="setting-select">
              <option value="post">Post</option>
              <option value="page">Page</option>
              <option value="custom">Custom</option>
            </select>
          </div>

          <div class="toggle-row">
            <div class="toggle-info">
              <span class="toggle-label">{{ t('features.content.form.featured') || 'Featured Content' }}</span>
              <span class="toggle-desc">{{ t('features.content.form.featuredDesc') || 'Pin this content to the top' }}</span>
            </div>
            <button 
              @click="content.is_featured = !content.is_featured"
              class="switch-toggle"
              :class="{ 'switch-toggle--active': content.is_featured }"
            >
              <div class="switch-thumb"></div>
            </button>
          </div>
        </div>
      </div>

      <!-- Menu Settings Section -->
      <div class="sidebar-section">
        <button 
          type="button"
          @click="sections.menu = !sections.menu"
          class="section-header"
        >
          <div class="section-header-left">
            <div class="section-icon section-icon--indigo">
              <MenuSquare class="w-3.5 h-3.5" />
            </div>
            <span class="section-title-text">{{ t('features.menus.title') || 'Menus' }}</span>
          </div>
          <div class="section-header-right">
            <span v-if="content.menu_item?.add_to_menu" class="status-badge status-badge--indigo">Active</span>
            <ChevronDown 
              class="section-chevron"
              :class="{ 'rotate-180': sections.menu }"
            />
          </div>
        </button>
        <div v-show="sections.menu" class="section-content">
          <div class="toggle-row">
            <div class="toggle-info">
              <span class="toggle-label">{{ t('features.menus.actions.addToMenu') || 'Add to Menu' }}</span>
              <span class="toggle-desc">Add this content to a menu</span>
            </div>
            <button 
              @click="toggleAddToMenu"
              class="switch-toggle"
              :class="{ 'switch-toggle--active': content.menu_item?.add_to_menu }"
            >
              <div class="switch-thumb"></div>
            </button>
          </div>

          <template v-if="content.menu_item?.add_to_menu">
            <div class="field-group">
              <label class="field-label">{{ t('features.menus.form.selectMenu') || 'Select Menu' }}</label>
              <select v-model="selectedMenuId" @change="handleMenuChange" class="setting-select">
                <option value="">{{ t('features.menus.form.selectMenu') }}</option>
                <option v-for="menu in builder?.menus?.value || []" :key="menu.id" :value="menu.id">
                  {{ menu.name }}
                </option>
              </select>
            </div>

            <div class="field-group">
              <label class="field-label">{{ t('features.menus.form.parentItem') || 'Parent Item' }}</label>
              <select v-model="content.menu_item.parent_id" class="setting-select" :disabled="loadingParentItems">
                <option :value="null">{{ t('features.menus.form.rootItem') || 'Root (No Parent)' }}</option>
                <option v-for="item in menuParentItems" :key="item.id" :value="item.id">
                  {{ '  '.repeat(item.depth || 0) + (item.title || item.label) }}
                </option>
              </select>
            </div>

            <div class="field-group">
              <label class="field-label">{{ t('features.menus.form.label') || 'Menu Label' }}</label>
              <input 
                type="text" 
                v-model="content.menu_item.title" 
                class="setting-input" 
                :placeholder="t('features.menus.form.labelPlaceholder') || 'Menu label...'" 
              />
            </div>
          </template>
        </div>
      </div>

      <!-- Taxonomy Section -->
      <div class="sidebar-section">
        <button 
          type="button"
          @click="sections.taxonomy = !sections.taxonomy"
          class="section-header"
        >
          <div class="section-header-left">
            <div class="section-icon section-icon--blue">
              <Tags class="w-3.5 h-3.5" />
            </div>
            <span class="section-title-text">{{ t('features.content.form.taxonomy') || 'Taxonomy' }}</span>
          </div>
          <ChevronDown 
            class="section-chevron"
            :class="{ 'rotate-180': sections.taxonomy }"
          />
        </button>
        <div v-show="sections.taxonomy" class="section-content">
          <div class="field-group">
            <label class="field-label">{{ t('features.content.form.category') }}</label>
            <select v-model="content.category_id" class="setting-select">
              <option :value="null">{{ t('features.content.form.selectCategory') || 'Select Category' }}</option>
              <option v-for="cat in builder?.categories?.value || []" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <div class="field-group">
            <label class="field-label">{{ t('features.content.form.tags') }}</label>
            <div class="flex flex-wrap gap-1.5 mb-2">
              <div v-for="tag in content.tags" :key="tag.id || tag.name" class="tag-badge">
                {{ tag.name }}
                <button @click="removeTag(tag)" class="tag-remove">
                  <X class="w-3 h-3" />
                </button>
              </div>
            </div>
            <div class="relative">
              <input 
                type="text" 
                v-model="tagQuery" 
                @input="handleTagSearch"
                @keydown.enter.prevent="addCustomTag"
                class="setting-input pl-8" 
                :placeholder="t('features.content.form.addTags') || 'Search or add tag...'" 
              />
              <Search class="absolute left-2.5 top-2.5 w-4 h-4 text-muted-foreground" />
              
              <div v-if="tagSuggestions.length > 0" class="tag-suggestions">
                <div 
                  v-for="tag in tagSuggestions" 
                  :key="tag.id"
                  @click="addTag(tag)"
                  class="suggestion-item"
                >
                  {{ tag.name }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Featured Image Section -->
      <div class="sidebar-section">
        <button 
          type="button"
          @click="sections.image = !sections.image"
          class="section-header"
        >
          <div class="section-header-left">
            <div class="section-icon section-icon--purple">
              <ImageIcon class="w-3.5 h-3.5" />
            </div>
            <span class="section-title-text">{{ t('features.content.form.featuredImage') }}</span>
          </div>
          <div class="section-header-right">
            <div v-if="content.featured_image" class="thumb-preview">
              <img :src="content.featured_image" />
            </div>
            <ChevronDown 
              class="section-chevron"
              :class="{ 'rotate-180': sections.image }"
            />
          </div>
        </button>
        <div v-show="sections.image" class="section-content">
          <MediaPicker 
            @selected="(media) => content.featured_image = media.url"
            :constraints="{ allowedExtensions: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'] }"
          >
            <template #trigger="{ open }">
              <div class="image-preview" @click="open">
                <template v-if="content.featured_image">
                  <img :src="content.featured_image" class="w-full h-full object-cover rounded" />
                  <div class="image-overlay">
                    <Edit2 class="w-6 h-6 text-white" />
                  </div>
                </template>
                <div v-else class="image-placeholder">
                  <ImageIcon class="w-10 h-10" />
                  <span>{{ t('builder.panels.pageSettings.placeholders.selectImage') }}</span>
                </div>
              </div>
            </template>
          </MediaPicker>
          <button v-if="content.featured_image" @click="content.featured_image = null" class="remove-image-btn">
            {{ t('features.content.form.removeImage') || 'Remove Image' }}
          </button>
        </div>
      </div>

      <!-- Excerpt Section -->
      <div class="sidebar-section">
        <button 
          type="button"
          @click="sections.excerpt = !sections.excerpt"
          class="section-header"
        >
          <div class="section-header-left">
            <div class="section-icon section-icon--amber">
              <FileText class="w-3.5 h-3.5" />
            </div>
            <span class="section-title-text">{{ t('features.content.form.excerpt') }}</span>
          </div>
          <ChevronDown 
            class="section-chevron"
            :class="{ 'rotate-180': sections.excerpt }"
          />
        </button>
        <div v-show="sections.excerpt" class="section-content">
          <textarea 
            v-model="content.excerpt" 
            rows="3" 
            class="setting-input" 
            :placeholder="t('features.content.form.excerptPlaceholder') || 'Short summary...'"
          ></textarea>
        </div>
      </div>

      <!-- Discussion Section -->
      <div class="sidebar-section">
        <button 
          type="button"
          @click="sections.discussion = !sections.discussion"
          class="section-header"
        >
          <div class="section-header-left">
            <div class="section-icon section-icon--orange">
              <MessageSquare class="w-3.5 h-3.5" />
            </div>
            <span class="section-title-text">{{ t('features.content.form.discussion') || 'Discussion' }}</span>
          </div>
          <div class="section-header-right">
            <span v-if="!content.comment_status" class="status-badge status-badge--orange">Disabled</span>
            <ChevronDown 
              class="section-chevron"
              :class="{ 'rotate-180': sections.discussion }"
            />
          </div>
        </button>
        <div v-show="sections.discussion" class="section-content">
          <div class="toggle-row">
            <div class="toggle-info">
              <span class="toggle-label">{{ t('features.content.form.allowComments') || 'Allow Comments' }}</span>
              <span class="toggle-desc">{{ t('features.content.form.allowCommentsDesc') || 'Enable comments on this content' }}</span>
            </div>
            <button 
              @click="content.comment_status = !content.comment_status"
              class="switch-toggle"
              :class="{ 'switch-toggle--active': content.comment_status }"
            >
              <div class="switch-thumb"></div>
            </button>
          </div>
        </div>
      </div>

      <!-- SEO Section -->
      <div class="sidebar-section sidebar-section--last">
        <button 
          type="button"
          @click="sections.seo = !sections.seo"
          class="section-header"
        >
          <div class="section-header-left">
            <div class="section-icon section-icon--red">
              <Globe class="w-3.5 h-3.5" />
            </div>
            <span class="section-title-text">SEO</span>
          </div>
          <ChevronDown 
            class="section-chevron"
            :class="{ 'rotate-180': sections.seo }"
          />
        </button>
        <div v-show="sections.seo" class="section-content">
          <div class="field-group">
            <label class="field-label">{{ t('features.content.seo.metaTitle') || 'Meta Title' }}</label>
            <input type="text" v-model="content.meta_title" class="setting-input" />
          </div>
          
          <div class="field-group">
            <label class="field-label">{{ t('features.content.seo.metaDescription') || 'Meta Description' }}</label>
            <textarea v-model="content.meta_description" rows="2" class="setting-input"></textarea>
          </div>

          <div class="field-group">
            <label class="field-label">{{ t('features.content.form.metaKeywords') || 'Meta Keywords' }}</label>
            <input type="text" v-model="content.meta_keywords" class="setting-input" placeholder="keyword1, keyword2" />
          </div>

          <div class="field-group">
            <label class="field-label">{{ t('features.content.form.ogImage') || 'OG Image' }}</label>
            <MediaPicker
              @selected="(media: { url?: string } | string | null) => content.og_image = (typeof media === 'object' ? media?.url : media) || null"
              :constraints="{ allowedExtensions: ['jpg', 'jpeg', 'png', 'webp'] }"
            >
              <template #trigger="{ open }">
                <div v-if="content.og_image" class="og-image-preview" @click="open">
                  <img :src="content.og_image" />
                  <div class="og-image-overlay">
                    <span>{{ t('common.actions.change') || 'Change' }}</span>
                  </div>
                </div>
                <button v-else type="button" class="og-image-btn" @click="open">
                  <ImageIcon class="w-3 h-3" />
                  {{ t('features.content.form.selectOgImage') || 'Select OG Image' }}
                </button>
              </template>
            </MediaPicker>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, inject, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import ImageIcon from 'lucide-vue-next/dist/esm/icons/image.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import Edit2 from 'lucide-vue-next/dist/esm/icons/pen-line.js';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import FileCheck from 'lucide-vue-next/dist/esm/icons/file-check.js';
import Tags from 'lucide-vue-next/dist/esm/icons/tags.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import MenuSquare from 'lucide-vue-next/dist/esm/icons/square-menu.js';
import MessageSquare from 'lucide-vue-next/dist/esm/icons/message-square.js';
import MediaPicker from '@/components/media/MediaPicker.vue';
import api from '@/services/api';
import type { BuilderInstance } from '@/types/builder';

// Define loose interfaces for Content as it can be dynamic
interface Tag {
  id?: number | string;
  name: string;
}

interface MenuItem {
  add_to_menu: boolean;
  menu_id: number | string;
  parent_id: number | string | null;
  title: string;
}

interface Content {
  id?: number | string;
  title?: string;
  slug?: string;
  status?: string;
  published_at?: string;
  type?: string;
  is_featured?: boolean;
  excerpt?: string;
  comment_status?: boolean;
  meta_title?: string;
  meta_description?: string;
  meta_keywords?: string;
  featured_image?: string | null;
  og_image?: string | null;
  category_id?: number | string | null;
  tags: Tag[];
  menu_item?: MenuItem;
  [key: string]: unknown;
}

interface MenuParentItem {
  id: number | string;
  parent_id: number | string | null;
  title?: string;
  label?: string;
  sort_order: number;
  children?: MenuParentItem[];
  depth?: number;
}

const { t } = useI18n();
const builder = inject<BuilderInstance>('builder');

// Safely access content
const content = computed(() => builder?.content.value as Content);

// Collapsible section states
const sections = ref({
  publishing: true,
  menu: false,
  taxonomy: true,
  image: false,
  excerpt: false,
  discussion: false,
  seo: false
});

// Menu handling
const selectedMenuId = ref<number | string>(content.value?.menu_item?.menu_id || '');
const menuParentItems = ref<MenuParentItem[]>([]);
const loadingParentItems = ref(false);

const toggleAddToMenu = () => {
  if (!content.value.menu_item) {
    content.value.menu_item = {
      add_to_menu: true,
      menu_id: '',
      parent_id: null,
      title: ''
    };
  } else {
    content.value.menu_item.add_to_menu = !content.value.menu_item.add_to_menu;
  }
};

const handleMenuChange = async () => {
  if (!selectedMenuId.value) {
    menuParentItems.value = [];
    return;
  }
  
  if (content.value.menu_item) {
    content.value.menu_item.menu_id = parseInt(selectedMenuId.value as string);
  }
  await fetchMenuParentItems(selectedMenuId.value);
};

const fetchMenuParentItems = async (menuId: number | string) => {
  if (!menuId) {
    menuParentItems.value = [];
    return;
  }
  
  loadingParentItems.value = true;
  try {
    const response = await api.get(`/admin/ja/menus/${menuId}/items`);
    const data = response.data?.data || response.data || [];
    const flatItems = Array.isArray(data) ? data : [];
    
    if (flatItems.length > 0 && !flatItems[0].depth) {
      menuParentItems.value = flattenTreeForSelect(buildTree(flatItems));
    } else {
      menuParentItems.value = flatItems;
    }
  } catch (error) {
    logger.error('Failed to fetch menu items:', error);
  } finally {
    loadingParentItems.value = false;
  }
};

const buildTree = (items: MenuParentItem[], parentId: number | string | null = null): MenuParentItem[] => {
  return items
    .filter(item => item.parent_id === parentId)
    .sort((a, b) => a.sort_order - b.sort_order)
    .map(item => ({
      ...item,
      children: buildTree(items, item.id)
    }));
};

const flattenTreeForSelect = (items: MenuParentItem[], depth = 0): MenuParentItem[] => {
  let result: MenuParentItem[] = [];
  items.forEach(item => {
    result.push({ ...item, depth });
    if (item.children) {
      result = result.concat(flattenTreeForSelect(item.children, depth + 1));
    }
  });
  return result;
};

// Initial load of parent items if menu_id is set
watch(() => content.value?.menu_item?.menu_id, (newVal) => {
  if (newVal && menuParentItems.value.length === 0) {
    selectedMenuId.value = newVal;
    fetchMenuParentItems(newVal);
  }
}, { immediate: true });

// Tag search logic
const tagQuery = ref('');
const tagSuggestions = computed(() => {
  if (!tagQuery.value || tagQuery.value.length < 2) return [];
  const query = tagQuery.value.toLowerCase();
  
  // Assuming builder.availableTags exists and is an array of Tags
  const availableTags = (builder?.availableTags || []) as Tag[];
  
  return availableTags.filter(tag => 
    tag.name.toLowerCase().includes(query) && 
    !content.value.tags.find(t => t.id === tag.id)
  ).slice(0, 5);
});

const addTag = (tag: Tag) => {
  if (!content.value.tags.find(t => t.id === tag.id)) {
    content.value.tags.push(tag);
  }
  tagQuery.value = '';
};

const addCustomTag = () => {
  if (!tagQuery.value) return;
  if (!content.value.tags.find(t => t.name.toLowerCase() === tagQuery.value.toLowerCase())) {
    content.value.tags.push({ name: tagQuery.value });
  }
  tagQuery.value = '';
};

const removeTag = (tag: Tag) => {
  content.value.tags = content.value.tags.filter(t => 
    tag.id ? t.id !== tag.id : t.name !== tag.name
  );
};

const handleTagSearch = () => {
  // Reactive via computed
};
</script>

<style scoped>
.page-settings-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
  max-height: 100%;
  overflow-y: auto;
  overflow-x: hidden;
  background: var(--builder-bg-secondary);
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
}

/* Custom Scrollbar for better visibility */
.page-settings-panel::-webkit-scrollbar {
  width: 6px;
}

.page-settings-panel::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
}

.page-settings-panel::-webkit-scrollbar-thumb {
  background: var(--builder-accent);
  opacity: 0.8;
  border-radius: 10px;
}

.page-settings-panel::-webkit-scrollbar-thumb:hover {
  background: var(--builder-accent-hover);
}

/* Ensure the container inside follows the height */
.settings-container {
  background: var(--builder-bg-primary);
  border-radius: 8px;
  margin: 12px;
  border: 1px solid var(--builder-border);
  flex-shrink: 0; /* Important: don't let it shrink to 0 */
}

.sidebar-section {
  border-bottom: 1px solid var(--builder-border);
}

.sidebar-section--last {
  border-bottom: none;
}

.section-header {
  width: 100%;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
}

.section-header:hover {
  background: var(--builder-bg-secondary);
}

.section-header-left {
  display: flex;
  align-items: center;
  gap: 8px;
}

.section-header-right {
  display: flex;
  align-items: center;
  gap: 8px;
}

.section-icon {
  padding: 6px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.section-icon--green { background: rgba(16, 185, 129, 0.1); color: rgb(16, 185, 129); }
.section-icon--indigo { background: rgba(99, 102, 241, 0.1); color: rgb(99, 102, 241); }
.section-icon--blue { background: rgba(59, 130, 246, 0.1); color: rgb(59, 130, 246); }
.section-icon--purple { background: rgba(168, 85, 247, 0.1); color: rgb(168, 85, 247); }
.section-icon--amber { background: rgba(245, 158, 11, 0.1); color: rgb(245, 158, 11); }
.section-icon--orange { background: rgba(249, 115, 22, 0.1); color: rgb(249, 115, 22); }
.section-icon--red { background: rgba(239, 68, 68, 0.1); color: rgb(239, 68, 68); }

.section-title-text {
  font-size: 13px;
  font-weight: 600;
  color: var(--builder-text-primary);
}

.section-chevron {
  width: 16px;
  height: 16px;
  color: var(--builder-text-muted);
  transition: transform 0.2s;
}

.section-content {
  padding: 16px;
  border-top: 1px solid var(--builder-border);
  background: var(--builder-bg-secondary);
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 12px;
}

.field-group:last-child {
  margin-bottom: 0;
}

.field-label {
  font-size: 11px;
  font-weight: 500;
  color: var(--builder-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

.setting-input,
.setting-select {
  width: 100%;
  padding: 8px 12px;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: 6px;
  color: var(--builder-text-primary);
  font-size: 13px;
  transition: all 0.2s;
}

.setting-input:focus,
.setting-select:focus {
  border-color: var(--builder-accent);
  box-shadow: 0 0 0 2px var(--builder-accent-alpha-10);
  outline: none;
}

.slug-input-wrapper {
  display: flex;
  align-items: center;
  gap: 4px;
}

.slug-prefix {
  font-size: 12px;
  color: var(--builder-text-muted);
  font-family: monospace;
  padding-left: 4px;
}

.slug-input {
  flex: 1;
  font-family: monospace;
}

/* Toggle Row */
.toggle-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: 8px;
  margin-bottom: 12px;
}

.toggle-row:last-child {
  margin-bottom: 0;
}

.toggle-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.toggle-label {
  font-size: 12px;
  font-weight: 500;
  color: var(--builder-text-primary);
}

.toggle-desc {
  font-size: 10px;
  color: var(--builder-text-muted);
}

/* Status Badge */
.status-badge {
  font-size: 10px;
  font-weight: 500;
  padding: 2px 8px;
  border-radius: 99px;
}

.status-badge--indigo {
  background: rgba(99, 102, 241, 0.1);
  color: rgb(99, 102, 241);
}

.status-badge--orange {
  background: rgba(249, 115, 22, 0.1);
  color: rgb(249, 115, 22);
}

/* Thumb preview */
.thumb-preview {
  width: 24px;
  height: 24px;
  border-radius: 4px;
  overflow: hidden;
  border: 1px solid var(--builder-border);
}

.thumb-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Tag Styles */
.tag-badge {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: 99px;
  font-size: 11px;
  color: var(--builder-text-primary);
}

.tag-remove {
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.5;
  transition: opacity 0.2s;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
}

.tag-remove:hover {
  opacity: 1;
  color: var(--red-500);
}

.tag-suggestions {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  z-index: 10;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: 6px;
  margin-top: 4px;
  box-shadow: var(--shadow-lg);
  max-height: 200px;
  overflow-y: auto;
}

.suggestion-item {
  padding: 8px 12px;
  font-size: 12px;
  cursor: pointer;
  transition: background 0.2s;
}

.suggestion-item:hover {
  background: var(--builder-bg-secondary);
  color: var(--builder-accent);
}

/* Media Styles */
.image-preview {
  position: relative;
  height: 140px;
  background: var(--builder-bg-primary);
  border: 2px dashed var(--builder-border);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  overflow: hidden;
  transition: all 0.2s;
}

.image-preview:hover {
  border-color: var(--builder-accent);
}

.image-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: var(--builder-text-muted);
  font-size: 12px;
}

.image-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
}

.image-preview:hover .image-overlay {
  opacity: 1;
}

.remove-image-btn {
  margin-top: 8px;
  font-size: 11px;
  color: var(--red-500);
  background: none;
  border: none;
  cursor: pointer;
  transition: color 0.2s;
}

.remove-image-btn:hover {
  color: var(--red-600);
}

/* OG Image */
.og-image-preview {
  position: relative;
  aspect-ratio: 1200 / 630;
  width: 100%;
  border-radius: 6px;
  overflow: hidden;
  border: 1px solid var(--builder-border);
  cursor: pointer;
}

.og-image-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.og-image-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
  color: white;
  font-size: 12px;
  font-weight: 500;
}

.og-image-preview:hover .og-image-overlay {
  opacity: 1;
}

.og-image-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 8px 12px;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: 6px;
  color: var(--builder-text-secondary);
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.og-image-btn:hover {
  border-color: var(--builder-accent);
  color: var(--builder-accent);
}

/* Toggle Switch Styles */
.switch-toggle {
  width: 36px;
  height: 20px;
  background: var(--builder-border);
  border-radius: 99px;
  position: relative;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
}

.switch-toggle--active {
  background: var(--builder-accent);
}

.switch-thumb {
  width: 16px;
  height: 16px;
  background: white;
  border-radius: 50%;
  position: absolute;
  top: 2px;
  left: 2px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.switch-toggle--active .switch-thumb {
  left: 18px;
}
</style>

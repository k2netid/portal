<template>
  <div class="templates-panel">
    <div class="panel-header-actions">
      <BaseInput 
        v-model="searchQuery"
        :placeholder="t('builder.panels.templates.searchPlaceholder')"
        class="search-input"
      >
        <template #prefix>
          <Search :size="14" />
        </template>
      </BaseInput>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="templates-loading">
      <div class="spinner"></div>
      <span>{{ t('builder.panels.templates.loading') }}</span>
    </div>

    <!-- List -->
    <template v-else>
      <div class="templates-list">
        <!-- Template Categories -->
        <div v-for="category in templateCategories" :key="category.id" class="template-category">
          <div class="category-header">
            <span class="category-name">{{ category.label }}</span>
            <span class="category-count">{{ getCategoryTemplates(category.id).length }}</span>
          </div>

          <div class="category-items">
            <div 
              v-for="template in getCategoryTemplates(category.id)" 
              :key="template.id" 
              class="template-item" 
              :class="{ 'template-item--active': currentPageId === template.id }"
              @click="selectTemplate(template.id)"
            >
              <div class="template-info">
                <span class="template-title">{{ template.name || template.title }}</span>
                <span class="template-rules" v-if="getTemplateRules(template)">
                  Rules: {{ getTemplateRules(template) }}
                </span>
                <span class="template-usage" v-else>No assignment rules set</span>
              </div>
              <div class="template-actions">
                <button class="action-btn" @click.stop="openSettings(template)" :title="t('builder.common.settings')">
                  <Settings :size="14" />
                </button>
                <button class="action-btn action-btn--delete" @click.stop="handleDelete(template)" :title="t('builder.common.delete')">
                  <Trash2 :size="14" />
                </button>
              </div>
            </div>

            <button class="add-template-btn" @click="handleCreate(category.id)">
              <Plus :size="14" />
              <span>{{ t('builder.panels.templates.addNew') }} {{ category.label }}</span>
            </button>
          </div>
        </div>
      </div>
    </template>

    <!-- Assignment Settings Modal -->
    <BaseModal 
        v-if="showSettingsModal"
        :is-open="showSettingsModal"
        :title="t('builder.panels.templates.assignmentTitle')"
        @close="showSettingsModal = false"
    >
        <div class="assignment-settings">
            <div class="setting-group">
                <label>{{ t('builder.panels.templates.applyTo') }}</label>
                <select v-model="editingRules.apply_to" class="select-input">
                    <option value="all">{{ t('builder.panels.templates.applyOptions.all') }}</option>
                    <option value="specific_type">{{ t('builder.panels.templates.applyOptions.specific_type') }}</option>
                    <option value="specific_category">{{ t('builder.panels.templates.applyOptions.specific_category') }}</option>
                    <option value="front_page">{{ t('builder.panels.templates.applyOptions.front_page') }}</option>
                </select>
            </div>

            <div v-if="editingRules.apply_to === 'specific_type'" class="setting-group">
                <label>{{ t('builder.panels.templates.contentType') }}</label>
                <select v-model="editingRules.type_id" class="select-input">
                    <option value="post">{{ t('builder.panels.templates.contentTypeOptions.post') }}</option>
                    <option value="page">{{ t('builder.panels.templates.contentTypeOptions.page') }}</option>
                    <option value="custom">{{ t('builder.panels.templates.contentTypeOptions.custom') }}</option>
                </select>
            </div>

            <div v-if="editingRules.apply_to === 'specific_category'" class="setting-group">
                <label>{{ t('builder.panels.templates.category') }}</label>
                <select v-model="editingRules.category_id" class="select-input">
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </div>

            <div class="modal-footer">
                <button class="cancel-btn" @click="showSettingsModal = false">{{ t('builder.common.cancel') }}</button>
                <button class="save-btn" @click="saveRules">{{ t('builder.common.save') }}</button>
            </div>
        </div>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, computed, inject, onMounted, reactive } from 'vue';
import { useI18n } from 'vue-i18n';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import { BaseInput, BaseModal } from '@/components/builder/ui';
import type { BuilderInstance, PageMetadata, Category } from '@/types/builder';

interface Template extends Omit<Partial<PageMetadata>, 'id'> {
  id: number | string;
  name?: string;
  title?: string;
  type?: string;
  template_type?: string;
  meta?: {
    template_type?: string;
    assignment_rules?: {
      apply_to: string;
      type_id?: string;
      category_id?: string | number;
    };
  };
}

const { t } = useI18n();
const builder = inject<BuilderInstance>('builder');

const loading = ref(false);
const searchQuery = ref('');
const templates = ref<Template[]>([]);
const currentPageId = computed(() => builder?.currentPageId?.value);
const categories = computed(() => (builder?.categories?.value || []) as Category[]);

const showSettingsModal = ref(false);
const activeEditingTemplate = ref<Template | null>(null);
const editingRules = reactive({
    apply_to: 'all',
    type_id: '',
    category_id: '' as string | number
});

const templateCategories = [
  { id: 'header', label: 'Headers' },
  { id: 'footer', label: 'Footers' },
  { id: 'single', label: 'Single Pages' },
  { id: 'archive', label: 'Archives' },
  { id: 'section', label: 'Global Sections' }
];

const fetchTemplates = async () => {
  if (!builder) return;
  loading.value = true;
  try {
    const response = await builder.fetchTemplates();
    templates.value = (response as Template[]) || [];
  } catch (error) {
    logger.error('Failed to fetch templates:', error);
  } finally {
    loading.value = false;
  }
};

const getCategoryTemplates = (categoryId: string) => {
  let filtered = templates.value.filter(t => t.type === categoryId || t.template_type === categoryId || (t.meta && t.meta.template_type === categoryId));
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(t => (t.name || t.title || '').toLowerCase().includes(query));
  }
  return filtered;
};

const getTemplateRules = (template: Template) => {
    const rules = template.meta?.assignment_rules;
    if (!rules) return null;
    if (rules.apply_to === 'all') return t('builder.panels.templates.applyOptions.all');
    if (rules.apply_to === 'specific_type') {
        const typeLabel = rules.type_id ? t(`builder.panels.templates.contentTypeOptions.${rules.type_id}`) : rules.type_id;
        return `${t('builder.panels.templates.contentType')}: ${typeLabel}`;
    }
    if (rules.apply_to === 'specific_category') {
        const cat = categories.value.find((c) => c.id === rules.category_id);
        return `${t('builder.panels.templates.category')}: ${cat?.name || rules.category_id}`;
    }
    if (rules.apply_to === 'front_page') return t('builder.panels.templates.applyOptions.front_page');
    return null;
};

const selectTemplate = (id: string | number) => {
  if (builder?.loadContent) {
      builder.loadContent(id);
  }
};

const handleCreate = async (type: string) => {
  const name = prompt(`Enter ${type} template name:`);
  if (name && builder) {
    try {
      await builder.createTemplate({ name, type });
      fetchTemplates();
    } catch (error) {
      logger.error('Failed to create template:', error);
    }
  }
};

const openSettings = (template: Template) => {
    activeEditingTemplate.value = template;
    const rules = template.meta?.assignment_rules || { apply_to: 'all', type_id: '', category_id: '' };
    editingRules.apply_to = rules.apply_to;
    editingRules.type_id = rules.type_id || '';
    editingRules.category_id = rules.category_id || '';
    showSettingsModal.value = true;
};

const saveRules = async () => {
    if (!activeEditingTemplate.value || !builder) return;
    try {
        const meta = { 
            ...(activeEditingTemplate.value.meta || {}), 
            assignment_rules: { ...editingRules } 
        };
        await builder.updateContentMeta(activeEditingTemplate.value.id, meta);
        showSettingsModal.value = false;
        fetchTemplates();
    } catch (error) {
        logger.error('Failed to save assignment rules:', error);
    }
};

const handleDelete = async (template: Template) => {
    if (!builder) return;
    const confirmed = await builder.confirm({
        title: 'Delete Template',
        message: 'Are you sure you want to delete this template?',
        type: 'delete'
    });
    if (confirmed) {
        try {
            await builder.deleteTemplate(template.id);
            fetchTemplates();
        } catch (error) {
            logger.error('Delete failed:', error);
        }
    }
};

onMounted(() => {
    fetchTemplates();
});
</script>

<style scoped>
.templates-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.panel-header-actions {
  padding: 16px;
  background: var(--builder-bg-primary);
  border-bottom: 1px solid var(--builder-border);
}

.templates-list {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.template-category {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.category-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 4px;
}

.category-name {
  font-size: 11px;
  font-weight: 700;
  color: var(--builder-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.category-count {
  font-size: 10px;
  padding: 2px 6px;
  background: var(--builder-bg-secondary);
  color: var(--builder-text-muted);
  border-radius: 10px;
}

.category-items {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.template-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  background: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.template-item:hover {
  border-color: var(--builder-accent);
  background: var(--builder-bg-tertiary);
}

.template-item--active {
  border-color: var(--builder-accent);
  background: var(--builder-bg-tertiary);
  box-shadow: 0 0 0 1px var(--builder-accent);
}

.template-info {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-width: 0;
}

.template-title {
  font-size: 12px;
  font-weight: 600;
  color: var(--builder-text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.template-rules {
    font-size: 10px;
    color: var(--builder-accent);
    font-weight: 500;
}

.template-usage {
  font-size: 10px;
  color: var(--builder-text-muted);
}

.template-actions {
  display: flex;
  gap: 4px;
  opacity: 0;
}

.template-item:hover .template-actions {
  opacity: 1;
}

.action-btn {
  padding: 4px;
  background: transparent;
  border: none;
  color: var(--builder-text-muted);
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s;
}

.action-btn:hover {
  color: var(--builder-accent);
  background: rgba(var(--builder-accent-rgb), 0.1);
}

.action-btn--delete:hover {
  color: var(--builder-error);
  background: rgba(var(--builder-error-rgb), 0.1);
}

.add-template-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 8px;
  background: transparent;
  border: 1px dashed var(--builder-border);
  border-radius: 6px;
  color: var(--builder-text-muted);
  font-size: 11px;
  cursor: pointer;
  transition: all 0.2s;
  margin-top: 4px;
}

.add-template-btn:hover {
  border-color: var(--builder-accent);
  color: var(--builder-accent);
  background: rgba(var(--builder-accent-rgb), 0.05);
}

.assignment-settings {
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.setting-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.setting-group label {
    font-size: 12px;
    font-weight: 600;
    color: var(--builder-text-secondary);
}

.select-input {
    width: 100%;
    height: 36px;
    padding: 0 12px;
    background: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    border-radius: 6px;
    color: var(--builder-text-primary);
    font-size: 13px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 8px;
}

.cancel-btn {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid var(--builder-border);
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
}

.save-btn {
    padding: 8px 16px;
    background: var(--builder-accent);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.templates-loading {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--builder-text-muted);
}

.spinner {
  width: 24px;
  height: 24px;
  border: 2px solid var(--builder-border);
  border-top-color: var(--builder-accent);
  border-radius: 50%;
  animation: rotate 0.8s linear infinite;
  margin-bottom: 12px;
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>

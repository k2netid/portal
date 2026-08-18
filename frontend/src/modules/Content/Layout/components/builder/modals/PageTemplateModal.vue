<template>
  <BaseModal
    :is-open="true"
    :title="$t('builder.pageTemplateModal.title') || 'Import Page Template'"
    :width="800"
    draggable
    placement="center"
    @close="$emit('close')"
  >
    <div class="modal-body-content">
      <div class="modal-content">
        <div class="template-header">
            <div class="info-alert">
                <AlertTriangle class="w-4 h-4" />
                <span>{{ $t('builder.pageTemplateModal.warning') || 'Warning: Importing a template will replace all current content.' }}</span>
            </div>
        </div>

        <div class="template-grid">
            <button 
            v-for="template in templates" 
            :key="template.id"
            class="template-card"
            @click="insertTemplate(template)"
            :title="template.description"
            >
            <div class="template-preview">
                <LayoutTemplate class="preview-icon" />
            </div>
            <div class="template-info">
                <h5 class="template-name">{{ template.name }}</h5>
                <p class="template-desc">{{ template.description }}</p>
            </div>
            </button>
        </div>
      </div>
    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue';
import { useI18n } from 'vue-i18n';
import { BaseModal } from '@/components/builder/ui';
import ModuleRegistry from '@/components/builder/core/ModuleRegistry';
import { pageTemplates } from '@/components/builder/templates/PageTemplates.js';
import LayoutTemplate from 'lucide-vue-next/dist/esm/icons/layout-template.js';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import type { BuilderInstance, BlockInstance } from '@/types/builder';

import type { PageTemplate } from '@/components/builder/templates/PageTemplates.js';

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'inserted'): void;
}>();

const builder = inject<BuilderInstance>('builder');
const { t } = useI18n();

const templates = computed(() => pageTemplates);

// Insert a template into the builder (REPLACES CONTENT)
const insertTemplate = async (template: PageTemplate) => {
  if (!builder || !template.factory) return;
  
  const confirmed = await builder.confirm({
    title: t('builder.modals.confirm.resetLayout'),
    message: t('builder.modals.confirm.resetLayoutDesc'),
    confirmText: t('builder.modals.confirm.confirm'),
    cancelText: t('builder.modals.confirm.cancel'),
    type: 'warning'
  });
  
  if (!confirmed) return;
  
  // Generate the page data (array of sections)
  const pageData = template.factory();
  
  // Deep clone and regenerate IDs
  const clonedPage: BlockInstance[] = JSON.parse(JSON.stringify(pageData));
  
  const regenerateIds = (block: BlockInstance) => {
    block.id = ModuleRegistry.generateId();
    if (block.children) {
      block.children.forEach(regenerateIds);
    }
  };
  
  clonedPage.forEach(section => regenerateIds(section));
  
  // Replace builder content
  builder.blocks.value = clonedPage;
  
  builder.takeSnapshot();
  
  // Select first section if exists
  if (builder.blocks.value && builder.blocks.value.length > 0) {
      builder.selectModule(builder.blocks.value[0].id);
  }
  
  emit('inserted');
  emit('close');
};
</script>

<style scoped>
.modal-body-content {
    padding: 24px;
}

.template-header {
    margin-bottom: 20px;
}

.info-alert {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px;
    background: rgba(234, 179, 8, 0.1); /* Amber/Yellow tint */
    border: 1px solid rgba(234, 179, 8, 0.3);
    border-radius: 6px;
    color: #ca8a04;
    font-size: 13px;
    font-weight: 500;
}

.template-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
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
    height: 100%;
}

.template-card:hover {
    border-color: var(--builder-accent);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.template-preview {
    height: 120px;
    background: linear-gradient(135deg, var(--builder-accent-light, rgba(32, 89, 234, 0.1)) 0%, var(--builder-bg-tertiary) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    border-bottom: 1px solid var(--builder-border);
}

.preview-icon {
    width: 32px;
    height: 32px;
    color: var(--builder-accent);
    opacity: 0.7;
}

.template-card:hover .preview-icon {
    opacity: 1;
    transform: scale(1.1);
    transition: transform 0.2s;
}

.template-info {
    padding: 16px;
}

.template-name {
    margin: 0 0 6px 0;
    font-size: 15px;
    font-weight: 600;
    color: var(--builder-text-primary);
}

.template-desc {
    margin: 0;
    font-size: 12px;
    color: var(--builder-text-muted);
    line-height: 1.5;
}
</style>

<template>
  <div class="portability-panel">
    <div class="tabs">
      <button 
        class="tab-btn" 
        :class="{ 'tab-btn--active': activeTab === 'export' }"
        @click="activeTab = 'export'"
      >
        {{ t('builder.panels.portability.tabs.export') }}
      </button>
      <button 
        class="tab-btn" 
        :class="{ 'tab-btn--active': activeTab === 'import' }"
        @click="activeTab = 'import'"
      >
        {{ t('builder.panels.portability.tabs.import') }}
      </button>
    </div>

    <div class="tab-content" v-if="activeTab === 'export'">
      <div class="content-wrapper">
        <p class="helper-text">{{ t('builder.panels.portability.export.helper') }}</p>
        <textarea class="json-area" readonly :value="exportJson"></textarea>
      </div>
      <div class="actions">
        <button class="action-btn" @click="copyToClipboard">
          <component :is="icons.Copy" :size="16" />
          <span>{{ t('builder.panels.portability.export.copy') }}</span>
        </button>
        <button class="action-btn action-btn--primary">
            {{ t('builder.panels.portability.export.file') }}
        </button>
      </div>
    </div>

    <div class="tab-content" v-else>
      <div class="content-wrapper">
        <p class="helper-text">{{ t('builder.panels.portability.import.helper') }}</p>
        <textarea class="json-area" v-model="importJson" :placeholder="t('builder.panels.portability.import.placeholder')"></textarea>
      </div>
      <div class="actions">
        <button class="action-btn action-btn--primary" @click="handleImport">
            {{ t('builder.panels.portability.import.button') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, inject } from 'vue';
import { useI18n } from 'vue-i18n';
import Copy from 'lucide-vue-next/dist/esm/icons/copy.js';
import type { BuilderInstance } from '@/types/builder';

const { t } = useI18n();
const icons = { Copy };
const builder = inject<BuilderInstance>('builder');
const activeTab = ref('export');
const importJson = ref('');

const exportJson = computed(() => {
  const blocks = builder?.blocks?.value || [];
  if (!blocks) return '';
  return JSON.stringify(blocks, null, 2);
});

const copyToClipboard = () => {
  navigator.clipboard.writeText(exportJson.value);
  // Toast notification would go here
};

const handleImport = () => {
    if(!importJson.value || !builder) return;
    try {
        const blocks = JSON.parse(importJson.value);
        if(Array.isArray(blocks)) {
             builder.blocks.value = blocks;
             builder.takeSnapshot(); // Add to history
             alert(t('builder.panels.portability.import.success'));
        }
    } catch(_e) {
        alert(t('builder.panels.portability.import.invalid'));
    }
};
</script>

<style scoped>
.portability-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.tabs {
  display: flex;
  border-bottom: 1px solid var(--builder-border);
}

.tab-btn {
  flex: 1;
  padding: 12px;
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  color: var(--builder-text-secondary);
  font-weight: 500;
  cursor: pointer;
}

.tab-btn:hover {
  color: var(--builder-text-primary);
}

.tab-btn--active {
  color: var(--builder-accent);
  border-bottom-color: var(--builder-accent);
}

.tab-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: var(--spacing-md);
  gap: var(--spacing-md);
  overflow: hidden;
}

.content-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
  overflow: hidden;
}

.helper-text {
  font-size: var(--font-size-sm);
  color: var(--builder-text-muted);
}

.json-area {
  flex: 1;
  width: 100%;
  padding: 8px;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: var(--border-radius-sm);
  color: var(--builder-text-primary);
  font-family: monospace;
  font-size: 11px;
  resize: none;
}

.json-area:focus {
  outline: none;
  border-color: var(--builder-accent);
}

.actions {
  display: flex;
  gap: var(--spacing-sm);
}

.action-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 8px;
  background: transparent;
  border: 1px solid var(--builder-border);
  border-radius: var(--border-radius-sm);
  color: var(--builder-text-primary);
  font-size: var(--font-size-sm);
  cursor: pointer;
}

.action-btn:hover {
  background: var(--builder-bg-primary);
  border-color: var(--builder-accent);
  color: var(--builder-accent);
}

.action-btn--primary {
  background: var(--builder-accent);
  border-color: var(--builder-accent);
  color: white;
}

.action-btn--primary:hover {
  background: var(--builder-accent-hover);
}
</style>

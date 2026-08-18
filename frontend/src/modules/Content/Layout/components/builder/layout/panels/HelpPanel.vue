<template>
  <div class="help-panel">
    <div class="help-section">
      <h4 class="section-title">{{ t('builder.panels.help.shortcuts') }}</h4>
      <div class="shortcuts-list">
        <div class="shortcut-item" v-for="sc in shortcuts" :key="sc.label">
          <span class="sc-label">{{ sc.label }}</span>
          <div class="sc-keys">
            <span class="key-badge" v-for="(k, i) in sc.keys" :key="i">{{ k }}</span>
          </div>
        </div>
      </div>
    </div>
    
    <div class="help-footer">
      <a href="#" class="help-link">{{ t('builder.panels.help.footer.docs') }}</a>
      <a href="#" class="help-link">{{ t('builder.panels.help.footer.support') }}</a>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Shortcut {
  label: string;
  keys: string[];
}

const shortcuts = computed<Shortcut[]>(() => [
  { label: t('builder.panels.help.labels.save'), keys: ['Ctrl', 'S'] },
  { label: t('builder.panels.help.labels.undo'), keys: ['Ctrl', 'Z'] },
  { label: t('builder.panels.help.labels.redo'), keys: ['Ctrl', 'Shift', 'Z'] },
  { label: t('builder.panels.help.labels.delete'), keys: ['Del'] },
  { label: t('builder.panels.help.labels.duplicate'), keys: ['Ctrl', 'D'] },
  { label: t('builder.panels.help.labels.copy'), keys: ['Ctrl', 'C'] },
  { label: t('builder.panels.help.labels.paste'), keys: ['Ctrl', 'V'] }
]);
</script>

<style scoped>
.help-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: var(--spacing-md);
}

.help-section {
  flex: 1;
}

.section-title {
  font-size: var(--font-size-sm);
  font-weight: 600;
  color: var(--builder-text-primary);
  margin-bottom: var(--spacing-md);
}

.shortcuts-list {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.shortcut-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.sc-label {
  font-size: var(--font-size-sm);
  color: var(--builder-text-secondary);
}

.sc-keys {
  display: flex;
  gap: 4px;
}

.key-badge {
  padding: 2px 6px;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: 4px;
  font-size: 10px;
  color: var(--builder-text-primary);
  font-family: monospace;
}

.help-footer {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-top: var(--spacing-lg);
  border-top: 1px solid var(--builder-border);
}

.help-link {
  font-size: var(--font-size-sm);
  color: var(--builder-accent);
  text-decoration: none;
}

.help-link:hover {
  text-decoration: underline;
}
</style>

<template>
  <div class="history-panel">
    <!-- History List -->
    <div class="history-list">
      <button 
        v-for="(snapshot, index) in history"
        :key="index"
        class="history-item"
        :class="{ 
          'history-item--active': index === historyIndex,
          'history-item--future': index > historyIndex 
        }"
        @click="jumpTo(index)"
      >
        <div class="history-icon">
          <Flag v-if="index === 0" :size="14" />
          <Clock v-else :size="14" />
        </div>
        <div class="history-info">
          <span class="history-label">{{ getLabel(index) }}</span>
          <span class="history-time">{{ index === 0 ? t('builder.panels.history.initial') : t('builder.panels.history.change') }}</span>
        </div>
        <div v-if="index === historyIndex" class="history-current">
          {{ t('builder.panels.history.current') }}
        </div>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue';
import { useI18n } from 'vue-i18n';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import Flag from 'lucide-vue-next/dist/esm/icons/flag.js';
import type { BuilderInstance } from '@/types/builder';

const { t } = useI18n();
// Inject builder state
const builder = inject<BuilderInstance>('builder');

// History State
const history = computed(() => builder?.history?.value || []);
const historyIndex = computed(() => builder?.historyIndex?.value ?? -1);

// Methods
const jumpTo = (index: number) => {
  // We need to support jump in useBuilder or just use undo/redo loop
  // For now, let's implement a simple jump wrapper if logic exists, 
  // or fall back to loop undo/redo
  if (!builder) return;
  
  const current = historyIndex.value;
  const diff = index - current;
  
  if (diff === 0) return;
  
  if (diff < 0) {
    // Undo X times
    for (let i = 0; i < Math.abs(diff); i++) builder.undo();
  } else {
    // Redo X times
    for (let i = 0; i < diff; i++) builder.redo();
  }
};

const getLabel = (index: number) => {
  if (index === 0) return t('builder.panels.history.sessionStart');
  return t('builder.panels.history.action', { index: index });
};
</script>

<style scoped>
.history-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.history-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-sm);
  background: transparent;
  border: none;
  border-radius: var(--border-radius-sm);
  cursor: pointer;
  text-align: left;
  transition: all 0.2s;
}

.history-item:hover {
  background: var(--builder-bg-primary);
  box-shadow: 0 0 0 1px var(--builder-border);
}

.history-item--active {
  background: var(--builder-bg-primary);
  border-left: 3px solid var(--builder-accent);
  box-shadow: 0 0 0 1px var(--builder-border);
}

.history-item--future {
  opacity: 0.5;
}

.history-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--builder-text-muted);
}

.history-item--active .history-icon {
  color: var(--builder-accent);
}

.history-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.history-label {
  font-size: var(--font-size-sm);
  color: var(--builder-text-primary);
  font-weight: 500;
}

.history-time {
  font-size: 10px;
  color: var(--builder-text-muted);
}

.history-current {
  font-size: 10px;
  color: white;
  background: var(--builder-accent);
  padding: 2px 6px;
  border-radius: 10px;
  font-weight: 600;
}
</style>

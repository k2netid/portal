<template>
  <div class="history-panel">
    <div class="history-tabs">
      <button class="history-tab" :class="{ 'is-active': tab === 'session' }" @click="tab = 'session'">
        {{ t('builder.panels.history.session', 'Session') }}
      </button>
      <button class="history-tab" :class="{ 'is-active': tab === 'saved' }" @click="tab = 'saved'; loadSaved()">
        {{ t('builder.panels.history.saved', 'Saved') }}
      </button>
    </div>

    <div v-if="tab === 'session'" class="history-list">
      <button
        v-for="(_, index) in sessionHistory"
        :key="index"
        class="history-item"
        :class="{
          'history-item--active': index === sessionIndex,
          'history-item--future': index > sessionIndex
        }"
        @click="jumpTo(index)"
      >
        <div class="history-icon">
          <Flag v-if="index === 0" :size="14" />
          <Clock v-else :size="14" />
        </div>
        <div class="history-info">
          <span class="history-label">{{ index === 0 ? t('builder.panels.history.sessionStart') : t('builder.panels.history.action', { index }) }}</span>
          <span class="history-time">{{ index === 0 ? t('builder.panels.history.initial') : t('builder.panels.history.change') }}</span>
        </div>
      </button>
    </div>

    <div v-else class="history-list">
      <p v-if="loadingSaved" class="history-empty">{{ t('builder.panels.history.loading', 'Loading…') }}</p>
      <p v-else-if="saved.length === 0" class="history-empty">{{ t('builder.panels.history.noSaved', 'No saved revisions yet. Save the page to snapshot the canvas.') }}</p>
      <button
        v-for="row in saved"
        :key="String(row.id)"
        class="history-item"
        @click="restoreSaved(String(row.id))"
      >
        <div class="history-icon">
          <Clock :size="14" />
        </div>
        <div class="history-info">
          <span class="history-label">{{ String(row.reason || t('builder.panels.history.savedRevision', 'Saved revision')) }}</span>
          <span class="history-time">{{ formatTime(row.created_at) }}</span>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, inject, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import Flag from 'lucide-vue-next/dist/esm/icons/flag.js';
import type { BuilderInstance } from '@/modules/Layout/types/builder';

const { t } = useI18n();
const builder = inject<BuilderInstance>('builder');

const tab = ref<'session' | 'saved'>('session');
const loadingSaved = ref(false);
const saved = ref<Array<Record<string, unknown>>>([]);

const sessionHistory = computed(() => builder?.history?.value || []);
const sessionIndex = computed(() => builder?.historyIndex?.value ?? -1);

const jumpTo = (index: number) => {
  if (!builder) return;
  const current = sessionIndex.value;
  const diff = index - current;
  if (diff === 0) return;
  if (diff < 0) {
    for (let i = 0; i < Math.abs(diff); i++) builder.undo();
  } else {
    for (let i = 0; i < diff; i++) builder.redo();
  }
};

const loadSaved = async () => {
  if (!builder?.fetchRevisions || !builder.content?.value?.id) {
    saved.value = [];
    return;
  }
  loadingSaved.value = true;
  try {
    saved.value = await builder.fetchRevisions();
  } catch {
    saved.value = [];
  } finally {
    loadingSaved.value = false;
  }
};

const restoreSaved = async (id: string) => {
  if (!builder?.restoreRevision) return;
  await builder.restoreRevision(id);
  await loadSaved();
};

const formatTime = (value: unknown): string => {
  if (typeof value !== 'string') return '';
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? value : date.toLocaleString();
};

onMounted(() => {
  if (builder?.content?.value?.id) {
    void loadSaved();
  }
});
</script>

<style scoped>
.history-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
}
.history-tabs {
  display: flex;
  gap: 4px;
  margin-bottom: var(--spacing-sm);
}
.history-tab {
  flex: 1;
  border: none;
  background: transparent;
  padding: 6px;
  font-size: 12px;
  cursor: pointer;
  color: var(--builder-text-muted);
}
.history-tab.is-active {
  color: var(--builder-text-primary);
  border-bottom: 2px solid var(--builder-accent);
}
.history-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.history-empty {
  font-size: 12px;
  color: var(--builder-text-muted);
  padding: var(--spacing-sm);
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
}
.history-item:hover {
  background: var(--builder-bg-primary);
}
.history-item--active {
  background: var(--builder-bg-primary);
  border-left: 3px solid var(--builder-accent);
}
.history-item--future {
  opacity: 0.5;
}
.history-icon {
  color: var(--builder-text-muted);
}
.history-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}
.history-label {
  font-size: var(--font-size-sm);
  font-weight: 500;
}
.history-time {
  font-size: 10px;
  color: var(--builder-text-muted);
}
</style>

<template>
  <div class="preset-popover-content">
    <!-- Current Active Preset -->
    <div class="preset-header">
      <div class="preset-check-wrapper">
         <Check :size="14" class="preset-check" />
      </div>
      <div class="preset-info">
        <div class="preset-name">{{ $t('builder.presets.defaultPreset') }}</div>
        <div class="preset-sub">{{ $t('builder.presets.basedOn', { name: 'Default' }) }}</div>
      </div>
    </div>
    
    <BaseDivider :margin="4" />

    <!-- Available Presets List -->
    <div class="presets-list-scroll" v-if="filteredPresets.length > 0">
      <div 
        v-for="preset in filteredPresets" 
        :key="preset.id"
        class="preset-item"
        @click="handleAction('apply', preset)"
      >
        <div class="preset-item-icon">
          <Layout v-if="preset.is_system" :size="14" />
          <div v-else class="user-preset-dot"></div>
        </div>
        <div class="preset-item-info">
          <div class="preset-item-name">{{ preset.name }}</div>
          <div v-if="preset.is_system" class="preset-tag">SYSTEM</div>
        </div>
      </div>
      <BaseDivider :margin="4" />
    </div>
    
    <!-- Actions -->
    <div class="dropdown-actions">
      <button class="dropdown-item primary-action" @click="handleAction('newFromCurrent')">
        <div class="action-icon-wrapper">
          <Save :size="14" class="action-icon" />
        </div>
        <span>{{ $t('builder.presets.newFromCurrent') }}</span>
      </button>
      
      <button class="dropdown-item accent-action" @click="handleAction('addNew')">
        <div class="action-icon-wrapper">
          <Plus :size="14" class="action-icon" />
        </div>
        <span>{{ $t('builder.presets.addNew') }}</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import Save from 'lucide-vue-next/dist/esm/icons/save.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Layout from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import BaseDivider from './BaseDivider.vue';

interface Props {
  type: string;
  fieldName?: string;
}

import type { BuilderInstance, BuilderPreset } from '@/types/builder';

const props = withDefaults(defineProps<Props>(), {
  fieldName: ''
});

const emit = defineEmits<{
  (e: 'action', payload: { type: string, data: BuilderPreset | null }): void;
}>();

const builder = inject<BuilderInstance>('builder');

const filteredPresets = computed(() => {
  if (!builder?.presets.value) return [];
  return builder.presets.value.filter((p: BuilderPreset) => p.type === props.type);
});

const handleAction = (type: string, data: BuilderPreset | null = null) => {
  emit('action', { type, data });
};
</script>

<style scoped>
.preset-popover-content {
  padding: 0;
}

.preset-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
}

.preset-check-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  background: var(--builder-accent-soft, rgba(var(--builder-accent-rgb), 0.1));
  border-radius: 50%;
  color: var(--builder-accent);
  flex-shrink: 0;
}

.preset-info {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.preset-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--builder-text-primary);
  line-height: 1.2;
}

.preset-sub {
  font-size: 11px;
  color: var(--builder-text-muted);
  opacity: 0.8;
}

.presets-list-scroll {
  max-height: 240px;
  overflow-y: auto;
  padding: 4px;
}

.preset-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 10px;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s;
}

.preset-item:hover {
  background: var(--builder-bg-secondary);
}

.preset-item-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  flex-shrink: 0;
  color: var(--builder-text-muted);
}

.user-preset-dot {
  width: 6px;
  height: 6px;
  background: var(--builder-accent);
  border-radius: 50%;
}

.preset-item-info {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.preset-item-name {
  font-size: 13px;
  color: var(--builder-text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.preset-tag {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--builder-accent);
  background: var(--builder-accent-soft, rgba(var(--builder-accent-rgb), 0.1));
  padding: 1px 4px;
  border-radius: 3px;
  letter-spacing: 0.02em;
}

.dropdown-actions {
  padding: 4px;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 8px 10px;
  text-align: left;
  background: none;
  border: none;
  font-size: 13px;
  font-weight: 500;
  color: var(--builder-text-secondary);
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s;
}

.dropdown-item:hover {
  background: var(--builder-bg-secondary);
  color: var(--builder-text-primary);
}

.action-icon-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  flex-shrink: 0;
}

.dropdown-item .action-icon {
  opacity: 0.6;
}

.primary-action {
  color: var(--builder-text-primary);
}

.accent-action {
  color: var(--builder-accent);
}

.accent-action .action-icon {
  color: var(--builder-accent);
}
</style>

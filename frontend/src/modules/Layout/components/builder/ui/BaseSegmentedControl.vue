<template>
  <div class="base-segmented-control" :class="{ 'is-full-width': fullWidth }">
    <button
      v-for="option in options"
      :key="String(option.value)"
      class="segment-item"
      :class="{ 'is-active': modelValue === option.value }"
      @click="$emit('update:modelValue', option.value)"
      :title="option.label"
    >
      <component v-if="option.icon" :is="option.icon" :size="14" />
      <span v-if="option.label && !option.iconOnly" class="segment-label">{{ option.label }}</span>
    </button>
  </div>
</template>

<script setup lang="ts">
import { type Component } from 'vue';

interface Option {
    label?: string;
    value: string | number | boolean;
    icon?: string | Component;
    iconOnly?: boolean;
}

interface Props {
  modelValue: string | number | boolean;
  options: Option[];
  fullWidth?: boolean;
}

defineProps<Props>();

defineEmits<{
  (e: 'update:modelValue', value: string | number | boolean): void;
}>();
</script>

<style scoped>
.base-segmented-control {
  display: inline-flex;
  background: var(--builder-bg-tertiary);
  border: 1px solid var(--builder-border);
  border-radius: var(--border-radius-sm);
  padding: 2px;
  gap: 2px;
}

.base-segmented-control.is-full-width {
  display: flex;
}

.segment-item {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 4px 8px;
  border-radius: 3px;
  border: none;
  background: transparent;
  color: var(--builder-text-muted);
  cursor: pointer;
  transition: all 0.2s;
  flex: 1;
  min-height: 24px;
}

.segment-item:hover {
  color: var(--builder-text-primary);
  background: rgba(255, 255, 255, 0.05);
}

.segment-item.is-active {
  background: var(--builder-accent);
  color: white;
  box-shadow: var(--shadow-sm);
}

.segment-label {
  font-size: 11px;
  font-weight: 500;
}
</style>

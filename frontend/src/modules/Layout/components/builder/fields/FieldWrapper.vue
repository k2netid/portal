<template>
  <div class="field-wrapper">
    <div class="field-header">
      <div class="field-label-group">
        <label class="field-label">{{ label }}</label>
      </div>
      
      <FieldActions 
        v-if="!hideActions"
        :label="label"
        :responsive="responsive"
        :show-responsive="showResponsive"
        :active-device="activeDevice"
        :show-dynamic-data="showDynamicData"
        :show-context-menu="showContextMenu"
        :show-reset="hasCustomValue"
        :show-info="!!description"
        :show-presets="showPresets"
        @reset="$emit('reset')"
        @reset-field="$emit('reset-field')"
        @responsive="$emit('responsive')"
        @select-dynamic-data="$emit('select-dynamic-data', $event)"
        @toggle-info="showInfo = $event"
        @assign-preset="$emit('assign-preset', $event)"
      />
    </div>
    
    <!-- Inline Info Panel -->
    <transition name="fade-slide">
      <div v-if="showInfo && description" class="field-info-panel">
          {{ description }}
      </div>
    </transition>
    
    <div class="field-control">
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

import FieldActions from './FieldActions.vue'



defineProps<{
  label: string;
  description?: string;
  responsive?: boolean;
  showResponsive?: boolean;
  showDynamicData?: boolean;
  showContextMenu?: boolean;
  hideActions?: boolean;
  hasCustomValue?: boolean;
  showPresets?: boolean;
  activeDevice?: string;
}>()

defineEmits(['reset', 'responsive', 'reset-field', 'assign-preset', 'select-dynamic-data'])

const showInfo = ref(false)


</script>

<style scoped>
.field-wrapper {
  margin-bottom: 16px;
  position: relative;
}

.field-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.field-label-group {
    display: flex;
    align-items: center;
    gap: 6px;
}

.field-label {
  font-size: 11px;
  font-weight: 500;
  color: var(--builder-text-secondary);
}

.field-info-icon {
  color: var(--builder-text-muted);
  transition: color 0.2s;
}

.field-info-icon:hover {
  color: var(--builder-text-primary);
}

.field-tooltip {
    background-color: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    color: var(--builder-text-primary);
    padding: 8px 12px;
    font-size: 12px;
    border-radius: 4px;
    margin-bottom: 8px;
    line-height: 1.4;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.field-actions {
  display: flex;
  align-items: center;
  gap: 4px;
  opacity: 0; 
  transition: opacity 0.2s;
  flex-shrink: 0;
}

.field-wrapper:hover .field-actions,
.field-header:hover .field-actions,
.field-actions.is-active {
    opacity: 1;
}

.field-info-panel {
    background-color: var(--builder-bg-secondary);
    border-left: 3px solid var(--builder-accent);
    padding: 10px 14px;
    margin-bottom: 12px;
    font-size: 12px;
    line-height: 1.6;
    color: var(--builder-text-secondary);
    border-radius: 0 4px 4px 0;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.2s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}

.action-icon {
    color: var(--builder-text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-icon:hover {
    color: var(--builder-text-primary);
}

/* Dropdown Menu */
.field-menu {
    position: absolute;
    top: 100%;
    right: 0;
    width: 240px;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    box-shadow: var(--shadow-lg);
    z-index: 60; /* Higher than floaters */
    padding: 4px 0;
}

.dynamic-data-menu {
    width: 260px;
}

.context-menu {
    width: 280px;
    background-color: var(--builder-bg-tertiary, #1f2937);
    border: 1px solid var(--builder-border, #374151);
}

.dd-header {
    padding: 8px 12px;
    font-size: 11px;
    font-weight: 600;
    color: var(--builder-text-muted);
    text-transform: uppercase;
}

.dd-action {
    padding: 0 12px 8px 12px;
}

.dd-btn-primary {
    display: block;
    width: 100%;
    background-color: var(--builder-accent);
    color: white;
    border: none;
    padding: 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    text-align: center;
    transition: background-color 0.15s;
}

.dd-btn-primary:hover {
    background-color: var(--builder-accent-hover);
}

.menu-item {
    padding: 8px 12px;
    font-size: 12px;
    color: var(--builder-text-primary);
    cursor: pointer;
    transition: background-color 0.15s;
}

.menu-item:hover {
    background-color: var(--builder-bg-secondary);
}

.menu-item.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.menu-divider {
    height: 1px;
    background-color: var(--builder-border);
    margin: 4px 0;
}
</style>

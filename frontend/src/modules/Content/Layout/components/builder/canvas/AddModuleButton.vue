<template>
  <div class="add-btn-wrapper" :class="{ 'add-btn-wrapper--floating': floating }">
    <button 
      class="add-module-btn"
      :class="[
        `add-module-btn--${type}`,
        { 'add-module-btn--circular': circular }
      ]"
      @click.stop="$emit('click')"
      :title="t('builder.actions.add', { name: label })"
    >
      <component :is="icons.Plus" :size="type === 'module' ? 14 : 16" />
    </button>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js'

const icons = { Plus }

interface Props {
  type?: 'section' | 'row' | 'column' | 'module';
  floating?: boolean;
  circular?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  type: 'module',
  floating: false,
  circular: false
})

defineEmits<{
  (e: 'click'): void;
}>()

const { t } = useI18n()

const label = computed(() => {
  const key = props.type === 'module' ? 'builder.module' : `builder.modules.${props.type}`
  return t(key)
})
</script>

<style scoped>
.add-btn-wrapper {
  display: flex;
  justify-content: center;
  width: 100%;
  pointer-events: none;
}

/* Floating mode for Section/Row (attached to bottom center) */
.add-btn-wrapper--floating {
  position: absolute;
  bottom: -15px; /* Fully centered on the border line */
  left: 50%;
  transform: translateX(-50%);
  z-index: 100001; /* Above the fullscreen builder (99999) */
  width: auto;
}

.add-module-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  pointer-events: auto; /* Re-enable clicks */
  transition: transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 2px 5px rgba(0,0,0,0.15);
  color: white;
}

.add-module-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Section: Blue */
.add-module-btn--section {
  background-color: var(--builder-section);
}

/* Row: Green */
.add-module-btn--row {
  background-color: var(--builder-row);
}

/* Column/Module: Gray/Standard */
.add-module-btn--column,
.add-module-btn--module {
  background-color: var(--builder-grey);
  width: 100%;
  border-radius: 4px;
  height: 24px;
}

/* Floating overrides for Column/Module to be circular */
.add-btn-wrapper--floating .add-module-btn--column {
    background-color: var(--builder-column);
    width: 28px;
    height: 28px;
    border-radius: 50%;
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
}

/* Floating overrides for Module to be circular (Consistent shape) */
.add-btn-wrapper--floating .add-module-btn--module {
    background-color: var(--builder-module, #4b5563);
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: none;
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    color: white;
}

.add-btn-wrapper--floating .add-module-btn--module:hover {
    background-color: var(--builder-module-hover, #374151);
    transform: scale(1.1);
    border-color: transparent;
}

/* Standard (Non-floating) Module Button */
.add-module-btn--module:not(.add-btn-wrapper--floating &) {
    background-color: var(--builder-bg-tertiary);
    color: var(--builder-text-secondary);
    box-shadow: none;
    border: 1px dashed var(--builder-border);
}

.add-module-btn--module:not(.add-btn-wrapper--floating &):hover {
    background-color: var(--builder-bg-secondary);
    color: var(--builder-text-primary);
    border-color: var(--builder-accent);
    transform: none;
}

/* Circular variant (Divi-style module adder) */
.add-module-btn--circular {
    width: 32px !important;
    height: 32px !important;
    border-radius: 50% !important;
    /* Use background from type if available */
    color: white !important;
    border: 2px solid white !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2) !important;
    z-index: 10;
}

.add-module-btn--circular:not(.add-module-btn--section):not(.add-module-btn--row):not(.add-module-btn--column) {
    background-color: var(--builder-bg-topbar, #1a1e25) !important;
}

.add-module-btn--circular:hover {
    transform: scale(1.1) !important;
    background-color: var(--builder-accent) !important;
}
</style>

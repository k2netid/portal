<template>
  <div class="css-field">
    <BaseSegmentedControl 
      v-model="activeTab" 
      :options="tabs"
      class="mb-4"
    />

    <!-- Free-Form CSS Tab -->
    <div v-if="activeTab === 'free'" class="tab-content">
      <div class="info-box mb-4">
        {{ $t('builder.advanced.css.info', { selector: 'selector', example: 'selector h1 { color: red; }' }) }}
      </div>
      <div class="control-row">
        <BaseLabel class="mb-2">CSS</BaseLabel>
        <div class="editor-container">
          <textarea 
            v-model="localValue.free" 
            class="builder-textarea font-mono text-xs"
            placeholder="selector { ... }"
            rows="8"
          ></textarea>
          <div class="editor-toolbar">
            <IconButton :icon="Plus" size="sm" />
            <IconButton :icon="Power" size="sm" />
            <IconButton :icon="ArrowDownUp" size="sm" />
            <div class="flex-1"></div>
            <IconButton :icon="BrainCircuit" size="sm" />
          </div>
        </div>
      </div>
    </div>

    <!-- Module Elements Tab -->
    <div v-if="activeTab === 'elements'" class="tab-content">
      <div class="info-box mb-4">
        {{ $t('builder.advanced.css.elementsInfo', { example: 'color: red instead of h1 { color: red; }' }) }}
      </div>
      
      <div v-for="element in elements" :key="element.id" class="element-row mb-4">
        <BaseLabel class="mb-2">{{ element.label }}</BaseLabel>
        <div class="editor-container">
          <textarea 
            v-model="localValue.elements[element.id]" 
            class="builder-textarea font-mono text-xs"
            rows="4"
          ></textarea>
          <div class="editor-toolbar">
            <IconButton :icon="Plus" size="sm" />
            <IconButton :icon="Power" size="sm" />
            <IconButton :icon="ArrowDownUp" size="sm" />
            <div class="flex-1"></div>
            <IconButton :icon="BrainCircuit" size="sm" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, reactive, computed } from 'vue'
import type { BlockInstance, SettingDefinition } from '@/types/builder'
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Power from 'lucide-vue-next/dist/esm/icons/power.js';
import ArrowDownUp from 'lucide-vue-next/dist/esm/icons/arrow-down-up.js';
import BrainCircuit from 'lucide-vue-next/dist/esm/icons/brain-circuit.js';
import { BaseSegmentedControl, BaseLabel, IconButton } from '@/components/builder/ui'

interface CSSState {
  free: string;
  elements: Record<string, string>;
}

const props = defineProps<{
  field?: SettingDefinition;
  value: CSSState;
  module: BlockInstance;
}>()

const emit = defineEmits(['update:value'])

const activeTab = ref('free')
const tabs = [
  { label: 'Free-Form CSS', value: 'free' },
  { label: 'Module Elements', value: 'elements' }
]

const localValue = reactive<CSSState>({
  free: props.value?.free || '',
  elements: props.value?.elements || {}
})

// Elements should ideally be dynamic based on module type
const elements = computed(() => {
  const base = [
    { id: 'before', label: 'Before' },
    { id: 'main', label: 'Main Element' },
    { id: 'after', label: 'After' }
  ]
  
  if (props.module.type === 'accordion' || props.module.type === 'accordion_item') {
    return [
      ...base,
      { id: 'open_toggle', label: 'Open Toggle' },
      { id: 'toggle_title', label: 'Toggle Title' },
      { id: 'toggle_icon', label: 'Toggle Icon' },
      { id: 'toggle_content', label: 'Toggle Content' }
    ]
  }
  
  return base
})

watch(() => props.value, (newVal) => {
  if (newVal) {
    localValue.free = newVal.free || ''
    localValue.elements = newVal.elements || {}
  }
}, { deep: true })

watch(localValue, (newVal) => {
  emit('update:value', { ...newVal })
}, { deep: true })

</script>

<style scoped>
.css-field {
  width: 100%;
}

.info-box {
  background: rgba(245, 158, 11, 0.05);
  border-left: 3px solid #f59e0b;
  padding: var(--spacing-sm) var(--spacing-md);
  font-size: 11px;
  line-height: 1.4;
  color: #f59e0b;
}

.info-box .selector {
  font-weight: 700;
}

.editor-container {
  background: #1e293b;
  border: 1px solid var(--builder-border);
  border-radius: var(--radius-md);
  overflow: hidden;
}

.builder-textarea {
  width: 100%;
  border: none;
  background: transparent;
  color: #f8fafc;
  padding: var(--spacing-sm);
  outline: none;
  resize: vertical;
}

.editor-toolbar {
  display: flex;
  align-items: center;
  gap: var(--spacing-xs);
  padding: 4px var(--spacing-sm);
  background: #0f172a;
  border-top: 1px solid var(--builder-border);
}

.flex-1 {
  flex: 1;
}
</style>

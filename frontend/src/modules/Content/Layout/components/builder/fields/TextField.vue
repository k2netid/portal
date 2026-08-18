<template>
  <div class="text-field-container">
    <BaseInput
      v-if="!isDynamic"
      v-model="internalValue"
      :placeholder="value ? '' : (placeholderValue || field.placeholder || '')"
    />
    
    <div v-else class="dynamic-value-tag">
      <div class="tag-content">
        <Database :size="12" />
        <span class="tag-label">{{ dynamicLabel }}</span>
      </div>
      <button class="tag-clear" @click="clearDynamic" title="Clear dynamic data">
        <X :size="12" />
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Database from 'lucide-vue-next/dist/esm/icons/database.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import { BaseInput } from '@/components/builder/ui'
import type { SettingDefinition } from '@/types/builder'

const props = defineProps<{
  field: SettingDefinition;
  value?: string;
  placeholderValue?: string | null;
}>()

const emit = defineEmits(['update:value'])

const internalValue = computed({
  get: () => props.value || '',
  set: (val: string) => emit('update:value', val)
})

const isDynamic = computed(() => {
  return !!props.value && typeof props.value === 'string' && props.value.startsWith('@dynamic:')
})

const dynamicLabel = computed(() => {
  if (!isDynamic.value || !props.value) return ''
  const tag = (props.value as string).replace('@dynamic:', '')
  // Convert tag like 'post_title' to 'Post Title'
  return tag.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
})

const clearDynamic = () => {
  emit('update:value', '')
}
</script>

<style scoped>
.text-field-container {
  width: 100%;
}

.dynamic-value-tag {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 6px 10px;
  background: var(--builder-bg-tertiary);
  border: 1px solid var(--builder-border);
  border-radius: var(--border-radius-sm);
  color: var(--builder-accent);
  font-size: var(--font-size-sm);
}

.tag-content {
  display: flex;
  align-items: center;
  gap: var(--spacing-xs);
  font-weight: 500;
}

.tag-label {
    max-width: 160px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.tag-clear {
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  color: var(--builder-text-muted);
  cursor: pointer;
  padding: 2px;
  border-radius: 4px;
  transition: all 0.2s;
}

.tag-clear:hover {
  background: var(--builder-bg-secondary);
  color: var(--builder-text-primary);
}
</style>

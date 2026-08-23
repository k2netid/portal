<template>
  <div class="number-field">
    <BaseInput
      type="number"
      v-model="internalValue"
      :min="field.min"
      :max="field.max"
      :step="field.step || 1"
      :placeholder="value !== undefined && value !== null ? '' : (placeholderValue !== null && placeholderValue !== undefined ? String(placeholderValue) : '')"
      class="number-input-base"
    >
      <template #suffix v-if="showUnit">
        <select
          class="unit-select-minimal"
          :value="unit"
          @change="$emit('update:unit', ($event.target as HTMLSelectElement).value)"
        >
          <option v-for="u in units" :key="u" :value="u">{{ u }}</option>
        </select>
      </template>
    </BaseInput>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { CSS_UNITS } from '@/components/builder/core/constants'
import { BaseInput } from '@/components/builder/ui'
import type { SettingDefinition } from '@/types/builder'

const props = defineProps<{
  field: SettingDefinition;
  value?: number | null;
  unit?: string;
  placeholderValue?: number | null;
}>()

const emit = defineEmits(['update:value', 'update:unit'])

const internalValue = computed({
  get: () => props.value ?? '',
  set: (val: string | number) => emit('update:value', val === '' ? null : Number(val))
})

const showUnit = computed(() => (props.field.unit as unknown) !== false)
const units = computed(() => (props.field.units as string[]) || CSS_UNITS)
</script>

<style scoped>
.number-field {
  width: 100%;
}

.unit-select-minimal {
  background: transparent;
  border: none;
  color: var(--builder-text-secondary);
  font-size: 11px;
  cursor: pointer;
  outline: none;
  padding: 0 4px;
}

.unit-select-minimal:hover {
  color: var(--builder-text-primary);
}

/* Hide number arrows */
:deep(.base-input::-webkit-outer-spin-button),
:deep(.base-input::-webkit-inner-spin-button) {
  -webkit-appearance: none;
  margin: 0;
}

:deep(.base-input[type=number]) {
  -moz-appearance: textfield;
  appearance: textfield;
}
</style>

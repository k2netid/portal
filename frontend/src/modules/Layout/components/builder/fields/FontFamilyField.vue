<template>
  <div class="font-family-field">
    <SelectField 
      :field="fontField" 
      :value="value || ''" 
      :placeholder-value="placeholderValue"
      searchable
      @update:value="val => emit('update:value', val)"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import type { BuilderInstance, SettingDefinition } from '@/types/builder'
import SelectField from '@/components/builder/fields/SelectField.vue'

const props = defineProps<{
  field?: SettingDefinition;
  value?: string;
  placeholderValue?: string;
}>()

const emit = defineEmits(['update:value'])

const builder = inject<BuilderInstance>('builder')

// Standard Web-Safe Fonts + Some Popular Google Fonts
const standardFonts = [
  { label: 'Inherit', value: 'inherit' },
  { label: 'System Default', value: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif' },
  { label: 'Inter', value: '"Inter", sans-serif' },
  { label: 'Instrument Sans', value: '"Instrument Sans", sans-serif' },
  { label: 'Arial', value: 'Arial, sans-serif' },
  { label: 'Georgia', value: 'Georgia, serif' },
  { label: 'Helvetica', value: 'Helvetica, sans-serif' },
  { label: 'Times New Roman', value: '"Times New Roman", serif' },
  { label: 'Verdana', value: 'Verdana, sans-serif' },
  { label: 'Monospace', value: 'monospace' }
]

const fontField = computed(() => {
  // Combine standard fonts with global variables if available
  const globalFonts = builder?.globalVariables.globalFonts.value || []
  const customFonts = globalFonts.map((f) => ({
    label: f.name || f.family,
    value: f.family || '',
    group: 'Global Fonts'
  }))

  const options = [
    ...standardFonts.map(f => ({ ...f, group: 'Standard Fonts' })),
    ...customFonts
  ]

  return {
    ...props.field,
    type: 'select',
    options
  } as SettingDefinition
})
</script>

<style scoped>
.font-family-field {
  width: 100%;
}
</style>

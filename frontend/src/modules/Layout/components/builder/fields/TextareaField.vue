<template>
  <BaseInput
    v-model="internalValue"
    type="textarea"
    :placeholder="field.placeholder || ''"
    :rows="field.rows || 4"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { BaseInput } from '@/modules/Layout/components/builder/ui'
import type { SettingDefinition } from '@/modules/Layout/types/builder'

const props = defineProps<{
  field: SettingDefinition;
  value?: string;
}>()

const emit = defineEmits(['update:value'])

const internalValue = computed({
  get: () => props.value || '',
  set: (val: string) => emit('update:value', val)
})
</script>

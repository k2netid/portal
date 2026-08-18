<template>
  <BaseToggle 
    :model-value="!!value" 
    :label="''"
    @update:model-value="$emit('update:value', $event)"
  />
</template>

<script setup lang="ts">
import { BaseToggle } from '@/components/builder/ui'
import type { SettingDefinition } from '@/types/builder'

defineProps<{
  field: SettingDefinition;
  value?: boolean;
}>()

defineEmits<{
  (e: 'update:value', val: boolean): void;
}>()
</script>

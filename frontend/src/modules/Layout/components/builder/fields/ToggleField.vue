<template>
  <BaseToggle 
    :model-value="!!value" 
    :label="''"
    @update:model-value="$emit('update:value', $event)"
  />
</template>

<script setup lang="ts">
import { BaseToggle } from '@/modules/Layout/components/builder/ui'
import type { SettingDefinition } from '@/modules/Layout/types/builder'

defineProps<{
  field: SettingDefinition;
  value?: boolean;
}>()

defineEmits<{
  (e: 'update:value', val: boolean): void;
}>()
</script>

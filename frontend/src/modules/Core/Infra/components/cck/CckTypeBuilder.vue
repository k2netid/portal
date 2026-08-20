<template>
  <DataModelBuilder v-bind="$attrs" :model-value="modelValue" :current-type-slug="currentTypeSlug" @update:model-value="$emit('update:modelValue', $event)" />
</template>

<script setup lang="ts">
import DataModelBuilder from '../models/DataModelBuilder.vue';
import type { DataModelFieldDefinition } from '../../services/dataModelService';

defineProps<{
    modelValue: DataModelFieldDefinition[];
    currentTypeSlug?: string;
}>();

defineEmits<{
    'update:modelValue': [value: DataModelFieldDefinition[]];
}>();
</script>

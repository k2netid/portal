<template>
  <RadixSelectValue v-bind="props" />
</template>

<script setup lang="ts">
import { SelectValue as RadixSelectValue, type SelectValueProps } from 'radix-vue';

const props = defineProps<SelectValueProps>();
</script>

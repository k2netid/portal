<template>
  <SelectValue :placeholder="placeholder" />
</template>

<script setup lang="ts">
import { SelectValue, type SelectValueProps } from 'radix-vue';

defineProps<SelectValueProps>();
</script>

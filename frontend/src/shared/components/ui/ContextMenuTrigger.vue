<template>
  <ContextMenuTrigger v-bind="forwarded">
    <slot />
  </ContextMenuTrigger>
</template>

<script setup lang="ts">
import { ContextMenuTrigger, type ContextMenuTriggerProps, useForwardProps } from 'radix-vue';

const props = defineProps<ContextMenuTriggerProps>();
const forwarded = useForwardProps(props);
</script>

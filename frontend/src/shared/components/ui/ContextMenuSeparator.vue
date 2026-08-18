<template>
  <ContextMenuSeparator
    v-bind="forwarded"
    :class="cn('h-px bg-border/40 my-1 mx-2', props.class)"
  />
</template>

<script setup lang="ts">
import { ContextMenuSeparator, type ContextMenuSeparatorProps, useForwardProps } from 'radix-vue';
import { cn } from '@/shared/utils/lib-utils';

const props = defineProps<ContextMenuSeparatorProps & { class?: string }>();
const forwarded = useForwardProps(props);
</script>

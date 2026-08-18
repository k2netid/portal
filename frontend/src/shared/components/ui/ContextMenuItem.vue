<template>
  <ContextMenuItem
    v-bind="forwarded"
    :class="cn(
      'relative flex cursor-default select-none items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm outline-none transition-colors duration-200 focus:bg-primary/5 focus:text-primary data-[disabled]:pointer-events-none data-[disabled]:opacity-50 group mx-1 w-[calc(100%-8px)]',
      inset && 'pl-8',
      props.class
    )"
  >
    <slot />
  </ContextMenuItem>
</template>

<script setup lang="ts">
import { ContextMenuItem, type ContextMenuItemEmits, type ContextMenuItemProps, useForwardPropsEmits } from 'radix-vue';
import { cn } from '@/shared/utils/lib-utils';

const props = defineProps<ContextMenuItemProps & { class?: string; inset?: boolean }>();
const emits = defineEmits<ContextMenuItemEmits>();

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
  <ContextMenuPortal>
    <ContextMenuContent
      v-bind="forwarded"
      :class="cn(
        '!z-[100010] min-w-[200px] overflow-hidden rounded-2xl border border-border/40 bg-background/95 p-1.5 shadow-2xl backdrop-blur-xl animate-in fade-in zoom-in-95 ring-1 ring-black/5',
        props.class
      )"
    >
      <slot />
    </ContextMenuContent>
  </ContextMenuPortal>
</template>

<script setup lang="ts">
import { ContextMenuContent, type ContextMenuContentEmits, type ContextMenuContentProps, ContextMenuPortal, useForwardPropsEmits } from 'radix-vue';
import { cn } from '@/shared/utils/lib-utils';

const props = defineProps<ContextMenuContentProps & { class?: string }>();
const emits = defineEmits<ContextMenuContentEmits>();

const forwarded = useForwardPropsEmits(props, emits);
</script>

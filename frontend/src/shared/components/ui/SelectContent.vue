<template>
  <SelectPortal>
    <SelectContent
      v-bind="delegatedProps"
      :class="cn(
        'relative !z-[100005] min-w-[8rem] overflow-hidden rounded-xl border border-border/60 bg-popover text-popover-foreground shadow-xl',
        props.class
      )"
    >
      <SelectViewport class="p-1 max-h-60 overflow-y-auto">
        <slot />
      </SelectViewport>
    </SelectContent>
  </SelectPortal>
</template>

<script setup lang="ts">
import { SelectPortal, SelectContent, type SelectContentProps, SelectViewport } from 'radix-vue';
import { cn } from '@/shared/utils/lib-utils';
import { computed, type HTMLAttributes } from 'vue';

const props = withDefaults(defineProps<SelectContentProps & { class?: HTMLAttributes['class'] }>(), {
  position: 'popper',
  class: undefined,
});

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});
</script>

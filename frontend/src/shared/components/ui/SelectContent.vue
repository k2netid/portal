<template>
  <SelectPortal>
    <SelectContent
      v-bind="forwarded"
      :class="
        cn(
          'relative !z-[100080] min-w-[8rem] max-h-[var(--radix-select-content-available-height,20rem)] overflow-hidden rounded-xl border border-border/80 bg-popover text-popover-foreground shadow-2xl ring-1 ring-black/5 dark:ring-white/10 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=top]:slide-in-from-bottom-2',
          position === 'popper' &&
            'data-[side=bottom]:translate-y-1 data-[side=left]:-translate-x-1 data-[side=right]:translate-x-1 data-[side=top]:-translate-y-1',
          props.class
        )
      "
    >
      <SelectViewport
        :class="
          cn(
            'p-1 max-h-[min(var(--radix-select-content-available-height,16rem),16rem)] overflow-y-auto custom-scrollbar',
            position === 'popper' &&
              'h-[var(--radix-select-trigger-height)] w-full min-w-[var(--radix-select-trigger-width)]'
          )
        "
      >
        <slot />
      </SelectViewport>
    </SelectContent>
  </SelectPortal>
</template>

<script setup lang="ts">
import {
  SelectPortal,
  SelectContent,
  type SelectContentEmits,
  type SelectContentProps,
  SelectViewport,
  useForwardPropsEmits,
} from 'radix-vue';
import { cn } from '@/shared/utils/lib-utils';
import { computed, type HTMLAttributes } from 'vue';

const props = withDefaults(
  defineProps<SelectContentProps & { class?: HTMLAttributes['class'] }>(),
  {
    position: 'popper',
    side: 'bottom',
    sideOffset: 6,
    avoidCollisions: true,
    collisionPadding: 16,
    class: undefined,
  }
);
const emits = defineEmits<SelectContentEmits>();

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

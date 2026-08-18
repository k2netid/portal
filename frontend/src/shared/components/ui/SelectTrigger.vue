<template>
  <RadixSelectTrigger
    v-bind="forwarded"
    data-slot="select-trigger"
    :class="cn(
      'flex h-8 w-full items-center justify-between rounded-lg border border-border/50 bg-transparent px-2.5 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50 disabled:cursor-not-allowed disabled:opacity-50 placeholder:text-muted-foreground',
      props.class,
    )"
  >
    <slot />
    <SelectIcon as-child>
      <ChevronDown class="h-4 w-4 opacity-50" />
    </SelectIcon>
  </RadixSelectTrigger>
</template>

<script setup lang="ts">
import { SelectTrigger as RadixSelectTrigger, type SelectTriggerProps, SelectIcon } from 'radix-vue';
import { ChevronDown } from 'lucide-vue-next';
import { cn } from '@/shared/utils/lib-utils';
import { computed, useAttrs, type HTMLAttributes } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps<SelectTriggerProps & { class?: HTMLAttributes['class'] }>();
const attrs = useAttrs();

const forwarded = computed(() => {
  const { class: _, ...delegated } = props;
  return { ...delegated, ...attrs };
});
</script>

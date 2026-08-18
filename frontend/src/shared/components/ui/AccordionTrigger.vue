<template>
  <AccordionHeader class="flex">
    <AccordionTrigger
      v-bind="delegatedProps"
      :class="cn(
        'flex flex-1 items-center justify-between py-4 font-medium transition-colors hover:underline [&[data-state=open]>svg]:rotate-180',
        props.class
      )"
    >
      <slot />
      <ChevronDown class="h-4 w-4 shrink-0 transition-transform duration-200" />
    </AccordionTrigger>
  </AccordionHeader>
</template>

<script setup lang="ts">
import { AccordionHeader, AccordionTrigger, type AccordionTriggerProps } from 'radix-vue';
import {
  ChevronDown,
} from 'lucide-vue-next';
import { cn } from '@/shared/utils/lib-utils';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<AccordionTriggerProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});
</script>

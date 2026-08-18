<template>
  <AccordionContent
    v-bind="delegatedProps"
    :class="cn(
      'overflow-hidden text-sm',
      props.class
    )"
  >
    <div class="pb-4 pt-0">
      <slot />
    </div>
  </AccordionContent>
</template>

<script setup lang="ts">
import { AccordionContent, type AccordionContentProps } from 'radix-vue';
import { cn } from '@/shared/utils/lib-utils';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<AccordionContentProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});
</script>

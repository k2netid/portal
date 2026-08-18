<template>
  <AccordionItem
    v-bind="delegatedProps"
    :class="cn('border-b', props.class)"
  >
    <slot />
  </AccordionItem>
</template>

<script setup lang="ts">
import { AccordionItem, type AccordionItemProps } from 'radix-vue';
import { cn } from '@/shared/utils/lib-utils';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<AccordionItemProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});
</script>

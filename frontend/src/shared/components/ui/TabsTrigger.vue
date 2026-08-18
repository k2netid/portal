<template>
  <TabsTrigger
    v-bind="delegatedProps"
    :class="cn(
      'inline-flex items-center justify-center whitespace-nowrap rounded-md px-4 py-2 text-sm font-medium cursor-pointer transition-colors duration-150 outline-none disabled:pointer-events-none disabled:opacity-50 text-muted-foreground hover:text-foreground data-[state=active]:bg-card data-[state=active]:text-foreground data-[state=active]:font-semibold',
      props.class
    )"
  >
    <slot />
  </TabsTrigger>
</template>

<script setup lang="ts">
import { TabsTrigger, type TabsTriggerProps } from 'radix-vue';
import { cn } from '@/shared/utils/lib-utils';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<TabsTriggerProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});
</script>

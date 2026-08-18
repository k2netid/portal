<template>
  <TabsContent
    v-bind="delegatedProps"
    :class="cn(
      'mt-4 ring-offset-background focus-visible:outline-none',
      props.class
    )"
  >
    <slot />
  </TabsContent>
</template>

<script setup lang="ts">
import { TabsContent, type TabsContentProps } from 'radix-vue';
import { cn } from '@/shared/utils/lib-utils';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<TabsContentProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});
</script>

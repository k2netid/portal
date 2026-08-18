<template>
  <th 
    data-slot="table-head"
    :class="cn('h-10 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0', props.class)"
  >
    <slot />
  </th>
</template>

<script setup lang="ts">
import { cn } from '@/shared/utils/lib-utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

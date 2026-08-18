<template>
  <td 
    data-slot="table-cell"
    :class="cn('p-4 align-middle [&:has([role=checkbox])]:pr-0', props.class)"
  >
    <slot />
  </td>
</template>

<script setup lang="ts">
import { cn } from '@/shared/utils/lib-utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

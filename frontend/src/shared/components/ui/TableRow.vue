<template>
  <tr 
    data-slot="table-row"
    :class="cn('border-b border-border/40 transition-colors hover:bg-muted/30 data-[state=selected]:bg-muted', props.class)"
  >
    <slot />
  </tr>
</template>

<script setup lang="ts">
import { cn } from '@/shared/utils/lib-utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

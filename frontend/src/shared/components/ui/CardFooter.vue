<template>
  <div 
    data-slot="card-footer"
    :class="cn('bg-muted/50 rounded-b-xl border-t border-border/60 p-4 group-data-[size=sm]/card:p-3 flex items-center', props.class)"
  >
    <slot />
  </div>
</template>

<script setup lang="ts">
import { cn } from '@/shared/utils/lib-utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

<template>
  <div 
    data-slot="card-header"
    :class="cn('gap-1.5 rounded-t-xl px-4 group-data-[size=sm]/card:px-3 grid auto-rows-min items-start', props.class)"
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

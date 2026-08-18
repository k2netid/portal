<template>
  <div 
    data-slot="card-content"
    :class="cn('px-4 group-data-[size=sm]/card:px-3', props.class)"
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

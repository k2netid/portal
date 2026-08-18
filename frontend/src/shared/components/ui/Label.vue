<script setup lang="ts">
import { cn } from '@/shared/utils/lib-utils';
import type { HTMLAttributes } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

<template>
  <label
    v-bind="$attrs"
    :class="cn('text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70', props.class)"
  >
    <slot />
  </label>
</template>

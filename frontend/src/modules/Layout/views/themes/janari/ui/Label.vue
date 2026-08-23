<template>
  <label
    :for="htmlFor"
    :class="cn('text-sm font-semibold text-foreground leading-none', props.class)"
  >
    <slot />
  </label>
</template>

<script setup lang="ts">
import { cn } from './utils/classNames';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  htmlFor?: string;
  class?: HTMLAttributes['class'];
}>();
</script>

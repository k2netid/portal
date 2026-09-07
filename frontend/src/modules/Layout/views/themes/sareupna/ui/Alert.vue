<template>
  <div
    role="alert"
    :class="cn(
      'relative w-full rounded-lg border p-4',
      variant === 'destructive'
        ? 'border-destructive/50 text-destructive bg-destructive/5'
        : 'bg-background text-foreground border-border/50',
      props.class,
    )"
  >
    <slot />
  </div>
</template>

<script setup lang="ts">
import { cn } from './utils/classNames';
import type { HTMLAttributes } from 'vue';

const props = withDefaults(defineProps<{
  variant?: 'default' | 'destructive';
  class?: HTMLAttributes['class'];
}>(), {
  variant: 'default',
  class: undefined,
});
</script>

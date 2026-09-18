<template>
  <button
    :type="type"
    :disabled="disabled"
    :class="cn(buttonClass, props.class)"
    v-bind="$attrs"
  >
    <slot />
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { cn } from './utils/classNames';
import type { HTMLAttributes } from 'vue';

const props = withDefaults(defineProps<{
  variant?: 'default' | 'outline' | 'secondary' | 'ghost' | 'destructive' | 'link';
  size?: 'default' | 'sm' | 'xs' | 'lg' | 'icon';
  class?: HTMLAttributes['class'];
  type?: 'button' | 'submit' | 'reset';
  disabled?: boolean;
}>(), {
  variant: 'default',
  size: 'default',
  type: 'button',
  disabled: false,
  class: undefined,
});

const variantClass: Record<string, string> = {
  default: 'bg-primary text-primary-foreground hover:bg-primary/90',
  outline: 'border border-border/60 bg-background hover:bg-accent/50 hover:text-foreground',
  secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
  ghost: 'hover:bg-accent/50 hover:text-foreground',
  destructive: 'bg-destructive/10 text-destructive hover:bg-destructive/20 border border-destructive/30',
  link: 'text-primary underline-offset-4 hover:underline',
};

const sizeClass: Record<string, string> = {
  default: 'h-9 px-3 text-sm',
  sm: 'h-8 px-2.5 text-xs rounded-lg',
  xs: 'h-7 px-2 text-xs rounded-lg',
  lg: 'h-11 px-4 text-sm',
  icon: 'h-9 w-9',
};

const buttonClass = computed(() => cn(
  'inline-flex items-center justify-center gap-1.5 rounded-lg font-medium whitespace-nowrap transition-colors',
  'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/25',
  'disabled:pointer-events-none disabled:opacity-50',
  variantClass[props.variant],
  sizeClass[props.size],
));
</script>

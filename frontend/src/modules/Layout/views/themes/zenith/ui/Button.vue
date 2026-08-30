<template>
  <component
    :is="as"
    :to="to"
    :href="href"
    :class="[
      'inline-flex items-center justify-center gap-2 font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary/50 disabled:opacity-50 disabled:pointer-events-none',
      sizeClasses[size || 'md'],
      variantClasses[variant || 'primary'],
      className
    ]"
    v-bind="$attrs"
  >
    <slot />
  </component>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    as?: string | object;
    to?: string;
    href?: string;
    variant?: 'primary' | 'secondary' | 'outline' | 'ghost';
    size?: 'sm' | 'md' | 'lg';
    class?: string;
  }>(),
  {
    as: 'button',
    variant: 'primary',
    size: 'md',
    class: '',
  }
);

const className = computed(() => props.class);

const sizeClasses = {
  sm: 'text-xs px-3.5 py-1.5 rounded-full',
  md: 'text-sm px-5 py-2.5 rounded-full',
  lg: 'text-base px-7 py-3.5 rounded-full',
};

const variantClasses = {
  primary: 'bg-primary text-primary-foreground hover:opacity-90 shadow-md shadow-primary/20 hover:shadow-lg hover:shadow-primary/30 active:scale-[0.98]',
  secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
  outline: 'border border-border/80 bg-background/50 hover:bg-muted/50 text-foreground',
  ghost: 'hover:bg-muted/60 text-foreground',
};
</script>

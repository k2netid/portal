<template>
  <component
    :is="as"
    :to="to"
    :href="href"
    :class="[
      'inline-flex items-center justify-center gap-2 font-semibold transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--sarangenge-teal)]/50 disabled:opacity-50 disabled:pointer-events-none',
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
  sm: 'text-xs px-3.5 py-1.5 rounded-[var(--sarangenge-radius-sm,0.85rem)]',
  md: 'text-sm px-5 py-2.5 rounded-[var(--sarangenge-radius-sm,0.85rem)]',
  lg: 'text-base px-7 py-3.5 rounded-[var(--sarangenge-radius-sm,0.85rem)]',
};

const variantClasses = {
  primary: 'bg-[var(--sarangenge-teal,#0f766e)] text-white hover:opacity-92 shadow-md shadow-[var(--sarangenge-teal)]/20 hover:shadow-lg active:scale-[0.98]',
  secondary: 'bg-[var(--sarangenge-sun,#e8a317)] text-[var(--sarangenge-teal-deep,#115e59)] hover:brightness-105',
  outline: 'border border-border/80 bg-background/60 hover:bg-muted/50 text-foreground',
  ghost: 'hover:bg-muted/60 text-foreground',
};
</script>

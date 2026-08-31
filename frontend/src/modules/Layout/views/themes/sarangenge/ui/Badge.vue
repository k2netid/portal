<template>
  <span
    :class="[
      'inline-flex items-center gap-1.5 font-semibold text-xs transition-colors',
      variantClasses[variant || 'default'],
      sizeClasses[size || 'md'],
      className
    ]"
  >
    <slot />
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    variant?: 'default' | 'teal' | 'sun' | 'outline' | 'success' | 'warning';
    size?: 'sm' | 'md' | 'lg';
    class?: string;
  }>(),
  {
    variant: 'default',
    size: 'md',
    class: '',
  }
);

const className = computed(() => props.class);

const sizeClasses = {
  sm: 'px-2 py-0.5 text-[10px] rounded-md',
  md: 'px-3 py-1 text-xs rounded-[var(--sarangenge-radius-sm,0.85rem)]',
  lg: 'px-3.5 py-1.5 text-sm rounded-[var(--sarangenge-radius-sm,0.85rem)]',
};

const variantClasses = {
  default: 'bg-muted text-foreground border border-border/80',
  teal: 'bg-[var(--sarangenge-teal,#0f766e)]/12 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-emerald-300 border border-[var(--sarangenge-teal)]/25',
  sun: 'bg-[var(--sarangenge-sun,#e8a317)]/15 text-amber-800 dark:text-amber-300 border border-[var(--sarangenge-sun)]/30',
  outline: 'border border-border text-muted-foreground bg-background/50',
  success: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30',
  warning: 'bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30',
};
</script>

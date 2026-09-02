<template>
  <span
    :class="[
      'inline-flex items-center gap-1.5 font-bold rounded-full transition-colors',
      sizeClasses[size],
      variantClasses[variant],
      $attrs.class
    ]"
    v-bind="$attrs"
  >
    <slot />
  </span>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    variant?: 'primary' | 'secondary' | 'cyan' | 'emerald' | 'crimson' | 'outline';
    size?: 'sm' | 'md';
  }>(),
  {
    variant: 'primary',
    size: 'sm',
  },
);

const sizeClasses = {
  sm: 'px-2.5 py-0.5 text-xs',
  md: 'px-3 py-1 text-sm',
};

const variantClasses = {
  primary: 'bg-sky-500/15 text-sky-600 dark:text-sky-400 border border-sky-500/30',
  secondary: 'bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30',
  cyan: 'bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30',
  emerald: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30',
  crimson: 'bg-red-500/15 text-red-700 dark:text-red-300 border border-red-500/30',
  outline: 'border border-border/80 text-muted-foreground',
};
</script>

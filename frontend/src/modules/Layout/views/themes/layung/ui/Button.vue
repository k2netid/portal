<template>
  <component
    :is="as"
    :type="as === 'button' ? type : undefined"
    :disabled="disabled || loading"
    :class="[
      'inline-flex items-center justify-center font-bold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none rounded-[var(--layung-radius-sm)]',
      sizeClasses[size],
      variantClasses[variant],
      $attrs.class
    ]"
    v-bind="$attrs"
  >
    <span
      v-if="loading"
      class="mr-2 inline-block w-4 h-4 rounded-full border-2 border-current border-t-transparent animate-spin"
    />
    <slot />
  </component>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    as?: string | object;
    type?: 'button' | 'submit' | 'reset';
    variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger' | 'cyber';
    size?: 'sm' | 'md' | 'lg' | 'icon';
    disabled?: boolean;
    loading?: boolean;
  }>(),
  {
    as: 'button',
    type: 'button',
    variant: 'primary',
    size: 'md',
    disabled: false,
    loading: false,
  },
);

const sizeClasses = {
  sm: 'px-3 py-1.5 text-xs gap-1.5',
  md: 'px-4 py-2 text-sm gap-2',
  lg: 'px-6 py-3 text-base gap-2.5',
  icon: 'p-2 w-9 h-9',
};

const variantClasses = {
  primary: 'bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white shadow-md shadow-orange-500/20 focus:ring-orange-500 active:scale-[0.98]',
  secondary: 'bg-cyan-600 hover:bg-cyan-500 text-white shadow-md shadow-cyan-600/20 focus:ring-cyan-500 active:scale-[0.98]',
  cyber: 'bg-slate-900 hover:bg-slate-800 text-cyan-400 border border-cyan-500/40 shadow-lg shadow-cyan-900/20 focus:ring-cyan-400 active:scale-[0.98]',
  outline: 'border border-border/80 hover:bg-muted/70 text-foreground focus:ring-primary',
  ghost: 'hover:bg-muted/70 text-foreground focus:ring-primary',
  danger: 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500 shadow-md',
};
</script>

<template>
  <div
    role="alert"
    :class="[
      'relative w-full rounded-2xl border p-4 backdrop-blur-md flex items-start gap-3.5',
      variantClasses[variant || 'default'],
      className
    ]"
  >
    <slot />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    variant?: 'default' | 'info' | 'success' | 'warning' | 'destructive';
    class?: string;
  }>(),
  {
    variant: 'default',
    class: '',
  }
);

const className = computed(() => props.class);

const variantClasses = {
  default: 'bg-card text-card-foreground border-border/80',
  info: 'bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200 border-[var(--sarangenge-teal)]/25',
  success: 'bg-emerald-500/10 text-emerald-800 dark:text-emerald-200 border-emerald-500/25',
  warning: 'bg-amber-500/10 text-amber-800 dark:text-amber-200 border-amber-500/30',
  destructive: 'bg-rose-500/10 text-rose-800 dark:text-rose-200 border-rose-500/30',
};
</script>

<template>
  <div
    :class="[
      'p-4 rounded-[var(--layung-radius-sm)] border text-sm flex items-start gap-3',
      variantClasses[variant],
      $attrs.class
    ]"
    v-bind="$attrs"
  >
    <div class="shrink-0 mt-0.5">
      <slot name="icon" />
    </div>
    <div class="flex-1 space-y-1">
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    variant?: 'info' | 'success' | 'warning' | 'error';
  }>(),
  {
    variant: 'info',
  },
);

const variantClasses = {
  info: 'bg-cyan-500/10 border-cyan-500/30 text-cyan-900 dark:text-cyan-200',
  success: 'bg-emerald-500/10 border-emerald-500/30 text-emerald-900 dark:text-emerald-200',
  warning: 'bg-orange-500/10 border-orange-500/30 text-orange-900 dark:text-orange-200',
  error: 'bg-red-500/10 border-red-500/30 text-red-900 dark:text-red-200',
};
</script>

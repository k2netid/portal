<template>
  <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
    <input
      type="checkbox"
      :checked="modelValue"
      :disabled="disabled"
      class="w-4 h-4 rounded text-[var(--sarangenge-teal,#0f766e)] border-border/80 focus:ring-2 focus:ring-[var(--sarangenge-teal,#0f766e)]/30 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
      @change="$emit('update:modelValue', ($event.target as HTMLInputElement).checked)"
    >
    <span
      v-if="$slots.default"
      class="text-sm font-medium text-foreground/90"
    >
      <slot />
    </span>
  </label>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    modelValue?: boolean;
    disabled?: boolean;
  }>(),
  {
    modelValue: false,
    disabled: false,
  }
);

defineEmits<{
  (e: 'update:modelValue', value: boolean): void;
}>();
</script>

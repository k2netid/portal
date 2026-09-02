<template>
  <label class="inline-flex items-center gap-2.5 cursor-pointer text-sm select-none">
    <input
      :checked="modelValue"
      type="checkbox"
      :disabled="disabled"
      class="w-4 h-4 rounded text-sky-600 border-border/80 focus:ring-sky-500 transition-colors"
      @change="$emit('update:modelValue', ($event.target as HTMLInputElement).checked)"
    >
    <span class="text-foreground font-medium">
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
  },
);

defineEmits<{
  'update:modelValue': [value: boolean];
}>();
</script>

<template>
  <div class="relative w-full">
    <select
      :value="modelValue"
      :disabled="disabled"
      :required="required"
      :class="[
        'w-full appearance-none px-4 py-2.5 rounded-[var(--layung-radius-sm)] bg-card border border-border/80 text-foreground text-sm transition-all focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-500 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm pr-10',
        $attrs.class
      ]"
      v-bind="$attrs"
      @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
    >
      <slot />
    </select>
    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-muted-foreground">
      <svg
        class="w-4 h-4"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M19 9l-7 7-7-7"
        />
      </svg>
    </div>
  </div>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    modelValue?: string | number;
    disabled?: boolean;
    required?: boolean;
  }>(),
  {
    modelValue: '',
    disabled: false,
    required: false,
  },
);

defineEmits<{
  'update:modelValue': [value: string];
}>();
</script>

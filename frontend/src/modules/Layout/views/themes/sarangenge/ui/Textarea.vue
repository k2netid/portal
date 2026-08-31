<template>
  <textarea
    :value="modelValue"
    :placeholder="placeholder"
    :rows="rows"
    :disabled="disabled"
    :required="required"
    :class="[
      'w-full px-4 py-2.5 rounded-[var(--sarangenge-radius-sm,0.85rem)] border border-border/80 bg-background/80 text-foreground placeholder:text-muted-foreground/60 transition-all duration-200 focus:outline-none focus:border-[var(--sarangenge-teal,#0f766e)] focus:ring-2 focus:ring-[var(--sarangenge-teal,#0f766e)]/20 disabled:opacity-50 disabled:cursor-not-allowed text-sm shadow-sm resize-y',
      className
    ]"
    v-bind="$attrs"
    @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    modelValue?: string;
    placeholder?: string;
    rows?: number;
    disabled?: boolean;
    required?: boolean;
    class?: string;
  }>(),
  {
    modelValue: '',
    placeholder: '',
    rows: 4,
    disabled: false,
    required: false,
    class: '',
  }
);

defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();

const className = computed(() => props.class);
</script>

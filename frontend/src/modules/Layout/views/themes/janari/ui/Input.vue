<template>
  <input
    :id="id"
    :type="type"
    :value="type === 'file' ? undefined : modelValue"
    :placeholder="placeholder"
    :required="required"
    :disabled="disabled"
    :class="cn(inputClass, props.class)"
    v-bind="$attrs"
    @input="onInput"
  >
</template>

<script setup lang="ts">
import { cn } from './utils/classNames';
import type { HTMLAttributes } from 'vue';

const props = withDefaults(defineProps<{
  id?: string;
  type?: string;
  modelValue?: string | number;
  placeholder?: string;
  required?: boolean;
  disabled?: boolean;
  class?: HTMLAttributes['class'];
}>(), {
  type: 'text',
  id: undefined,
  modelValue: undefined,
  placeholder: undefined,
  class: undefined,
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();

const inputClass =
  'flex h-10 w-full rounded-lg border border-border/50 bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary/40 disabled:cursor-not-allowed disabled:opacity-50';

const onInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  emit('update:modelValue', target.value);
};
</script>

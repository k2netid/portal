<script setup lang="ts">
import { cn } from '@/shared/utils/lib-utils';
import type { HTMLAttributes } from 'vue';

interface Props {
  defaultValue?: string | number;
  modelValue?: string | number;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  defaultValue: undefined,
  modelValue: undefined,
  class: '',
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number): void;
}>();

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  emit('update:modelValue', target.value);
};
</script>

<template>
  <input
    :value="modelValue"
    data-slot="input"
    :class="cn(
      'flex h-10 w-full rounded-lg border border-border/50 bg-transparent px-3 py-1 text-base file:h-6 file:text-sm file:font-medium focus:outline-none focus:border-primary disabled:cursor-not-allowed disabled:opacity-50 md:text-sm placeholder:text-muted-foreground outline-none file:inline-flex file:border-0 file:bg-transparent',
      props.class
    )"
    v-bind="$attrs"
    @input="handleInput"
  >
</template>

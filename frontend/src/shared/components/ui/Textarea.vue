<script setup lang="ts">
import { cn } from '@/shared/utils/lib-utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  modelValue?: string | number;
  class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number): void;
}>();
</script>

<template>
  <textarea
    :value="modelValue"
    data-slot="input"
    :class="cn(
      'flex min-h-[80px] w-full rounded-lg border border-border/50 bg-transparent px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary/50 disabled:cursor-not-allowed disabled:opacity-50 placeholder:text-muted-foreground outline-none',
      props.class
    )"
    v-bind="$attrs"
    @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
  />
</template>

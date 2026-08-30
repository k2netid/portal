<template>
  <button
    type="button"
    role="option"
    :aria-selected="ctx?.modelValue.value === value"
    :class="cn(
      'flex w-full cursor-pointer items-center rounded-lg px-3 py-2 text-sm transition-colors',
      ctx?.modelValue.value === value ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-primary/5',
      props.class,
    )"
    @click="ctx?.select(value)"
  >
    <slot />
  </button>
</template>

<script setup lang="ts">
import { inject, onMounted, useSlots, watchEffect } from 'vue';
import { JANARI_SELECT_KEY } from './composables/selectContext';
import { cn } from './utils/classNames';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  value: string;
  class?: HTMLAttributes['class'];
}>();

const ctx = inject(JANARI_SELECT_KEY);
if (!ctx) throw new Error('SelectItem must be used inside Select');

const slots = useSlots();

const registerLabel = () => {
  const text = slots.default?.()?.[0]?.children;
  if (typeof text === 'string' && text.trim()) {
    ctx.labels.value[props.value] = text.trim();
  }
};

onMounted(registerLabel);
watchEffect(registerLabel);
</script>

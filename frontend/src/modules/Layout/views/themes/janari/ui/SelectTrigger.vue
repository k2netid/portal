<template>
  <button
    ref="triggerEl"
    type="button"
    :id="id"
    :class="cn(
      'flex h-10 w-full items-center justify-between rounded-lg border border-border/50 bg-background px-3 py-2 text-sm',
      'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20',
      props.class,
    )"
    :aria-expanded="ctx?.open.value ?? false"
    aria-haspopup="listbox"
    @click="onToggle"
  >
    <slot />
  </button>
</template>

<script setup lang="ts">
import { inject, ref, watch } from 'vue';
import { JANARI_SELECT_KEY } from './composables/selectContext';
import { cn } from './utils/classNames';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  id?: string;
  class?: HTMLAttributes['class'];
}>();

const ctx = inject(JANARI_SELECT_KEY);
if (!ctx) throw new Error('SelectTrigger must be used inside Select');

const triggerEl = ref<HTMLButtonElement | null>(null);
watch(triggerEl, (el) => {
  ctx.triggerRef.value = el;
}, { immediate: true });

const onToggle = () => {
  ctx.open.value = !ctx.open.value;
};
</script>

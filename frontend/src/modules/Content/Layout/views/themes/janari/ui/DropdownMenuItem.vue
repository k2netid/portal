<template>
  <button
    type="button"
    role="menuitem"
    :class="cn(
      'flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm transition-colors',
      'hover:bg-primary/5 focus:bg-primary/5 focus:outline-none',
      props.class,
    )"
    @click="onClick"
  >
    <slot />
  </button>
</template>

<script setup lang="ts">
import { inject } from 'vue';
import { JANARI_DROPDOWN_KEY } from './composables/dropdownContext';
import { cn } from './utils/classNames';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
  (e: 'click', event: MouseEvent): void;
}>();

const ctx = inject(JANARI_DROPDOWN_KEY);

const onClick = (event: MouseEvent) => {
  emit('click', event);
  ctx?.close();
};
</script>

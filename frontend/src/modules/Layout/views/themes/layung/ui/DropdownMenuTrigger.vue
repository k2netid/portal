<template>
  <button
    ref="triggerEl"
    type="button"
    :class="props.class"
    :aria-expanded="ctx?.open.value ?? false"
    aria-haspopup="menu"
    v-bind="$attrs"
    @click="ctx?.toggle()"
  >
    <slot />
  </button>
</template>

<script setup lang="ts">
import { inject, ref, watch, type HTMLAttributes } from 'vue';
import { LAYUNG_DROPDOWN_KEY } from './composables/dropdownContext';

defineOptions({ inheritAttrs: false });

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();

const ctx = inject(LAYUNG_DROPDOWN_KEY);
if (!ctx) {
  throw new Error('DropdownMenuTrigger must be used inside DropdownMenu');
}

const triggerEl = ref<HTMLButtonElement | null>(null);
watch(triggerEl, (el) => {
  if (ctx) ctx.triggerRef.value = el;
}, { immediate: true });
</script>

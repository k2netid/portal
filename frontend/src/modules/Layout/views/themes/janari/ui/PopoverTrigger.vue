<template>
  <div
    ref="triggerEl"
    :class="asChild ? 'contents' : 'inline-flex'"
    @click="ctx.toggle()"
  >
    <button
      v-if="!asChild"
      type="button"
      class="inline-flex"
    >
      <slot />
    </button>
    <slot v-else />
  </div>
</template>

<script setup lang="ts">
import { inject, ref, watch } from 'vue';
import { JANARI_POPOVER_KEY } from './composables/popoverContext';

const props = defineProps<{
  asChild?: boolean;
}>();

const asChild = props.asChild ?? false;

const ctx = inject(JANARI_POPOVER_KEY);
if (!ctx) throw new Error('PopoverTrigger must be used inside Popover');

const triggerEl = ref<HTMLElement | null>(null);

const resolveTrigger = (): HTMLElement | null => {
  const el = triggerEl.value;
  if (!el) return null;
  if (asChild) {
    return (el.firstElementChild as HTMLElement | null) ?? el;
  }
  return el;
};

watch(triggerEl, () => {
  ctx.triggerRef.value = resolveTrigger();
}, { immediate: true });
</script>

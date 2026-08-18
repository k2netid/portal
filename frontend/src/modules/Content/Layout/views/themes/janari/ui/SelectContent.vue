<template>
  <Teleport to="body">
    <div
      v-if="ctx?.open.value"
      ref="panelRef"
      role="listbox"
      :class="cn(
        'max-h-60 min-w-[8rem] overflow-auto rounded-xl border border-border/50 bg-background p-1 shadow-lg ring-1 ring-border/40',
        props.class,
      )"
      :style="panelStyle"
    >
      <slot />
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { inject, ref, watch, onMounted, onUnmounted, type HTMLAttributes } from 'vue';
import { onClickOutside } from '@vueuse/core';
import { JANARI_SELECT_KEY } from './composables/selectContext';
import { cn } from './utils/classNames';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();

const ctx = inject(JANARI_SELECT_KEY);
if (!ctx) throw new Error('SelectContent must be used inside Select');

const panelRef = ref<HTMLElement | null>(null);
const panelStyle = ref<Record<string, string>>({});

const positionPanel = () => {
  const trigger = ctx.triggerRef.value;
  const panel = panelRef.value;
  if (!trigger || !panel) return;
  const rect = trigger.getBoundingClientRect();
  panelStyle.value = {
    position: 'fixed',
    top: `${rect.bottom + 4}px`,
    left: `${rect.left}px`,
    width: `${rect.width}px`,
    zIndex: '200',
  };
};

watch(() => ctx.open.value, (isOpen) => {
  if (isOpen) requestAnimationFrame(positionPanel);
});

onClickOutside(panelRef, () => ctx.close());

const onEscape = (e: KeyboardEvent) => {
  if (e.key === 'Escape') ctx.close();
};
onMounted(() => document.addEventListener('keydown', onEscape));
onUnmounted(() => document.removeEventListener('keydown', onEscape));
</script>

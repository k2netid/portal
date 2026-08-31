<template>
  <Teleport to="body">
    <div
      v-if="ctx?.open.value"
      ref="panelRef"
      role="menu"
      data-sarangenge-overlay
      :class="cn(
        'sarangenge-overlay-panel min-w-32 overflow-hidden rounded-xl border border-border/50 bg-card p-1.5 text-foreground shadow-lg ring-1 ring-border/40',
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
import { SARANGENGE_DROPDOWN_KEY } from './composables/dropdownContext';
import { cn } from './utils/classNames';

const props = defineProps<{
  class?: HTMLAttributes['class'];
  align?: 'start' | 'end' | 'center';
  sideOffset?: number;
}>();

const ctx = inject(SARANGENGE_DROPDOWN_KEY);
if (!ctx) {
  throw new Error('DropdownMenuContent must be used inside DropdownMenu');
}

const panelRef = ref<HTMLElement | null>(null);
const panelStyle = ref<Record<string, string>>({});

const positionPanel = () => {
  const trigger = ctx.triggerRef.value;
  const panel = panelRef.value;
  if (!trigger || !panel) return;

  const rect = trigger.getBoundingClientRect();
  const align = props.align ?? ctx.align.value;
  const offset = props.sideOffset ?? ctx.sideOffset.value;
  let left = rect.left;
  if (align === 'end') {
    left = rect.right - panel.offsetWidth;
  } else if (align === 'center') {
    left = rect.left + rect.width / 2 - panel.offsetWidth / 2;
  }

  panelStyle.value = {
    position: 'fixed',
    top: `${rect.bottom + offset}px`,
    left: `${Math.max(8, left)}px`,
    zIndex: '150',
  };
};

watch(() => ctx.open.value, (isOpen) => {
  if (isOpen) {
    requestAnimationFrame(positionPanel);
  }
});

onClickOutside(panelRef, () => ctx.close());

const onEscape = (e: KeyboardEvent) => {
  if (e.key === 'Escape') ctx.close();
};

onMounted(() => document.addEventListener('keydown', onEscape));
onUnmounted(() => document.removeEventListener('keydown', onEscape));
</script>

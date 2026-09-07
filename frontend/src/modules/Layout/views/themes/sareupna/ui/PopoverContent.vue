<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      ref="panelRef"
      :class="cn(
        'z-[200] rounded-xl border border-border/50 bg-background p-3 text-foreground shadow-lg ring-1 ring-border/40',
        props.class,
      )"
      :style="panelStyle"
    >
      <slot />
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, inject, ref, watch, onMounted, onUnmounted, type HTMLAttributes } from 'vue';
import { onClickOutside } from '@vueuse/core';
import { JANARI_POPOVER_KEY } from './composables/popoverContext';
import { useFloatingPanelPosition } from './composables/useFloatingPanel';
import { cn } from './utils/classNames';

const props = withDefaults(defineProps<{
  class?: HTMLAttributes['class'];
  side?: 'top' | 'bottom' | 'left' | 'right';
  align?: 'start' | 'end' | 'center';
  sideOffset?: number;
}>(), {
  side: 'bottom',
  align: 'start',
  sideOffset: 8,
  class: undefined,
});

const ctx = inject(JANARI_POPOVER_KEY);
if (!ctx) throw new Error('PopoverContent must be used inside Popover');

const panelRef = ref<HTMLElement | null>(null);
const panelStyle = ref<Record<string, string>>({});

const isOpen = computed(() => ctx.open.value);

const { apply } = useFloatingPanelPosition(ctx.triggerRef, panelRef, {
  side: props.side ?? ctx.side.value,
  align: props.align ?? ctx.align.value,
  sideOffset: props.sideOffset ?? ctx.sideOffset.value,
});

watch(isOpen, (open) => {
  if (open) {
    void apply().then(() => {
      const panel = panelRef.value;
      if (panel) {
        panelStyle.value = {
          position: panel.style.position,
          top: panel.style.top,
          left: panel.style.left,
          zIndex: panel.style.zIndex,
        };
      }
    });
  }
});

onClickOutside(panelRef, () => ctx.close());

const onEscape = (e: KeyboardEvent) => {
  if (e.key === 'Escape') ctx.close();
};
onMounted(() => document.addEventListener('keydown', onEscape));
onUnmounted(() => document.removeEventListener('keydown', onEscape));
</script>

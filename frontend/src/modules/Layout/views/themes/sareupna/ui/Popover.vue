<template>
  <div class="relative inline-block">
    <slot />
  </div>
</template>

<script setup lang="ts">
import { computed, provide, ref } from 'vue';
import { JANARI_POPOVER_KEY, type JanariPanelAlign, type JanariPanelSide } from './composables/popoverContext';

const props = withDefaults(defineProps<{
  open?: boolean;
  side?: JanariPanelSide;
  align?: JanariPanelAlign;
  sideOffset?: number;
}>(), {
  side: 'bottom',
  align: 'start',
  sideOffset: 8,
});

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void;
}>();

const internalOpen = ref(false);
const open = computed({
  get: () => props.open ?? internalOpen.value,
  set: (value: boolean) => {
    internalOpen.value = value;
    emit('update:open', value);
  },
});

const triggerRef = ref<HTMLElement | null>(null);
const close = () => { open.value = false; };
const toggle = () => { open.value = !open.value; };

provide(JANARI_POPOVER_KEY, {
  open,
  side: ref(props.side),
  align: ref(props.align),
  sideOffset: ref(props.sideOffset),
  triggerRef,
  close,
  toggle,
});
</script>

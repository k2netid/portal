<template>
  <div class="relative inline-flex">
    <slot />
  </div>
</template>

<script setup lang="ts">
import { provide, ref } from 'vue';
import { LAYUNG_DROPDOWN_KEY } from './composables/dropdownContext';

const props = withDefaults(defineProps<{
  align?: 'start' | 'end' | 'center';
  sideOffset?: number;
}>(), {
  align: 'end',
  sideOffset: 8,
});

const open = ref(false);
const triggerRef = ref<HTMLElement | null>(null);
const close = () => { open.value = false; };
const toggle = () => { open.value = !open.value; };

provide(LAYUNG_DROPDOWN_KEY, {
  open,
  align: ref(props.align),
  sideOffset: ref(props.sideOffset),
  triggerRef,
  close,
  toggle,
});
</script>

<template>
  <div class="inline-block" ref="wrapperRef">
    <BasePopover
      :is-open="isOpen"
      :trigger-rect="triggerRect"
      :align="align"
      :width="width"
      :no-padding="noPadding"
      :offset="4"
      @close="close"
    >
      <template #trigger>
         <div class="cursor-pointer" @click="toggle">
            <slot name="trigger" :open="isOpen" :toggle="toggle" />
         </div>
      </template>

      <div class="flex flex-col min-w-[140px]" :class="{ 'p-1': !noPadding }">
        <slot :close="close" />
      </div>
    </BasePopover>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import BasePopover from './BasePopover.vue';

interface Props {
  align?: 'left' | 'right' | 'center';
  width?: string | number;
  noPadding?: boolean;
}

withDefaults(defineProps<Props>(), {
  align: 'left',
  width: 200,
  noPadding: false
});

const isOpen = ref(false);
const wrapperRef = ref<HTMLElement | null>(null);
const triggerRect = ref<DOMRect | null>(null);

const toggle = (e?: Event) => {
  if (e) e.stopPropagation();
  isOpen.value = !isOpen.value;
};

const open = () => {
  if (wrapperRef.value) {
    window.dispatchEvent(new CustomEvent('builder:dropdown-open', { detail: { id: wrapperRef.value } }));
  }
  isOpen.value = true;
};

const close = () => {
  isOpen.value = false;
};

const handleOtherOpen = (e: Event) => {
    const customEvent = e as CustomEvent;
    if (isOpen.value && customEvent.detail.id !== wrapperRef.value) {
        close();
    }
};

watch(isOpen, (val) => {
    if (val) {
        window.addEventListener('builder:dropdown-open', handleOtherOpen);
    } else {
        window.removeEventListener('builder:dropdown-open', handleOtherOpen);
    }
});

defineExpose({ open, close, toggle });
</script>

<template>
  <Dialog :open="isOpen" @update:open="onOpenUpdate">
    <DialogContent 
      :class="cn(
        'sm:max-w-md p-0 overflow-hidden !z-[100001]',
        placement !== 'center' && 'fixed',
        placementClass,
        themeClasses,
        props.class
      )"
      :overlay-class="'!z-[100000]'"
      :style="{ width: typeof width === 'number' ? `${width}px` : width }"
    >
      <DialogHeader v-if="$slots.header || title" class="px-6 py-4 border-b bg-background">
        <slot name="header">
          <DialogTitle>{{ title }}</DialogTitle>
        </slot>
      </DialogHeader>
      
      <div class="px-6 py-6 overflow-y-auto max-h-[80vh] bg-background">
        <slot />
      </div>
      
      <DialogFooter v-if="$slots.footer" class="px-6 py-4 border-t bg-muted/30">
        <slot name="footer" />
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useCmsStore } from '@/stores/cms';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter
} from '@/components/ui';
import { cn } from '@/lib/utils';

interface Props {
  isOpen?: boolean;
  title?: string;
  width?: string | number;
  showClose?: boolean;
  closeOnBackdrop?: boolean;
  noBackdrop?: boolean;
  placement?: string;
  class?: string;
}

const props = withDefaults(defineProps<Props>(), {
  isOpen: false,
  title: '',
  width: 500,
  showClose: true,
  closeOnBackdrop: true,
  noBackdrop: false,
  placement: 'center',
  class: ''
});

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'update:isOpen', value: boolean): void;
}>();

const cmsStore = useCmsStore();

const placementClass = computed(() => {
    switch (props.placement) {
        case 'top-left': return 'top-4 left-4 translate-x-0 translate-y-0';
        case 'top-right': return 'top-4 right-4 left-auto translate-x-0 translate-y-0';
        case 'bottom-left': return 'bottom-4 left-4 top-auto translate-x-0 translate-y-0';
        case 'bottom-right': return 'bottom-4 right-4 top-auto left-auto translate-x-0 translate-y-0';
        case 'right-sidebar': return 'top-[60px] right-[340px] left-auto translate-x-0 translate-y-0';
        default: return '';
    }
});

const themeClasses = computed(() => {
    return [
        'ja-builder',
        cmsStore.isDarkMode ? 'ja-builder--dark dark' : 'ja-builder--light'
    ].join(' ');
});

const onOpenUpdate = (val: boolean) => {
    if (!val) emit('close');
    emit('update:isOpen', val);
};
</script>

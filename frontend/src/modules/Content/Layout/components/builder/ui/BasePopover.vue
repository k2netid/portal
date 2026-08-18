<template>
  <Popover :open="isOpen" @update:open="onOpenUpdate">
    <PopoverAnchor v-if="triggerRect" :reference-rect="triggerRect" />
    <PopoverTrigger v-else as-child>
      <slot name="trigger" />
    </PopoverTrigger>

    <PopoverContent 
      :side="side" 
      :align="mappedAlign" 
      :side-offset="offset"
      :avoid-collisions="true"
      :collision-padding="20"
      sticky="always"
      :class="cn(
        'ja-builder',
        cmsStore.isDarkMode ? 'ja-builder--dark' : 'ja-builder--light',
        'p-0 overflow-hidden border-slate-200 dark:border-slate-800 shadow-xl !z-[100001]',
        props.class
      )"
      :style="{ width: typeof width === 'number' ? `${width}px` : width }"
    >
      <div v-if="$slots.header || title" class="flex items-center justify-between px-4 py-2.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <slot name="header">
          <h3 class="text-[13px] font-bold text-slate-700 dark:text-slate-200 uppercase tracking-tight">{{ title }}</h3>
        </slot>
        <PopoverClose v-if="showClose" class="rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none data-[state=open]:bg-accent data-[state=open]:text-muted-foreground">
          <X class="h-4 w-4" />
          <span class="sr-only">Close</span>
        </PopoverClose>
      </div>
      
      <div class="overflow-y-auto" :class="{ 'p-4': !noPadding }">
        <slot />
      </div>
      
      <div v-if="$slots.footer" class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <slot name="footer" />
      </div>
    </PopoverContent>
  </Popover>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useCmsStore } from '@/stores/cms';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import { 
    Popover, 
    PopoverTrigger, 
    PopoverContent, 
} from '@/components/ui';
import { PopoverAnchor, PopoverClose } from 'radix-vue';
import { cn } from '@/lib/utils';

interface Props {
  isOpen?: boolean;
  triggerRect?: DOMRect | null;
  title?: string;
  showClose?: boolean;
  backdrop?: boolean;
  width?: string | number;
  offset?: number;
  noPadding?: boolean;
  align?: 'left' | 'right' | 'center';
  side?: 'top' | 'right' | 'bottom' | 'left';
  class?: string;
}

const props = withDefaults(defineProps<Props>(), {
  isOpen: false,
  triggerRect: null,
  title: '',
  showClose: true,
  backdrop: true,
  width: 320,
  offset: 8,
  noPadding: false,
  align: 'right',
  side: 'bottom',
  class: ''
});

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'update:isOpen', value: boolean): void;
}>();

const onOpenUpdate = (val: boolean) => {
    if (!val) emit('close');
    emit('update:isOpen', val);
};

const cmsStore = useCmsStore();

const mappedAlign = computed(() => {
    if (props.align === 'left') return 'start';
    if (props.align === 'right') return 'end';
    return 'center';
});
</script>

<template>
  <div 
    :class="cn(
        'bg-slate-200 dark:bg-slate-800 shrink-0',
        orientation === 'horizontal' ? 'h-[1px] w-full' : 'h-full w-[1px]',
        props.class
    )"
    :style="dividerStyle"
  ></div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import type { CSSProperties } from 'vue';

interface Props {
  orientation?: 'horizontal' | 'vertical';
  margin?: string | number;
  class?: string;
}

const props = withDefaults(defineProps<Props>(), {
  orientation: 'horizontal',
  margin: 8,
  class: ''
});

const dividerStyle = computed<CSSProperties>(() => {
  const marginValue = typeof props.margin === 'number' ? `${props.margin}px` : props.margin;
  return props.orientation === 'horizontal' 
    ? { marginTop: marginValue, marginBottom: marginValue }
    : { marginLeft: marginValue, marginRight: marginValue };
});
</script>

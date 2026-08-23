<template>
  <Button
    :variant="mappedVariant"
    size="icon"
    :title="title"
    :disabled="disabled"
    @click="$emit('click', $event)"
    :class="[
        active && 'border border-white/20 shadow-sm',
        sizeClasses,
        props.class
    ]"
  >
    <component :is="icon" :size="iconSize" />
    <slot />
  </Button>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { type Component } from 'vue';
import { Button } from '@/components/ui';

interface Props {
  icon: object | ((...args: unknown[]) => unknown) | string | Component;
  variant?: 'primary' | 'secondary' | 'ghost'; // custom variants mapped to shred
  size?: 'sm' | 'md' | 'lg';
  active?: boolean;
  title?: string;
  disabled?: boolean;
  class?: string | object | unknown[];
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'secondary',
  size: 'md',
  active: false,
  title: '',
  disabled: false,
  class: ''
});

defineEmits<{
  (e: 'click', event: MouseEvent): void;
}>();

const mappedVariant = computed(() => {
  if (props.active) return 'default';
  const map: Record<string, "default" | "secondary" | "ghost" | "link" | "outline" | "destructive"> = {
    'primary': 'default',
    'secondary': 'secondary',
    'ghost': 'ghost'
  };
  return map[props.variant || 'secondary'] || 'secondary';
});

const iconSize = computed(() => {
  switch (props.size) {
    case 'sm': return 14;
    case 'lg': return 20;
    default: return 16;
  }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm': return 'w-7 h-7';
        case 'lg': return 'w-10 h-10';
        default: return 'w-8 h-8';
    }
});
</script>

<template>
  <Button
    :variant="mappedVariant as any"
    :size="mappedSize as any"
    :disabled="disabled || loading"
    v-bind="$attrs"
    :class="[
        { 'opacity-50 pointer-events-none': loading },
        active && 'ring-2 ring-primary ring-offset-2',
        props.class
    ]"
  >
    <template v-if="loading">
      <Loader2 class="w-4 h-4 animate-spin mr-2" />
      <slot />
    </template>
    <slot v-else />
  </Button>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import { Button } from '@/components/ui';

interface Props {
  variant?: string;
  size?: string;
  disabled?: boolean;
  loading?: boolean;
  active?: boolean;
  class?: string;
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'secondary',
  size: 'md',
  disabled: false,
  loading: false,
  active: false,
  class: ''
});

const mappedVariant = computed(() => {
  const map: Record<string, string> = {
    'primary': 'default',
    'secondary': 'secondary',
    'danger': 'destructive',
    'ghost': 'ghost'
  };
  return map[props.variant] || 'default';
});

const mappedSize = computed(() => {
    const map: Record<string, string> = {
        'sm': 'sm',
        'md': 'default',
        'lg': 'lg'
    };
    return map[props.size] || 'default';
});
</script>

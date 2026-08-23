<template>
  <Label 
    class="flex items-center gap-1.5 text-[11px] font-semibold tracking-wider transition-colors"
    :class="[
        muted ? 'text-slate-500' : 'text-slate-900',
        uppercase ? 'uppercase' : ''
    ].filter(Boolean).join(' ')"
  >
    <slot name="prefix" />
    <slot />
    <slot name="suffix" />
  </Label>
</template>

<script setup lang="ts">
import { Label } from '@/components/ui';

interface Props {
  muted?: boolean;
  uppercase?: boolean;
}

withDefaults(defineProps<Props>(), {
  muted: true,
  uppercase: false
});
</script>

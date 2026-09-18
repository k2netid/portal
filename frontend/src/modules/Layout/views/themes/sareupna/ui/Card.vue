<template>
  <div :class="cn('flex flex-col gap-4 rounded-xl border border-border/40 bg-card text-card-foreground py-4 text-sm shadow-none', props.class)">
    <slot />
  </div>
</template>

<script setup lang="ts">
import { cn } from './utils/classNames';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

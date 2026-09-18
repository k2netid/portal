<template>
  <div :class="cn('text-sm opacity-90 [&_p]:leading-relaxed', props.class)">
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

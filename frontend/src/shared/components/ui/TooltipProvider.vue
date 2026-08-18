
<script setup lang="ts">
import { TooltipProvider, type TooltipProviderProps } from 'radix-vue'

const props = withDefaults(defineProps<TooltipProviderProps>(), {
  delayDuration: 0,
  skipDelayDuration: 300,
  disableHoverableContent: false,
})
</script>

<template>
  <TooltipProvider v-bind="props">
    <slot />
  </TooltipProvider>
</template>

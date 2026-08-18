<script setup lang="ts">
import type { DropdownMenuTriggerProps } from "reka-ui"
import { DropdownMenuTrigger as RekaDropdownMenuTrigger, useForwardProps } from "reka-ui"

const props = defineProps<DropdownMenuTriggerProps>()

const forwardedProps = useForwardProps(props)
</script>

<template>
  <RekaDropdownMenuTrigger
    class="outline-none"
    v-bind="forwardedProps"
  >
    <slot />
  </RekaDropdownMenuTrigger>
</template>

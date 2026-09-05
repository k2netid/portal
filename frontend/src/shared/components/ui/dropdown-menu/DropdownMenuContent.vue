<script setup lang="ts">
import type { DropdownMenuContentEmits, DropdownMenuContentProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import {
  DropdownMenuContent as RekaDropdownMenuContent,
  DropdownMenuPortal,
  useForwardPropsEmits,
} from "reka-ui"
import { cn } from "@/shared/utils/lib-utils"

const props = withDefaults(
  defineProps<DropdownMenuContentProps & { class?: HTMLAttributes["class"] }>(),
  {
    sideOffset: 4,
    class: undefined,
  },
)
const emits = defineEmits<DropdownMenuContentEmits>()

const delegatedProps = reactiveOmit(props, "class")

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <DropdownMenuPortal>
    <RekaDropdownMenuContent
      v-bind="forwarded"
      :class="cn('!z-[100080] min-w-32 overflow-hidden rounded-md border border-border/80 bg-popover p-1 text-popover-foreground shadow-xl shadow-black/15 ring-1 ring-black/5 dark:shadow-black/50 dark:ring-white/10 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2', props.class)"
    >
      <slot />
    </RekaDropdownMenuContent>
  </DropdownMenuPortal>
</template>

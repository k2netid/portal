<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue'
import { PopoverContent, type PopoverContentEmits, type PopoverContentProps, PopoverPortal, useForwardPropsEmits } from 'radix-vue'
import { cn } from '@/shared/utils/lib-utils'

const props = withDefaults(defineProps<PopoverContentProps & { class?: HTMLAttributes['class'] }>(), {
  sideOffset: 4,
  align: 'center',
  class: undefined,
})
const emits = defineEmits<PopoverContentEmits>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props

  return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <PopoverPortal>
    <PopoverContent
      v-bind="forwarded"
      :class="
        cn(
          '!z-[100001] w-72 rounded-xl border border-border/80 bg-popover p-4 text-popover-foreground outline-none shadow-2xl shadow-black/15 ring-1 ring-black/5 dark:shadow-black/50 dark:ring-white/10',
          props.class,
        )
      "
    >
      <slot />
    </PopoverContent>
  </PopoverPortal>
</template>

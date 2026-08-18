<template>
  <div
    v-bind="attrs"
    data-slot="card"
    :data-size="size"
    :class="cn(
      'bg-card text-card-foreground gap-4 overflow-hidden rounded-xl border border-border/40 py-4 text-sm has-[[data-slot=card-footer]]:pb-0 has-[>img:first-child]:pt-0 data-[size=sm]:gap-3 data-[size=sm]:py-3 data-[size=sm]:has-[[data-slot=card-footer]]:pb-0 group/card flex flex-col shadow-none',
      props.class,
    )"
  >
    <slot />
  </div>
</template>

<script setup lang="ts">
import { useAttrs } from 'vue';
import { cn } from '@/shared/utils/lib-utils';

import { type HTMLAttributes } from 'vue';

defineOptions({ inheritAttrs: false });

const attrs = useAttrs();

const props = defineProps<{
  class?: HTMLAttributes['class'];
  size?: 'default' | 'sm';
}>();
</script>

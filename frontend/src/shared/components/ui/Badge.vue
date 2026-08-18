<template>
  <div :class="cn(badgeVariants({ variant }), props.class)">
    <slot />
  </div>
</template>

<script setup lang="ts">
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/shared/utils/lib-utils';

const badgeVariants = cva(
  'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
  {
    variants: {
      variant: {
        default: 'border-transparent bg-primary text-primary-foreground hover:bg-primary/80',
        secondary: 'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80',
        destructive: 'border-transparent bg-destructive text-destructive-foreground hover:bg-secondary/80',
        outline: 'text-foreground',
        success: 'border-transparent bg-green-800 text-white',
        warning: 'border-transparent bg-warning/15 text-warning',
        info: 'border-transparent bg-info/15 text-info',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  }
);

import { type HTMLAttributes } from 'vue';

interface Props {
  variant?: VariantProps<typeof badgeVariants>['variant'];
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

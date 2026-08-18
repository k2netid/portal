<script setup lang="ts">
import { useAttrs } from 'vue';

defineOptions({ inheritAttrs: false });

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/shared/components/ui';
import { cn } from '@/shared/utils/lib-utils';

withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        /** Pad body when no titled header (default form section). */
        padded?: boolean;
    }>(),
    { padded: true, title: '', description: '' },
);

const attrs = useAttrs();
</script>

<template>
  <Card
    :class="cn(
      'rounded-xl border border-border/50 bg-card shadow-none',
      padded && !title && !$slots.header && 'p-5',
      attrs.class as string,
    )"
  >
    <CardHeader
      v-if="title || $slots.header"
      class="border-b border-border/40 px-5 py-4"
    >
      <slot name="header">
        <CardTitle
          v-if="title"
          class="text-base font-semibold tracking-tight text-foreground"
        >
          {{ title }}
        </CardTitle>
        <CardDescription
          v-if="description"
          class="text-sm text-muted-foreground"
        >
          {{ description }}
        </CardDescription>
      </slot>
    </CardHeader>
    <CardContent
      v-if="title || $slots.header"
      class="p-5"
    >
      <slot />
    </CardContent>
    <slot v-else />
  </Card>
</template>

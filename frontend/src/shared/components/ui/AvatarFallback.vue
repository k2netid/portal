<template>
  <AvatarFallback
    v-bind="delegatedProps"
    :class="cn('flex h-full w-full items-center justify-center rounded-full bg-muted', props.class)"
  >
    <slot />
  </AvatarFallback>
</template>

<script setup lang="ts">
import { AvatarFallback, type AvatarFallbackProps } from 'radix-vue';
import { cn } from '@/shared/utils/lib-utils';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<AvatarFallbackProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});
</script>

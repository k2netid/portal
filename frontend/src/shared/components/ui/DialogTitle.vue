<template>
  <DialogTitle
    v-bind="delegatedProps"
    :class="cn('text-lg font-semibold leading-none tracking-tight', props.class)"
  >
    <slot />
  </DialogTitle>
</template>

<script setup lang="ts">
import { DialogTitle, type DialogTitleProps } from 'radix-vue';
import { cn } from '@/shared/utils/lib-utils';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<DialogTitleProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});
</script>

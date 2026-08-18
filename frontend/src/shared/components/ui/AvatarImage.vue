<template>
  <AvatarImage
    v-bind="delegatedProps"
    :class="cn('aspect-square h-full w-full', props.class)"
  />
</template>

<script setup lang="ts">
import { AvatarImage, type AvatarImageProps } from 'radix-vue';
import { cn } from '@/shared/utils/lib-utils';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<AvatarImageProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});
</script>

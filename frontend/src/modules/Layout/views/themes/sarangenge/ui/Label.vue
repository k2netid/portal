<template>
  <label
    :class="[
      'block text-sm font-semibold text-foreground/90 mb-1.5',
      className
    ]"
    v-bind="$attrs"
  >
    <slot />
  </label>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    class?: string;
  }>(),
  {
    class: '',
  }
);

const className = computed(() => props.class);
</script>

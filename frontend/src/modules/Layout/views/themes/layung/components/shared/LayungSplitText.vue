<template>
  <component
    :is="as"
    class="inline-block"
  >
    <slot>{{ text }}</slot>
  </component>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    as?: string;
    text?: string;
  }>(),
  {
    as: 'span',
    text: '',
  },
);
</script>

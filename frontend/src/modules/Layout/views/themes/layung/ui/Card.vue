<template>
  <div
    :class="[
      'layung-panel p-6 overflow-hidden',
      $attrs.class
    ]"
    v-bind="$attrs"
  >
    <slot />
  </div>
</template>

<script setup lang="ts">
// Pure structural Card primitive
</script>

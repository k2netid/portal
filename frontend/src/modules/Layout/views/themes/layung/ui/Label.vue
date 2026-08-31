<template>
  <label
    :class="[
      'block text-xs font-bold text-foreground mb-1.5 uppercase tracking-wider',
      $attrs.class
    ]"
    v-bind="$attrs"
  >
    <slot />
  </label>
</template>

<script setup lang="ts">
// Pure Label primitive
</script>

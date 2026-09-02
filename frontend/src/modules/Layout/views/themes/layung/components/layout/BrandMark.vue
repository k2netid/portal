<template>
  <div class="layung-brand-mark">
    <img
      :src="src"
      :alt="alt"
      class="layung-brand-mark__img"
      width="110"
      height="28"
    >
  </div>
</template>

<script setup lang="ts">
defineProps<{
  src: string;
  alt: string;
}>();
</script>

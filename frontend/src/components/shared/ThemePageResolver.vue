<template>
  <ThemePageResolver :page="page" v-bind="$attrs">
    <slot />
  </ThemePageResolver>
</template>

<script setup lang="ts">
import ThemePageResolver from '@/modules/Content/Layout/components/themes/ThemePageResolver.vue'

defineProps<{
  page: string
}>()
</script>

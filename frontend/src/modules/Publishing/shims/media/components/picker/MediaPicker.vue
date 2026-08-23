<template>
  <!-- Re-export: real picker lives in Media pack (P3-2). Kept for any stale imports. -->
  <MediaPicker v-bind="$attrs">
    <template v-for="(_, name) in $slots" #[name]="slotData">
      <slot :name="name" v-bind="slotData || {}" />
    </template>
  </MediaPicker>
</template>

<script setup lang="ts">
import MediaPicker from '@/modules/Media/components/picker/MediaPicker.vue';
</script>

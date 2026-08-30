<template>
  <div class="text-sm text-muted-foreground italic">
    Block preview requires the Layout pack.
  </div>
</template>

<script setup lang="ts">
defineProps<{
  blocks?: unknown;
}>();
</script>

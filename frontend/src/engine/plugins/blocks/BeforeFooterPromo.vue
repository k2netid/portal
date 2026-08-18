<template>
  <div
    class="ja-plugin-block rounded-lg border border-border bg-muted/40 px-4 py-3 text-center text-sm"
    :data-plugin="pluginSlug"
  >
    <p class="font-medium text-foreground">
      {{ title }}
    </p>
    <p class="text-xs text-muted-foreground mt-1">
      {{ description }}
    </p>
  </div>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    pluginSlug?: string;
    title?: string;
    description?: string;
  }>(),
  {
    pluginSlug: 'before-footer-promo',
    title: 'Promo area',
    description: 'Theme slot: before_footer — register via plugin settings.',
  },
);
</script>

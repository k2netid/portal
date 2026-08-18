<template>
  <div
    class="ja-plugin-block rounded-xl border border-primary/15 bg-primary/5 px-4 py-3 text-sm text-foreground"
    :data-plugin="pluginSlug"
  >
    <p class="font-semibold text-primary mb-1">
      {{ title }}
    </p>
    <p class="text-muted-foreground text-xs leading-relaxed">
      {{ description }}
    </p>
  </div>
</template>

<script setup lang="ts">
/* eslint-disable vue/prop-name-casing -- block attrs from CMS use snake_case */
withDefaults(
  defineProps<{
    pluginSlug?: string;
    post_id?: string | number;
    post_type?: string;
    slug?: string;
    title?: string;
    description?: string;
  }>(),
  {
    pluginSlug: 'content-share-bar',
    title: 'Bagikan artikel ini',
    description: 'Plugin App Block — terdaftar via theme slot setelah konten artikel.',
    post_id: undefined,
    post_type: undefined,
    slug: undefined,
  },
);
</script>

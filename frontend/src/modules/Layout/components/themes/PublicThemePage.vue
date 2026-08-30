<template>
  <ThemePageResolver :page="page" />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import ThemePageResolver from '@/modules/Layout/components/themes/ThemePageResolver.vue';

const route = useRoute();
const page = computed(() => {
  const meta = route.meta.themePage;
  return typeof meta === 'string' && meta !== '' ? meta : 'pages/Home';
});
</script>

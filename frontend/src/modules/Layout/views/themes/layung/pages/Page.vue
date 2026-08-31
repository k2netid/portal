<template>
  <div class="py-12 sm:py-16 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    <Breadcrumb :items="[{ name: page?.title || 'Halaman' }]" />

    <div
      v-if="page"
      class="space-y-8"
    >
      <div class="space-y-3">
        <h1 class="text-3xl sm:text-5xl font-extrabold text-foreground font-heading tracking-tight">
          {{ page.title }}
        </h1>
        <p
          v-if="page.excerpt"
          class="text-base text-muted-foreground leading-relaxed"
        >
          {{ page.excerpt }}
        </p>
      </div>

      <div class="prose dark:prose-invert max-w-none text-muted-foreground leading-relaxed">
        <ThemeSafeHtml
          v-if="page.body"
          :html="page.body"
        />
      </div>
    </div>

    <div
      v-else-if="loading"
      class="py-20 text-center text-muted-foreground font-mono text-xs"
    >
      Memuat halaman...
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import apiClient from '@/engine/api/client';

const route = useRoute();
const page = ref<any>(null);
const loading = ref(true);

onMounted(async () => {
  const slug = (route.params.slug as string) || route.path.replace(/^\//, '');
  try {
    const res = await apiClient.get(`/public/publishing/contents/${slug}`);
    if (res.data?.data) {
      page.value = res.data.data;
    }
  } catch {
    page.value = {
      title: 'Halaman Informasi Jaringan',
      body: '<p>Halaman ini dikelola secara dinamis melalui visual page builder dan CMS Jejakawan Core Engine.</p>',
    };
  } finally {
    loading.value = false;
  }
});
</script>

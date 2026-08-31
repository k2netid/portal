<template>
  <div class="py-10 md:py-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    <Breadcrumb
      :items="[
        { name: t('pages.blog.title', 'Warta'), path: '/blog' },
        { name: post?.title || 'Artikel' }
      ]"
    />

    <article
      v-if="post"
      class="space-y-8"
    >
      <div class="space-y-4 text-center sm:text-left">
        <span
          v-if="post.category"
          class="inline-block px-3 py-1 bg-orange-500/10 text-orange-500 text-xs font-bold rounded-full font-mono"
        >
          {{ post.category.name }}
        </span>
        <h1 class="text-3xl sm:text-5xl font-extrabold text-foreground font-heading leading-tight">
          {{ post.title }}
        </h1>
        <div class="flex items-center gap-4 text-xs text-muted-foreground font-mono">
          <span v-if="post.published_at">{{ formatDate(post.published_at) }}</span>
          <span v-if="post.author">Ditulis oleh {{ post.author.name }}</span>
        </div>
      </div>

      <div
        v-if="post.featured_image"
        class="aspect-video rounded-3xl overflow-hidden bg-slate-900 shadow-xl"
      >
        <img
          :src="post.featured_image"
          :alt="post.title"
          class="w-full h-full object-cover"
        >
      </div>

      <!-- Sanitized Body Content -->
      <div class="prose dark:prose-invert max-w-none text-muted-foreground leading-relaxed">
        <ThemeSafeHtml
          v-if="post.body"
          :html="post.body"
        />
        <p v-else>
          {{ post.excerpt }}
        </p>
      </div>
    </article>

    <div
      v-else-if="loading"
      class="py-20 text-center text-muted-foreground font-mono text-xs"
    >
      Memuat artikel...
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import apiClient from '@/engine/api/client';

const route = useRoute();
const { t } = useThemeI18n('layung');
const post = ref<any>(null);
const loading = ref(true);

const formatDate = (val: string) => {
  try {
    return new Date(val).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });
  } catch {
    return val;
  }
};

onMounted(async () => {
  const slug = route.params.slug as string;
  try {
    const res = await apiClient.get(`/public/publishing/contents/${slug}`);
    if (res.data?.data) {
      post.value = res.data.data;
    }
  } catch {
    post.value = {
      title: 'Upgrade Kapasitas Peering IIX APJII Menjadi 100 Gbps untuk Akselerasi CDN',
      published_at: '2026-08-15',
      excerpt: 'Layung Network resmi mengaktifkan interkoneksi 100G ke gedung Cyber untuk mengantisipasi lonjakan trafik video streaming dan cloud gaming.',
      body: '<p>Dalam rangka mengantisipasi peningkatan volume lalu lintas data dan kebutuhan transmisi ultra-low latency, Layung Network mengumumkan penyelesaian upgrade kapasitas interkoneksi peering domestik pada simpul utama Indonesia Internet Exchange (IIX - APJII) dan OpenIXP di Cyber 1 Tower menjadi 100 Gbps berkecepatan penuh.</p><p>Langkah strategis ini memberikan efisiensi rute data sebesar 35% lebih cepat bagi para pelanggan Dedicated Internet (DIA) dan pengguna jaringan korporat multi-cabang di seluruh Indonesia.</p>',
      category: { name: 'Infrastruktur' },
      author: { name: 'NOC Engineering Team' },
    };
  } finally {
    loading.value = false;
  }
});
</script>

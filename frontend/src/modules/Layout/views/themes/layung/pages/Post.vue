<template>
  <div class="py-10 md:py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    <Breadcrumb
      :items="[
        { name: t('pages.blog.title', 'Warta'), path: '/blog' },
        { name: post?.title || 'Artikel' }
      ]"
    />

    <div
      v-if="post"
      class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start"
    >
      <article class="lg:col-span-8 space-y-8">
        <div class="space-y-4 text-center sm:text-left">
          <span
            v-if="post.category"
            class="inline-block px-3 py-1 bg-sky-500/10 text-sky-500 text-xs font-bold rounded-full font-mono"
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

      <!-- Sidebar with Universal WidgetArea -->
      <aside class="lg:col-span-4 space-y-6">
        <WidgetArea
          location="sidebar"
          :context="{ post }"
        >
          <div class="space-y-6">
            <SearchWidget />
            <CategoriesWidget />
            <RecentPostsWidget :current-post-id="post?.id" />
            <SocialShareWidget :title="post?.title" />
          </div>
        </WidgetArea>
      </aside>
    </div>

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
import WidgetArea from '@/modules/Layout/components/widgets/WidgetArea.vue';
import SearchWidget from '@/modules/Layout/components/widgets/SearchWidget.vue';
import CategoriesWidget from '@/modules/Layout/components/widgets/CategoriesWidget.vue';
import RecentPostsWidget from '@/modules/Layout/components/widgets/RecentPostsWidget.vue';
import SocialShareWidget from '@/modules/Layout/components/widgets/SocialShareWidget.vue';
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
      title: 'Kami Mengoperasikan AS153992 sebagai Anggota Korporat IDNIC',
      published_at: '2026-08-15',
      excerpt: 'Penyedia Layanan Internet mengumumkan operasional BGP mandiri AS153992 (IDNIC-Kami-ID) untuk layanan ISP di Bandung.',
      body: '<p>Kami mengoperasikan prefix 165.99.252.0/24 di bawah AS153992 sebagai anggota korporat IDNIC. Langkah ini memperkuat identitas routing mandiri untuk layanan dedicated internet dan managed network.</p>',
      category: { name: 'Infrastruktur' },
      author: { name: 'NOC Engineering Team' },
    };
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="zenith-theme flex-1 flex flex-col py-12 sm:py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 w-full">
      <!-- Back Link -->
      <router-link
        to="/blog"
        class="inline-flex items-center gap-2 text-sm font-semibold text-muted-foreground hover:text-foreground transition-colors"
      >
        <ArrowLeft class="w-4 h-4" />
        {{ t('theme.zenith.pages.post.backToBlog', 'Back to Blog') }}
      </router-link>

      <div
        v-if="loading"
        class="min-h-[400px] flex items-center justify-center"
      >
        <div class="w-8 h-8 rounded-full border-2 border-primary border-t-transparent animate-spin" />
      </div>

      <article
        v-else-if="post"
        class="space-y-8"
      >
        <!-- Title & Meta -->
        <div class="space-y-4 text-center sm:text-left">
          <span
            v-if="post.category"
            class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-primary/10 text-primary border border-primary/20"
          >
            {{ post.category.name }}
          </span>
          <h1 class="text-3xl sm:text-5xl font-extrabold text-foreground font-heading leading-tight">
            {{ post.title }}
          </h1>
          <p class="text-sm text-muted-foreground">
            {{ t('theme.zenith.common.publishedOn', 'Published on') }} {{ post.published_at ? new Date(post.published_at).toLocaleDateString() : 'Recently' }}
          </p>
        </div>

        <!-- Featured Image -->
        <div
          v-if="post.featured_image"
          class="rounded-3xl overflow-hidden aspect-[16/9] border border-border/60 shadow-xl"
        >
          <img
            :src="post.featured_image"
            :alt="post.title"
            class="w-full h-full object-cover"
          >
        </div>

        <!-- Body Content -->
        <div class="prose prose-lg dark:prose-invert max-w-none text-foreground leading-relaxed pt-6">
          <ThemeSafeHtml :html="post.body || post.excerpt || ''" />
        </div>
      </article>

      <div
        v-else
        class="text-center py-20 text-muted-foreground"
      >
        {{ t('theme.zenith.pages.blog.noPosts', 'Article not found.') }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import { ArrowLeft } from 'lucide-vue-next';

interface Post {
  id: string | number;
  title: string;
  slug: string;
  excerpt?: string;
  body?: string;
  featured_image?: string;
  published_at?: string;
  category?: { name: string };
}

const { t } = useI18n();
const route = useRoute();
const post = ref<Post | null>(null);
const loading = ref(true);

onMounted(async () => {
  const slug = String(route.params.slug || '');
  if (!slug) {
    loading.value = false;
    return;
  }

  try {
    const res = await api.get(`/public/publishing/contents/${slug}`);
    if (res.data?.data) {
      post.value = res.data.data;
    }
  } catch {
    // Fallback demonstration post
    post.value = {
      id: 1,
      title: 'Building Modern Web Experiences with Vue 3 & Laravel',
      slug,
      body: '<p>Modern digital experiences demand lightning-fast reactivity paired with an uncompromising, rock-solid backend foundation. JA-CMS delivers both through its modular monolith architecture.</p><p>With reactive CSS tokens, live theme customizer hooks, and strict type safety across all domain boundaries, developers and editors can collaborate effortlessly.</p>',
      published_at: new Date().toISOString(),
      category: { name: 'Engineering' }
    };
  } finally {
    loading.value = false;
  }
});
</script>

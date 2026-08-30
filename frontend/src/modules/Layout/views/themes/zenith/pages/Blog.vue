<template>
  <div class="zenith-theme flex-1 flex flex-col py-12 sm:py-16">
    <BlockRenderer
      v-if="hasBuilderBlocks"
      :blocks="builderBlocks"
      :context="{ post: pageData, site: { name: 'Jejakawan' } }"
    />
    <SafeHtml
      v-else-if="cmsBody"
      class="container mx-auto px-4 py-16"
      :html="cmsBody"
      mode="publishing"
    />
    <template v-else>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 w-full">
      <!-- Header -->
      <div class="text-center space-y-4 max-w-3xl mx-auto">
        <h1 class="text-4xl sm:text-5xl font-extrabold text-foreground font-heading">
          {{ t('theme.zenith.pages.blog.title', 'Editorial & Insights') }}
        </h1>
        <p class="text-lg text-muted-foreground">
          {{ t('theme.zenith.pages.blog.subtitle', 'Articles, thoughts, and technical deep dives.') }}
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_18rem] gap-10 items-start">
      <div>
      <!-- Articles Grid -->
      <div
        v-if="loading"
        class="min-h-[300px] flex items-center justify-center"
      >
        <div class="w-8 h-8 rounded-full border-2 border-primary border-t-transparent animate-spin" />
      </div>

      <div
        v-else-if="posts.length > 0"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
      >
        <Card
          v-for="post in posts"
          :key="post.id"
          :hover="true"
          class="flex flex-col justify-between"
        >
          <div class="space-y-4">
            <div
              v-if="post.featured_image"
              class="rounded-xl overflow-hidden aspect-[16/10] bg-muted/30"
            >
              <img
                :src="post.featured_image"
                :alt="post.title"
                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                loading="lazy"
              >
            </div>

            <div class="space-y-2">
              <span
                v-if="post.category"
                class="text-xs font-semibold text-primary uppercase tracking-wider"
              >
                {{ post.category.name }}
              </span>
              <h3 class="text-xl font-bold text-foreground font-heading line-clamp-2">
                <router-link
                  :to="`/blog/${post.slug}`"
                  class="hover:text-primary transition-colors"
                >
                  {{ post.title }}
                </router-link>
              </h3>
              <p class="text-sm text-muted-foreground line-clamp-3 leading-relaxed">
                {{ post.excerpt || post.body?.replace(/<[^>]*>?/gm, '') }}
              </p>
            </div>
          </div>

          <div class="mt-6 pt-4 border-t border-border/40 flex items-center justify-between text-xs text-muted-foreground">
            <span>{{ post.published_at ? new Date(post.published_at).toLocaleDateString() : 'Recent' }}</span>
            <router-link
              :to="`/blog/${post.slug}`"
              class="font-semibold text-primary inline-flex items-center gap-1 hover:underline"
            >
              {{ t('theme.zenith.common.readMore', 'Read More') }}
              <ArrowRight class="w-3.5 h-3.5" />
            </router-link>
          </div>
        </Card>
      </div>

      <div
        v-else
        class="text-center py-16 text-muted-foreground"
      >
        {{ t('theme.zenith.pages.blog.noPosts', 'No articles published yet.') }}
      </div>
      </div>
        <WidgetArea location="sidebar" />
      </div>
    </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import SafeHtml from '@/modules/Core/System/components/ui/SafeHtml.vue';
import { Card } from '@/modules/Layout/views/themes/zenith/ui';
import WidgetArea from '@/modules/Layout/components/widgets/WidgetArea.vue';
import { ArrowRight } from 'lucide-vue-next';

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
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('blog');
const posts = ref<Post[]>([]);
const loading = ref(true);

onMounted(async () => {
  try {
    const res = await api.get('/public/publishing/contents', {
      params: { type: 'post', per_page: 9, status: 'published' }
    });
    if (res.data && Array.isArray(res.data.data)) {
      posts.value = res.data.data;
    }
  } catch {
    // Graceful fallback
    posts.value = [
      {
        id: 1,
        title: 'Building Modern Web Experiences with Vue 3 & Laravel',
        slug: 'building-modern-web-experiences',
        excerpt: 'Discover the architecture patterns that power zero-latency content delivery and seamless reactive design.',
        published_at: new Date().toISOString(),
        category: { name: 'Engineering' }
      },
      {
        id: 2,
        title: 'The Art of Minimalist Digital Design',
        slug: 'art-of-minimalist-digital-design',
        excerpt: 'Why clean typography, balanced spacing, and purposeful color choices create lasting user engagement.',
        published_at: new Date().toISOString(),
        category: { name: 'Design' }
      }
    ];
  } finally {
    loading.value = false;
  }
});
</script>

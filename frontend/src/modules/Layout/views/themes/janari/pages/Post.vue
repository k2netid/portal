<template>
  <div class="min-h-screen bg-background">
    <!-- Loading State -->
    <div
      v-if="loading"
      class="container mx-auto px-4 py-12"
    >
      <div class="animate-pulse space-y-8 max-w-3xl mx-auto">
        <div class="h-8 bg-muted rounded w-3/4" />
        <div class="h-4 bg-muted rounded w-1/2" />
        <div class="h-96 bg-muted rounded-xl" />
        <div class="space-y-4">
          <div class="h-4 bg-muted rounded" />
          <div class="h-4 bg-muted rounded" />
        </div>
      </div>
    </div>

    <!-- Post Content -->
    <article
      v-else-if="post"
      class="pb-20"
    >
      <!-- Header -->
      <!-- Header -->
      <header class="relative py-10 md:py-14 mb-6 md:mb-8 overflow-hidden border-b border-border/40">
        <div class="absolute inset-0 bg-gradient-to-b from-primary/5 to-background z-0" />
        <div class="container mx-auto px-4 text-center max-w-4xl relative z-10">
          <div class="flex items-center justify-center gap-3 mb-6 md:mb-8">
            <span
              v-if="post.category"
              class="px-3 md:px-4 py-1.5 bg-primary text-primary-foreground rounded-full text-[10px] md:text-xs font-bold tracking-wider uppercase shadow-sm"
            >
              {{ post.category.name }}
            </span>
            <span class="text-muted-foreground text-sm font-medium flex items-center gap-1">
              <Calendar class="w-4 h-4" />
              {{ post.published_at ? new Date(post.published_at).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' }) : '' }}
            </span>
          </div>
          <h1 class="text-2xl md:text-6xl font-bold text-foreground mb-6 md:mb-8 leading-tight tracking-tight text-balance">
            {{ post.title }}
          </h1>
          <div class="flex items-center justify-center gap-4">
            <div class="w-12 h-12 bg-muted rounded-full overflow-hidden ring-2 ring-background shadow-md">
              <!-- Author Avatar placeholder -->
              <User class="w-full h-full text-muted-foreground bg-muted" />
            </div>
            <div class="text-left">
              <p class="text-sm font-bold text-foreground">
                {{ post.author?.name || authorFallback }}
              </p>
              <p class="text-xs text-muted-foreground uppercase tracking-wider">
                {{ authorRole }}
              </p>
            </div>
          </div>
        </div>
      </header>

      <!-- Featured Image -->
      <div
        v-if="post.featured_image"
        class="container mx-auto px-4 mb-8 md:mb-12 max-w-5xl"
      >
        <div class="aspect-video w-full relative overflow-hidden rounded-xl shadow-lg border border-border">
          <img
            :src="post.featured_image"
            :alt="post.title"
            class="absolute inset-0 w-full h-full object-cover"
            width="1280"
            height="720"
            loading="eager"
            fetchpriority="high"
            decoding="async"
            sizes="(max-width: 1024px) 100vw, 1280px"
          >
        </div>
      </div>

      <!-- Content with Sidebar -->
      <div class="container mx-auto px-4 md:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
          <!-- Main Content -->
          <div class="lg:col-span-9">
            <div class="max-w-none">
              <BlockRenderer
                v-if="hasBuilderBlocks"
                :blocks="builderBlocks"
              />
              <SafeHtml
                v-else
                ref="contentRef"
                class="prose prose-sm md:prose-lg prose-indigo mx-auto dark:prose-invert"
                :html="post.body || ''"
                mode="publishing"
              />
                      
              <!-- Tags -->
              <div
                v-if="post.tags && post.tags.length > 0"
                class="mt-12 pt-8 border-t border-border"
              >
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="tag in post.tags"
                    :key="tag.id || tag.name"
                    class="text-sm text-muted-foreground px-3 py-1 bg-muted rounded-lg"
                  >
                    #{{ tag.name }}
                  </span>
                </div>
              </div>

              <PluginSlot
                name="after_post_content"
                class="mt-8"
                :context="{
                  post_id: post.id,
                  post_type: post.type,
                  slug: post.slug,
                }"
              />

              <!-- Public Comments Section -->
              <PublicComments
                v-if="post.comment_status !== false"
                :content-id="String(post.id)"
                :is-comments-open="true"
              />
            </div>
          </div>

          <!-- Sidebar -->
          <aside class="lg:col-span-3">
            <BlogSidebar />
            <PluginSlot name="sidebar_article" class="mt-8" :context="{ post_id: post.id, slug: post.slug }" />
          </aside>
        </div>
      </div>
    </article>
        
    <!-- Not Found -->
    <div
      v-else
      class="text-center py-20"
    >
      <h1 class="text-2xl font-bold text-foreground">
        {{ notFoundText }}
      </h1>
      <router-link
        to="/blog"
        class="text-primary hover:text-primary/80 mt-4 inline-block"
      >
        {{ backToBlogText }}
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import SafeHtml from '@/modules/Core/System/components/ui/SafeHtml.vue';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import PublicComments from '@/modules/Publishing/components/comments/PublicComments.vue';
import PluginSlot from '@/shared/components/PluginSlot.vue';
import BlogSidebar from '../components/blog/BlogSidebar.vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { normalizeLocaleCode } from '@/engine/i18n';
import { PublishingService } from '@/modules/Publishing/services/publishingService';
import { useJanariIdentity } from '@/modules/Layout/views/themes/janari/composables/useJanariIdentity';
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting';
import { pageUsesBuilderOverride } from '@/modules/Layout/composables/useThemePageOverride';

import { useIconHydration } from '@/shared/composables/useIconHydration';
import { Calendar, User } from 'lucide-vue-next';

import type { Content } from '@/modules/Publishing/types/content'

const route = useRoute();
const { locale, t } = useI18n({ useScope: 'global' });
const { localizedString } = useLocalizedThemeSetting();
const { hydrateIcons } = useIconHydration();

const post = ref<Content | null>(null);
const loading = ref(true);
const contentRef = ref<{ $el: HTMLElement } | null>(null);
const { displaySiteName } = useJanariIdentity();
const authorFallback = computed(() => {
  const tpl = localizedString('page_post_author_fallback') || '{site} Editorial'
  return tpl.replace(/\{site\}/g, displaySiteName.value || 'Jejakawan')
});
const authorRole = computed(() => localizedString('page_post_author_role') || t('theme.janari.pages.post.authorRole'));
const notFoundText = computed(() => localizedString('page_post_not_found') || t('publishing.frontend.post.notFound'));
const backToBlogText = computed(() => localizedString('page_post_back_to_blog') || t('publishing.frontend.post.backToBlog'));

const builderBlocks = computed(() => (post.value?.meta?.builder_blocks as any[]) || []);
const hasBuilderBlocks = computed(() => pageUsesBuilderOverride(post.value as Record<string, unknown> | null));

watch(() => post.value, () => {
    nextTick(() => {
        if (contentRef.value) {
            const el = contentRef.value.$el || contentRef.value;
            hydrateIcons(el);
        }
    });
}, { deep: true });

const loadPost = async () => {
    loading.value = true;
    try {
        const slug = route.params.slug as string;
        const response = await PublishingService.publicContent(slug, {
            locale: normalizeLocaleCode(locale.value),
        });
        post.value = response.data;
    } catch (error) {
        logger.error('Failed to load post:', error);
    } finally {
        loading.value = false;
        nextTick(() => {
            if (contentRef.value) {
                const el = contentRef.value.$el || contentRef.value;
                hydrateIcons(el);
            }
        });
    }
};

onMounted(() => {
    void loadPost();
});
watch(locale, () => {
    void loadPost();
});
</script>


<template>
  <div class="min-h-screen bg-background">
    <div
      v-if="loading"
      class="flex min-h-[55vh] items-center justify-center"
    >
      <div class="h-10 w-10 animate-spin rounded-full border-b-2 border-primary" />
    </div>

    <template v-else-if="pageData">
      <!-- CASE 1: BLANK CANVAS (LANDING PAGE FUNNEL - 100% Custom Visual Blocks) -->
      <div v-if="pageTemplate === 'blank_canvas'" class="w-full min-h-screen">
        <BlockRenderer v-if="hasBuilderBlocks" :blocks="builderBlocks" />
        <div v-else-if="hasBody" class="w-full prose prose-lg max-w-none px-4 py-8" v-html="pageBody" />
        <section v-else class="container mx-auto px-4 py-16">
          <div class="mx-auto max-w-2xl rounded-2xl border border-dashed border-border bg-card/40 p-10 text-center">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
              <Archive class="h-8 w-8 text-primary" />
            </div>
            <h2 class="mt-4 text-2xl font-bold tracking-tight text-foreground md:text-3xl">
              {{ pageTitle }}
            </h2>
            <p class="mt-4 text-muted-foreground">
              {{ t('theme.janari.pages.page.emptyPublished') }}
            </p>
          </div>
        </section>
      </div>

      <!-- CASE 2: STANDARD / FULL WIDTH TEMPLATES -->
      <div v-else class="w-full">
        <section class="border-y border-border bg-gradient-to-b from-primary/[0.09] via-primary/[0.04] to-background">
          <div class="container mx-auto px-4 py-12 md:py-16">
            <h1 class="mt-6 text-3xl font-bold tracking-tight text-foreground md:text-5xl lg:text-6xl">
              <JanariSplitText :text="pageTitle" />
            </h1>
            <SafeHtml
              v-if="pageIntro"
              class="mt-5 max-w-3xl text-sm leading-relaxed text-muted-foreground md:text-base prose prose-sm prose-p:my-3 prose-strong:text-foreground prose-a:text-primary"
              :html="pageIntro"
              mode="Jejakawan"
            />
          </div>
        </section>

        <PluginSlot name="after_hero" class="w-full" />

        <section
          v-if="hasFeaturedImage && featuredImagePosition === 'hero'"
          class="container mx-auto px-4 pt-8"
        >
          <figure class="overflow-hidden rounded-2xl border border-border bg-card/40 shadow-sm">
            <div class="relative h-[240px] w-full md:h-[360px] lg:h-[440px]">
              <img
                :src="pageData?.featured_image || ''"
                :alt="featuredImageAlt"
                class="h-full w-full object-cover"
                width="1440"
                height="810"
                loading="eager"
                fetchpriority="high"
                decoding="async"
                sizes="100vw"
              >
              <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />
              <div class="absolute bottom-0 left-0 right-0 p-5 md:p-7">
                <p
                  v-if="featuredImageHeading"
                  class="text-lg font-black uppercase tracking-tight text-white md:text-2xl"
                >
                  {{ featuredImageHeading }}
                </p>
              </div>
            </div>
            <figcaption
              v-if="featuredImageCaptionText"
              class="border-t border-border/40 bg-background/70 px-5 py-4 text-sm leading-relaxed text-muted-foreground md:px-7"
            >
              {{ featuredImageCaptionText }}
            </figcaption>
          </figure>
        </section>

        <section
          v-if="hasFeaturedImage && featuredImagePosition === 'full-bleed'"
          class="pt-8"
        >
          <figure class="overflow-hidden border-y border-border bg-card/40 shadow-sm">
            <div class="relative h-[260px] w-full md:h-[420px] lg:h-[520px]">
              <img
                :src="pageData?.featured_image || ''"
                :alt="featuredImageAlt"
                class="h-full w-full object-cover"
                width="1920"
                height="900"
                loading="eager"
                fetchpriority="high"
                decoding="async"
                sizes="100vw"
              >
              <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent" />
              <div class="absolute bottom-0 left-0 right-0 mx-auto w-full max-w-6xl px-4 pb-7 md:px-8 md:pb-10">
                <p
                  v-if="featuredImageHeading"
                  class="text-xl font-black uppercase tracking-tight text-white md:text-3xl"
                >
                  {{ featuredImageHeading }}
                </p>
              </div>
            </div>
            <figcaption
              v-if="featuredImageCaptionText"
              class="mx-auto max-w-6xl px-4 py-4 text-sm leading-relaxed text-muted-foreground md:px-8"
            >
              {{ featuredImageCaptionText }}
            </figcaption>
          </figure>
        </section>

        <!-- Visual Builder Layout (if page is built with Visual Page Builder) -->
        <section
          v-if="hasBuilderBlocks"
          class="w-full py-6 md:py-10"
        >
          <div :class="pageTemplate === 'full_width' ? 'w-full' : 'container mx-auto px-4'">
            <BlockRenderer :blocks="builderBlocks" />
          </div>
        </section>

        <!-- Standard Editorial Layout -->
        <section
          v-else-if="hasBody"
          class="py-10 md:py-14"
        >
          <div class="container mx-auto grid grid-cols-1 gap-8 px-4 lg:grid-cols-12">
            <aside class="lg:col-span-3">
              <div class="sticky top-24 rounded-xl border border-border bg-card/60 p-5 backdrop-blur">
                <p class="text-[10px] font-bold tracking-wider text-muted-foreground">{{ t('theme.janari.pages.page.metaLabel') }}</p>
                <div class="mt-4 space-y-3 text-sm text-muted-foreground">
                  <div>
                    <p class="text-[10px] uppercase tracking-[0.2em]">{{ t('theme.janari.pages.page.typeLabel') }}</p>
                    <p class="mt-1 font-semibold text-foreground/80">{{ pageTypeLabel }}</p>
                  </div>
                </div>
              </div>
            </aside>

            <article class="lg:col-span-9">
              <div class="rounded-2xl border border-border bg-card/50 p-6 shadow-sm md:p-10">
                <figure
                  v-if="hasFeaturedImage && featuredImagePosition === 'inline-top'"
                  class="mb-8 overflow-hidden rounded-xl border border-border/60 bg-background/80"
                >
                  <img
                    :src="pageData?.featured_image || ''"
                    :alt="featuredImageAlt"
                    class="h-[220px] w-full object-cover md:h-[340px]"
                    width="1200"
                    height="680"
                    loading="lazy"
                    decoding="async"
                    sizes="(max-width: 1024px) 100vw, 75vw"
                  >
                  <figcaption
                    v-if="featuredImageCaptionText"
                    class="border-t border-border/40 px-4 py-3 text-xs leading-relaxed text-muted-foreground md:px-5 md:text-sm"
                  >
                    <span>{{ featuredImageCaptionText }}</span>
                  </figcaption>
                </figure>
                <SafeHtml
                  class="janari-page-content prose prose-slate max-w-none prose-p:my-4 prose-p:leading-relaxed prose-headings:font-black prose-headings:tracking-tight prose-a:text-primary prose-ul:my-4 prose-ol:my-4"
                  :html="pageBody"
                  mode="Jejakawan"
                />
              </div>
            </article>
          </div>
        </section>

        <section
          v-else
          class="container mx-auto px-4 py-16"
        >
          <div class="mx-auto max-w-2xl rounded-2xl border border-dashed border-border bg-card/40 p-10 text-center">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
              <Archive class="h-8 w-8 text-primary" />
            </div>
            <h2 class="mt-4 text-2xl font-bold tracking-tight text-foreground md:text-3xl">
              {{ pageTitle }}
            </h2>
            <p class="mt-4 text-muted-foreground">
              {{ t('theme.janari.pages.page.emptyPublished') }}
            </p>
          </div>
        </section>
      </div>
    </template>

    <section
      v-else
      class="container mx-auto px-4 py-20"
    >
      <div class="mx-auto max-w-2xl rounded-2xl border border-border bg-card/40 p-10 text-center">
        <h1 class="mt-4 text-3xl font-bold tracking-tight text-foreground md:text-4xl">
          {{ t('theme.janari.pages.page.notFoundTitle') }}
        </h1>
        <p class="mt-4 text-muted-foreground">
          {{ t('theme.janari.pages.page.notFoundDescription') }}
        </p>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { PluginSlot } from '@/shared/components'
import { logger } from '@/shared/utils/logger';
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import SafeHtml from '@/modules/Core/System/components/ui/SafeHtml.vue';
import { useRoute, useRouter } from 'vue-router';
import JanariSplitText from '../components/shared/JanariSplitText.vue';
import BlockRenderer from '@/modules/Content/Layout/components/content-renderer/BlockRenderer.vue';
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';
import { normalizeLocaleCode } from '@/engine/i18n';
import { resolveLocalizedContentField } from '@/modules/Content/Layout/utils/resolveLocalizedContent';
import { Archive } from 'lucide-vue-next';
import {
    hasSubstantivePublicContent,
    isPublicContentRecord,
} from '@/modules/Content/Publishing/utils/publicContent';

import type { Content } from '@/modules/Content/Publishing/types/content'

const { t, locale } = useI18n({ useScope: 'global' });
const route = useRoute();
const router = useRouter();
const pageData = ref<Content | null>(null);
const loading = ref(true);

const pageSlug = computed(() => (route.params.slug as string) || '');
const pageTitle = computed(() => {
    const localized = resolveLocalizedContentField(pageData.value, 'title', locale.value);
    return localized || pageSlug.value.replace(/-/g, ' ') || t('theme.janari.common.page');
});
const pageIntro = computed(() => resolveLocalizedContentField(pageData.value, 'intro', locale.value).trim());
const pageBody = computed(() => resolveLocalizedContentField(pageData.value, 'body', locale.value));
const hasBody = computed(() => Boolean(pageBody.value.trim()));

const pageTemplate = computed(() => {
    const meta = pageData.value?.meta as Record<string, any> | undefined;
    return (meta?.template as string) || 'default';
});

const builderBlocks = computed(() => {
    const meta = pageData.value?.meta as Record<string, any> | undefined;
    if (meta?.builder_blocks && Array.isArray(meta.builder_blocks) && meta.builder_blocks.length > 0) {
        return meta.builder_blocks;
    }
    return [];
});
const hasBuilderBlocks = computed(() => builderBlocks.value.length > 0);
const hasFeaturedImage = computed(() => Boolean(pageData.value?.featured_image));
const pageTypeLabel = computed(() => (pageData.value?.type || 'page').toUpperCase());
const featuredImagePosition = computed(() => {
    const raw = pageData.value?.featured_image_position;
    if (raw === 'inline-top' || raw === 'full-bleed') return raw;
    return 'hero';
});
const featuredImageHeading = computed(() => pageData.value?.featured_image_title?.trim() || '');
const featuredImageCaptionText = computed(() => pageData.value?.featured_image_caption?.trim() || '');
const featuredImageAlt = computed(() => featuredImageHeading.value || pageTitle.value);

const fetchPage = async () => {
    const slug = pageSlug.value;

    if (!slug) {
        loading.value = false;
        pageData.value = null;
        return;
    }

    loading.value = true;
    try {
        const response = await api.get(publishingPaths.publicContent(slug), {
            params: { locale: normalizeLocaleCode(locale.value) },
        });
        const payload = response.data;

        if (!isPublicContentRecord(payload) || !hasSubstantivePublicContent(payload)) {
            pageData.value = null;
            await router.replace({ name: 'not-found' });
            return;
        }

        pageData.value = payload;
    } catch (error) {
        logger.error('Failed to load page:', error);
        pageData.value = null;
        await router.replace({ name: 'not-found' });
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    void fetchPage();
    if (typeof window !== 'undefined') {
        window.addEventListener('language-changed', fetchPage);
    }
});
onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('language-changed', fetchPage);
    }
});
watch(() => route.params.slug, fetchPage);
watch(locale, fetchPage);
</script>

<style scoped>
:deep(.janari-page-content p) {
  margin-top: 1rem !important;
  margin-bottom: 1rem !important;
  line-height: 1.85 !important;
}

:deep(.janari-page-content p:first-child) {
  margin-top: 0 !important;
}

:deep(.janari-page-content p:last-child) {
  margin-bottom: 0 !important;
}
</style>


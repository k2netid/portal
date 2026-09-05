<template>
  <div class="sarangenge-theme flex-1 flex flex-col py-10 md:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 w-full">
      <Breadcrumb
        v-if="pageTitle"
        :items="[{ name: pageTitle }]"
      />

      <BlockRenderer
        v-if="hasBuilderBlocks"
        :blocks="builderBlocks"
        :context="{ post: pageData, site: { name: displaySchoolName } }"
      />

      <article
        v-else-if="pageHtml || pageData"
        class="sarangenge-panel p-8 sm:p-12 space-y-6"
      >
        <h1
          v-if="pageTitle"
          class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight"
        >
          {{ pageTitle }}
        </h1>
        <div
          v-if="pageFeaturedImage"
          class="rounded-2xl sm:rounded-3xl overflow-hidden aspect-[16/9] border border-border/60 shadow-lg bg-muted"
        >
          <img
            :src="pageFeaturedImage"
            :alt="pageTitle"
            class="w-full h-full object-cover"
          />
        </div>
        <div class="prose prose-lg dark:prose-invert max-w-none text-foreground leading-relaxed">
          <ThemeSafeHtml :html="pageHtml" />
        </div>
      </article>

      <PageDisabled
        v-else
        :title="t('pages.disabled.defaultTitle', 'Halaman Tidak Ditemukan')"
        :message="t('pages.disabled.defaultMessage', 'Halaman yang Anda cari sedang tidak aktif atau belum dipublikasikan.')"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '@/modules/Layout/views/themes/sarangenge/components/shared/Breadcrumb.vue';
import PageDisabled from '@/modules/Layout/views/themes/sarangenge/components/shared/PageDisabled.vue';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';

const route = useRoute();
const { t } = useThemeI18n('sarangenge');
const { displaySchoolName } = useSarangengeIdentity();

const pageSlug = computed(() => {
  const segment = (route.params.slug as string) || route.path.replace(/^\//, '').split('/')[0] || 'page';
  return segment;
});

const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride(pageSlug.value);

const pageTitle = computed(() => {
  const title = pageData.value?.title;
  return typeof title === 'string' ? title : '';
});

const pageFeaturedImage = computed(() => {
  const img = pageData.value?.featured_image;
  return typeof img === 'string' && img.trim() !== '' ? img : null;
});

const pageHtml = computed(() => {
  const body = cmsBody.value || pageData.value?.body || '';
  return typeof body === 'string' ? body : '';
});
</script>

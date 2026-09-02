<template>
  <div class="py-10 md:py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
    <Breadcrumb :items="[{ name: t('pages.careers.title', 'Karir') }]" />

    <template v-if="hasBuilderBlocks">
      <BlockRenderer
        :blocks="builderBlocks"
        :context="{ post: pageData, site: { name: displayCompanyName } }"
      />
    </template>

    <template v-else-if="cmsBody">
      <div class="prose dark:prose-invert max-w-none text-muted-foreground leading-relaxed">
        <ThemeSafeHtml :html="cmsBody" />
      </div>
    </template>

    <template v-else>
      <div class="space-y-4 max-w-3xl">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 font-mono uppercase">
          {{ t('pages.careers.badge', 'Karir') }}
        </span>
        <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
          {{ t('pages.careers.title', 'Karir') }}
        </h1>
        <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
          {{ t('pages.careers.subtitle', 'Bergabung dengan tim operasional K2NET di Bandung.') }}
        </p>
      </div>

      <div class="layung-panel p-6 sm:p-10 max-w-3xl space-y-6">
        <p class="text-sm text-muted-foreground leading-relaxed">
          {{ t('pages.careers.body', 'Lowongan dibuka sesuai kebutuhan operasional. Kirim CV dan pengantar singkat melalui halaman kontak.') }}
        </p>
        <Button
          as="router-link"
          to="/contact"
          variant="primary"
          size="md"
          class="font-bold"
        >
          {{ t('pages.careers.cta', 'Kirim lamaran') }}
        </Button>
      </div>

      <CtaSection />
    </template>
  </div>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import { Button } from '@/modules/Layout/views/themes/layung/ui';
import { useLayungIdentity } from '../composables/useLayungIdentity';

const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('careers');
</script>

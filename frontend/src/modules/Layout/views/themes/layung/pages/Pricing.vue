<template>
  <div
    class="layung-page flex-1 flex flex-col space-y-8 sm:space-y-12 w-full py-8 sm:py-10 md:py-12 overflow-x-clip"
    data-ja-customizer-target="pricing"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full space-y-6">
      <Breadcrumb :items="[{ name: t('pages.pricing.hubTitle', 'Paket & Harga') }]" />

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
            {{ t('pages.pricing.hubBadge', 'Paket & Harga') }}
          </span>
          <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
            {{ t('pages.pricing.hubTitle', 'Paket Layanan K2NET') }}
          </h1>
          <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
            {{ t('pages.pricing.hubSubtitle', 'Pilih antara paket konektivitas internet (ISP) atau layanan IT terkelola (MSP) — masing-masing dengan skema dan harga yang disesuaikan kebutuhan Anda.') }}
          </p>
        </div>
      </template>
    </div>

    <template v-if="!hasBuilderBlocks && !cmsBody">
      <PricingHubSection />
    </template>
  </div>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import PricingHubSection from '../components/sections/PricingHubSection.vue';
import { useLayungIdentity } from '../composables/useLayungIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';

const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('pricing');

useThemeHashScroll(72);
</script>

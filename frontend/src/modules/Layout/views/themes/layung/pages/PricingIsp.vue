<template>
  <div
    class="py-10 md:py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10"
    data-ja-customizer-target="pricing/isp"
  >
    <Breadcrumb
      :items="[
        { name: t('pages.pricing.hubTitle', 'Paket & Harga'), path: '/pricing' },
        { name: t('pages.pricingIsp.title', 'Paket Internet (ISP)') },
      ]"
    />

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
          {{ t('pages.pricingIsp.badge', 'Paket Internet — ISP') }}
        </span>
        <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
          {{ t('pages.pricingIsp.title', 'Paket Konektivitas Internet K2NET') }}
        </h1>
        <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
          {{ t('pages.pricingIsp.subtitle', 'Dedicated Internet untuk bisnis dan institusi, Broadband Bisnis untuk SOHO, serta Retail Broadband untuk rumah tangga di Bandung & Jawa Barat.') }}
        </p>
      </div>

      <section
        id="packages"
        class="scroll-mt-20"
      >
        <IspPackagesSection />
      </section>

      <FaqSection />
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
import IspPackagesSection from '../components/sections/IspPackagesSection.vue';
import FaqSection from '../components/sections/FaqSection.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import { useLayungIdentity } from '../composables/useLayungIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';

const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('pricing-isp');

useThemeHashScroll(72);
</script>

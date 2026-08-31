<template>
  <div class="py-12 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
    <Breadcrumb :items="[{ name: t('pages.pricing.title', 'Paket & Bandwidth') }]" />

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
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 font-mono uppercase">
          Transparansi Biaya & SLA
        </span>
        <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
          {{ t('pages.pricing.title', 'Transparansi Biaya & Rincian Paket Bandwidth') }}
        </h1>
        <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
          {{ t('pages.pricing.subtitle', 'Investasi konektivitas yang jelas, tanpa biaya tersembunyi, dilengkapi jaminan Service Level Agreement (SLA).') }}
        </p>
      </div>

      <!-- Simulator -->
      <SpeedCalculatorSection />

      <!-- Packages Table / Cards -->
      <section
        id="packages"
        class="scroll-mt-28"
      >
        <PackagesSection />
      </section>

      <!-- SLA Guarantee Box -->
      <SlaGuaranteeSection />

      <!-- FAQ -->
      <FaqSection />

      <!-- CTA -->
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
import SpeedCalculatorSection from '../components/sections/SpeedCalculatorSection.vue';
import PackagesSection from '../components/sections/PackagesSection.vue';
import SlaGuaranteeSection from '../components/sections/SlaGuaranteeSection.vue';
import FaqSection from '../components/sections/FaqSection.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import { useLayungIdentity } from '../composables/useLayungIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';

const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('pricing');

useThemeHashScroll(128);
</script>

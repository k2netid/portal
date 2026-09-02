<template>
  <div class="py-10 md:py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
    <Breadcrumb
      :items="[
        { name: t('pages.pricing.hubTitle', 'Paket & Harga'), path: '/pricing' },
        { name: t('pages.pricingMsp.title', 'Managed Services (MSP)') },
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
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-mono uppercase">
          {{ t('pages.pricingMsp.badge', 'Managed Services — MSP') }}
        </span>
        <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
          {{ t('pages.pricingMsp.title', 'Paket Managed Services K2NET') }}
        </h1>
        <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
          {{ t('pages.pricingMsp.subtitle', 'Pendampingan IT operasional untuk sekolah dan institusi — dari instalasi jaringan hingga perawatan server, aplikasi, dan CCTV.') }}
        </p>
      </div>

      <section
        id="packages"
        class="scroll-mt-20"
      >
        <MspPackagesSection />
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
import MspPackagesSection from '../components/sections/MspPackagesSection.vue';
import FaqSection from '../components/sections/FaqSection.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import { useLayungIdentity } from '../composables/useLayungIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';

const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('pricing-msp');

useThemeHashScroll(72);
</script>

<template>
  <div class="py-12 sm:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
    <Breadcrumb :items="[{ name: t('pages.solusi.title', 'Managed Services') }]" />

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
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 font-mono uppercase">
          Managed IT & Cyber SOC
        </span>
        <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
          {{ t('pages.solusi.title', 'Managed IT & Cyber Security Solutions') }}
        </h1>
        <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
          {{ t('pages.solusi.subtitle', 'Transformasi digital aman dengan pendampingan penuh dari tim Network Operations Center (NOC) dan Security Operations Center (SOC) 24/7.') }}
        </p>
      </div>

      <!-- Managed Services Grid -->
      <section
        id="soc"
        class="scroll-mt-28"
      >
        <ManagedServicesSection />
      </section>

      <!-- SLA Assurance -->
      <section
        id="sdwan"
        class="scroll-mt-28"
      >
        <SlaGuaranteeSection />
      </section>

      <!-- Action -->
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
import ManagedServicesSection from '../components/sections/ManagedServicesSection.vue';
import SlaGuaranteeSection from '../components/sections/SlaGuaranteeSection.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import { useLayungIdentity } from '../composables/useLayungIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';

const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('solusi');

useThemeHashScroll(128);
</script>

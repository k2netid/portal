<template>
  <div class="py-10 md:py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
    <Breadcrumb :items="[{ name: t('pages.services.title', 'Konektivitas Fiber') }]" />

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
          Infrastruktur Serat Optik
        </span>
        <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
          {{ t('pages.services.title', 'Layanan Fiber Optik & Jaringan Terdedikasi') }}
        </h1>
        <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
          {{ t('pages.services.subtitle', 'Koneksi internet tanpa kompromi: Dark Fiber, Metro Ethernet, dan Dedicated Internet Access (DIA) simetris 1:1.') }}
        </p>
      </div>

      <!-- Bento Grid -->
      <section
        id="dia"
        class="scroll-mt-28"
      >
        <IspBentoSection />
      </section>

      <!-- Bandwidth Packages -->
      <section
        id="dark-fiber"
        class="scroll-mt-28"
      >
        <PackagesSection />
      </section>

      <!-- Topology -->
      <section
        id="topology"
        class="scroll-mt-28"
      >
        <NetworkTopologySection />
      </section>

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
import IspBentoSection from '../components/sections/IspBentoSection.vue';
import PackagesSection from '../components/sections/PackagesSection.vue';
import NetworkTopologySection from '../components/sections/NetworkTopologySection.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import { useLayungIdentity } from '../composables/useLayungIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';

const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('services');

useThemeHashScroll(128);
</script>

<template>
  <LayungPageGate
    setting-key="enable_solusi"
    :title="t('pages.solusi.title', 'Managed Services')"
  >
  <div
    class="layung-page flex-1 flex flex-col space-y-8 sm:space-y-12 w-full py-8 sm:py-10 md:py-12 overflow-x-clip"
    data-ja-customizer-target="solusi"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full space-y-6">
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
            {{ t('pages.solusi.badge', 'Managed Services') }}
          </span>
          <h1 class="text-4xl sm:text-5xl font-black text-foreground font-heading tracking-tight">
            {{ t('pages.solusi.title', 'Managed Services') }}
          </h1>
          <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
            {{ t('pages.solusi.subtitle', 'Pendampingan IT operasional untuk sekolah dan institusi — jaringan, server, CCTV, dan dukungan harian.') }}
          </p>
        </div>
      </template>
    </div>

    <template v-if="!hasBuilderBlocks && !cmsBody">
      <!-- Managed Services Section (handles its own max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full) -->
      <ManagedServicesSection />
    </template>
  </div>
  </LayungPageGate>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import LayungPageGate from '../components/shared/LayungPageGate.vue';
import ManagedServicesSection from '../components/sections/ManagedServicesSection.vue';
import { useLayungIdentity } from '../composables/useLayungIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';

const { t } = useThemeI18n('layung');
const { displayCompanyName } = useLayungIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('solusi');

useThemeHashScroll(72);
</script>

<template>
  <div class="sareupna-page flex-1 flex flex-col space-y-0 w-full min-h-0">
    <!-- Visual Builder Slot (when page has CMS blocks) -->
    <template v-if="hasBuilderBlocks">
      <BlockRenderer
        v-if="builderBlocks && builderBlocks.length"
        :blocks="builderBlocks"
      />
    </template>

    <!-- Standard Sareupna Flagship Layout -->
    <template v-else>
      <!-- Hero Section -->
      <div id="section-hero">
        <Hero />
      </div>

      <!-- Bento Grid Section -->
      <div id="section-bento">
        <BentoGridSection />
      </div>

      <!-- Developer Terminal Showcase -->
      <div id="section-terminal">
        <TerminalShowcaseSection />
      </div>

      <!-- Products & Modules Section -->
      <div id="section-products">
        <ProductsSection />
      </div>

      <!-- Conversion CTA Banner -->
      <div id="section-cta">
        <CtaSection />
      </div>

      <!-- Floating Side Navigation Dots -->
      <SectionNavDots
        v-if="showSideNavDots && visibleNavSections.length > 0"
        :sections="visibleNavSections"
        :style-preset="sideNavStyle"
      />
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import Hero from '../components/sections/Hero.vue';
import BentoGridSection from '../components/sections/BentoGridSection.vue';
import TerminalShowcaseSection from '../components/sections/TerminalShowcaseSection.vue';
import ProductsSection from '../components/sections/ProductsSection.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import SectionNavDots from '../components/shared/SectionNavDots.vue';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';

const { getSetting } = useTheme();
const { t } = useThemeI18n('sareupna');

// Visual Builder Detection
const builderBlocks = computed(() => {
  const blocks = getSetting('builder_blocks', null);
  return Array.isArray(blocks) ? blocks : null;
});

const hasBuilderBlocks = computed(() => {
  return Array.isArray(builderBlocks.value) && builderBlocks.value.length > 0;
});

// Side Navigation Dots Configuration
const showSideNavDots = computed(() => Boolean(getSetting('home_side_nav_dots', true)));
const sideNavStyle = computed(() => {
  const val = String(getSetting('home_side_nav_style', 'glass') || 'glass');
  if (['glass', 'minimal', 'glow', 'bars'].includes(val)) {
    return val as 'glass' | 'minimal' | 'glow' | 'bars';
  }
  return 'glass';
});

const visibleNavSections = computed(() => [
  { id: 'section-hero', label: t('navDots.hero') },
  { id: 'section-bento', label: t('navDots.bento') },
  { id: 'section-terminal', label: t('navDots.terminal') },
  { id: 'section-products', label: t('navDots.products') },
  { id: 'section-cta', label: t('navDots.cta') },
]);
</script>

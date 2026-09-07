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
      <div v-if="isSectionVisible('hero')" id="section-hero">
        <Hero />
      </div>

      <!-- Bento Grid Section -->
      <div v-if="isSectionVisible('bento')" id="section-bento">
        <BentoGridSection />
      </div>

      <!-- Developer Terminal Showcase -->
      <div v-if="isSectionVisible('terminal')" id="section-terminal">
        <TerminalShowcaseSection />
      </div>

      <!-- Products & Modules Section -->
      <div v-if="isSectionVisible('products')" id="section-products">
        <ProductsSection />
      </div>

      <!-- Cloud Specs & SLA Telemetry -->
      <div v-if="isSectionVisible('specs')" id="section-specs">
        <SpecsSection />
      </div>

      <!-- Conversion CTA Banner -->
      <div v-if="isSectionVisible('cta')" id="section-cta">
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
import SpecsSection from '../components/sections/SpecsSection.vue';
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

// Section Visibility Filter
const homeSections = computed<string[]>(() => {
  const val = getSetting('home_sections', ['hero', 'bento', 'terminal', 'products', 'specs', 'cta']);
  return Array.isArray(val) ? val : ['hero', 'bento', 'terminal', 'products', 'specs', 'cta'];
});

const isSectionVisible = (key: string) => homeSections.value.includes(key);

// Side Navigation Dots Configuration
const showSideNavDots = computed(() => Boolean(getSetting('home_side_nav_dots', true)));
const sideNavStyle = computed(() => {
  const val = String(getSetting('home_side_nav_style', 'glass') || 'glass');
  if (['glass', 'minimal', 'glow', 'bars'].includes(val)) {
    return val as 'glass' | 'minimal' | 'glow' | 'bars';
  }
  return 'glass';
});

const visibleNavSections = computed(() => {
  const sections = [];
  if (isSectionVisible('hero')) sections.push({ id: 'section-hero', label: t('navDots.hero') });
  if (isSectionVisible('bento')) sections.push({ id: 'section-bento', label: t('navDots.bento') });
  if (isSectionVisible('terminal')) sections.push({ id: 'section-terminal', label: t('navDots.terminal') });
  if (isSectionVisible('products')) sections.push({ id: 'section-products', label: t('navDots.products') });
  if (isSectionVisible('specs')) sections.push({ id: 'section-specs', label: t('navDots.specs') });
  if (isSectionVisible('cta')) sections.push({ id: 'section-cta', label: t('navDots.cta') });
  return sections;
});
</script>

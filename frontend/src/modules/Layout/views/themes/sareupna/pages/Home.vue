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

      <!-- Plugin Slot: After Hero (e.g. Instagram Feed) -->
      <PluginSlot name="after_hero" class="w-full" />

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
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import Hero from '../components/sections/Hero.vue';
import BentoGridSection from '../components/sections/BentoGridSection.vue';
import TerminalShowcaseSection from '../components/sections/TerminalShowcaseSection.vue';
import ProductsSection from '../components/sections/ProductsSection.vue';
import SpecsSection from '../components/sections/SpecsSection.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import PluginSlot from '@/shared/components/PluginSlot.vue';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';

const { getSetting } = useTheme();

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
</script>

<template>
  <div class="layung-home-view flex-1 flex flex-col space-y-0">
    <!-- Hero Section with Laser Grid & Coverage Checker -->
    <div v-if="isSectionVisible('hero')" id="section-hero">
      <Hero />
    </div>

    <!-- Plugin Slot: After Hero (e.g. Instagram Feed) -->
    <PluginSlot name="after_hero" class="w-full" />

    <!-- Bento Infrastructure Grid -->
    <div v-if="isSectionVisible('services') || isSectionVisible('bento')" id="section-services">
      <IspBentoSection />
    </div>

    <!-- Interactive Bandwidth Simulator -->
    <div
      v-if="calculatorEnabled && (isSectionVisible('calculator') || isSectionVisible('simulator'))"
      id="section-calculator"
      class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full"
    >
      <SpeedCalculatorSection />
    </div>

    <!-- SLA Guarantee -->
    <div v-if="isSectionVisible('sla') || isSectionVisible('guarantee')" id="section-sla">
      <SlaGuaranteeSection />
    </div>

    <!-- Managed IT & SOC Services -->
    <div v-if="isSectionVisible('managed_services') || isSectionVisible('msp')" id="section-managed-services">
      <ManagedServicesSection />
    </div>

    <!-- Enterprise Client Testimonials -->
    <div v-if="isSectionVisible('testimonials') || isSectionVisible('partners')" id="section-testimonials">
      <TestimonialsSection />
    </div>

    <!-- Technical & Provisioning FAQ -->
    <div v-if="isSectionVisible('faq')" id="section-faq">
      <FaqSection />
    </div>

    <!-- Urgent NOC Hotline & Quotation CTA -->
    <div v-if="isSectionVisible('cta')" id="section-cta">
      <CtaSection />
    </div>

    <PluginSlot
      name="home-bottom"
      class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
      :context="{ theme: 'layung' }"
    />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';
import PluginSlot from '@/shared/components/PluginSlot.vue';
import Hero from '../components/sections/Hero.vue';
import IspBentoSection from '../components/sections/IspBentoSection.vue';
import SpeedCalculatorSection from '../components/sections/SpeedCalculatorSection.vue';
import SlaGuaranteeSection from '../components/sections/SlaGuaranteeSection.vue';
import ManagedServicesSection from '../components/sections/ManagedServicesSection.vue';
import TestimonialsSection from '../components/sections/TestimonialsSection.vue';
import FaqSection from '../components/sections/FaqSection.vue';
import CtaSection from '../components/sections/CtaSection.vue';

const { getSetting } = useTheme();

const DEFAULT_HOME_SECTIONS = [
  'hero',
  'services',
  'calculator',
  'sla',
  'managed_services',
  'testimonials',
  'faq',
  'cta',
  'partners',
  'bento',
  'simulator',
  'guarantee',
  'msp',
] as const;

const activeSections = computed(() => {
  const raw = getSetting('home_sections', DEFAULT_HOME_SECTIONS);
  if (Array.isArray(raw)) return new Set(raw.map(String));
  return new Set(DEFAULT_HOME_SECTIONS);
});

const isSectionVisible = (sectionName: string): boolean => {
  return activeSections.value.has(sectionName);
};

const calculatorEnabled = computed(() => {
  return Boolean(getSetting('speed_calculator_enabled', true));
});

useThemeHashScroll(80);
</script>

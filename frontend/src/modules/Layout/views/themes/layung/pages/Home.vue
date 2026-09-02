<template>
  <div class="layung-home-view flex-1 flex flex-col space-y-0">
    <!-- Hero Section with Laser Grid & Coverage Checker -->
    <Hero v-if="isSectionVisible('hero')" />

    <!-- Bento Infrastructure Grid -->
    <IspBentoSection v-if="isSectionVisible('services') || isSectionVisible('bento')" />

    <!-- Interactive Bandwidth Simulator -->
    <div
      v-if="calculatorEnabled && (isSectionVisible('calculator') || isSectionVisible('simulator'))"
      class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full"
    >
      <SpeedCalculatorSection />
    </div>

    <!-- SLA Guarantee -->
    <SlaGuaranteeSection v-if="isSectionVisible('sla') || isSectionVisible('guarantee')" />

    <!-- Managed IT & SOC Services -->
    <ManagedServicesSection v-if="isSectionVisible('managed_services') || isSectionVisible('msp')" />

    <!-- Enterprise Client Testimonials -->
    <TestimonialsSection v-if="isSectionVisible('testimonials') || isSectionVisible('partners')" />

    <!-- Technical & Provisioning FAQ -->
    <FaqSection v-if="isSectionVisible('faq')" />

    <!-- Urgent NOC Hotline & Quotation CTA -->
    <CtaSection v-if="isSectionVisible('cta')" />

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

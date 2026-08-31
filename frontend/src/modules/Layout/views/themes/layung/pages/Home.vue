<template>
  <div class="layung-home-view space-y-0">
    <!-- Hero Section with Laser Grid & Coverage Checker -->
    <Hero />

    <!-- Bento Infrastructure Grid -->
    <IspBentoSection />

    <!-- Interactive Bandwidth Simulator -->
    <SpeedCalculatorSection v-if="calculatorEnabled" />

    <!-- Service Packages & Bandwidth Tiers -->
    <PackagesSection />

    <!-- Backbone Network Topology & Peering -->
    <NetworkTopologySection />

    <!-- SLA 99.999% Guarantee & MTTR -->
    <SlaGuaranteeSection />

    <!-- Managed IT & SOC Services -->
    <ManagedServicesSection />

    <!-- Enterprise Client Testimonials -->
    <TestimonialsSection />

    <!-- Technical & Provisioning FAQ -->
    <FaqSection />

    <!-- Urgent NOC Hotline & Quotation CTA -->
    <CtaSection />

    <!-- App Blocks Plugin Slot -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PluginSlot
        name="home-bottom"
        :context="{ theme: 'layung' }"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import PluginSlot from '@/shared/components/PluginSlot.vue';
import Hero from '../components/sections/Hero.vue';
import IspBentoSection from '../components/sections/IspBentoSection.vue';
import SpeedCalculatorSection from '../components/sections/SpeedCalculatorSection.vue';
import PackagesSection from '../components/sections/PackagesSection.vue';
import NetworkTopologySection from '../components/sections/NetworkTopologySection.vue';
import SlaGuaranteeSection from '../components/sections/SlaGuaranteeSection.vue';
import ManagedServicesSection from '../components/sections/ManagedServicesSection.vue';
import TestimonialsSection from '../components/sections/TestimonialsSection.vue';
import FaqSection from '../components/sections/FaqSection.vue';
import CtaSection from '../components/sections/CtaSection.vue';

const { getSetting } = useTheme();

const calculatorEnabled = computed(() => {
  return Boolean(getSetting('speed_calculator_enabled', true));
});
</script>

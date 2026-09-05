<template>
  <div class="sarangenge-theme flex-1 flex flex-col">
    <!-- Visual Builder Blocks if customized in Page Builder -->
    <BlockRenderer
      v-if="hasBuilderBlocks"
      :blocks="builderBlocks"
      :context="{ post: pageData, site: { name: displaySchoolName } }"
    />

    <!-- Dynamic CMS Body if exists (classic editor) -->
    <ThemeSafeHtml
      v-else-if="cmsBody"
      class="container mx-auto px-4 py-16"
      :html="cmsBody"
      mode="publishing"
    />

    <!-- Default 2026 Modular School Layout -->
    <template v-else>
      <Hero v-if="isSectionVisible('hero')" />
      <PluginSlot name="after_hero" class="w-full" />

      <BentoSection v-if="isSectionVisible('bento')" />
      <VocationalTrackFinder v-if="isSectionVisible('track_finder')" />
      <ProgramsSection v-if="isSectionVisible('programs')" />
      <AnnouncementsSection v-if="isSectionVisible('announcements')" />
      <AchievementsSection v-if="isSectionVisible('achievements')" />
      <FacilitiesSection v-if="isSectionVisible('facilities')" />
      <ExtracurricularSection v-if="isSectionVisible('extracurricular')" />
      <TestimonialsSection v-if="isSectionVisible('testimonials')" />
      <FaqSection v-if="isSectionVisible('faq')" />
      <CtaSection v-if="isSectionVisible('cta')" />
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import { PluginSlot } from '@/shared/components';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';

// Section components
import Hero from '@/modules/Layout/views/themes/sarangenge/components/sections/Hero.vue';
import BentoSection from '@/modules/Layout/views/themes/sarangenge/components/sections/BentoSection.vue';
import VocationalTrackFinder from '@/modules/Layout/views/themes/sarangenge/components/sections/VocationalTrackFinder.vue';
import ProgramsSection from '@/modules/Layout/views/themes/sarangenge/components/sections/ProgramsSection.vue';
import AnnouncementsSection from '@/modules/Layout/views/themes/sarangenge/components/sections/AnnouncementsSection.vue';
import AchievementsSection from '@/modules/Layout/views/themes/sarangenge/components/sections/AchievementsSection.vue';
import FacilitiesSection from '@/modules/Layout/views/themes/sarangenge/components/sections/FacilitiesSection.vue';
import ExtracurricularSection from '@/modules/Layout/views/themes/sarangenge/components/sections/ExtracurricularSection.vue';
import TestimonialsSection from '@/modules/Layout/views/themes/sarangenge/components/sections/TestimonialsSection.vue';
import FaqSection from '@/modules/Layout/views/themes/sarangenge/components/sections/FaqSection.vue';
import CtaSection from '@/modules/Layout/views/themes/sarangenge/components/sections/CtaSection.vue';

const { getSetting } = useTheme();
const { displaySchoolName } = useSarangengeIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('home');

const DEFAULT_HOME_SECTIONS = [
  'hero',
  'bento',
  'track_finder',
  'programs',
  'announcements',
  'achievements',
  'facilities',
  'extracurricular',
  'testimonials',
  'faq',
  'cta',
] as const;

const activeSections = computed(() => {
  const raw = getSetting('home_sections', DEFAULT_HOME_SECTIONS);
  if (Array.isArray(raw) && raw.length > 0) {
    return new Set(raw.map(String));
  }
  return new Set(DEFAULT_HOME_SECTIONS);
});

const isSectionVisible = (sectionName: string): boolean => {
  return activeSections.value.has(sectionName);
};
</script>

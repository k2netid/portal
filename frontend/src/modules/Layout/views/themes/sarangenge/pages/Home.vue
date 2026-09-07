<template>
  <div
    class="sarangenge-theme flex-1 flex flex-col"
    :class="{ 'sarangenge-home--proportional': isProportionalSnap && !hasBuilderBlocks && !cmsBody }"
  >
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
      <Hero v-if="isSectionVisible('hero')" id="section-hero" />
      <PluginSlot name="after_hero" class="w-full" />

      <BentoSection v-if="isSectionVisible('bento')" id="section-bento" />
      <VocationalTrackFinder v-if="isSectionVisible('track_finder')" id="section-track-finder" />
      <ProgramsSection v-if="isSectionVisible('programs')" id="section-programs" />
      <AnnouncementsSection v-if="isSectionVisible('announcements')" id="section-announcements" />
      <AchievementsSection v-if="isSectionVisible('achievements')" id="section-achievements" />
      <FacilitiesSection v-if="isSectionVisible('facilities')" id="section-facilities" />
      <ExtracurricularSection v-if="isSectionVisible('extracurricular')" id="section-extracurricular" />
      <TestimonialsSection v-if="isSectionVisible('testimonials')" id="section-testimonials" />
      <FaqSection v-if="isSectionVisible('faq')" id="section-faq" />
      <CtaSection v-if="isSectionVisible('cta')" id="section-cta" />

      <!-- Floating Side Dot Navigation (Desktop only, reactive to scroll snap mode) -->
      <SectionNavDots
        v-if="isProportionalSnap && showSideNavDots"
        :sections="visibleNavSections"
      />
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, watch, onUnmounted } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import { PluginSlot } from '@/shared/components';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import SectionNavDots, { type SectionNavItem } from '@/modules/Layout/views/themes/sarangenge/components/shared/SectionNavDots.vue';

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

const SECTION_META: Record<string, { id: string; label: string }> = {
  hero: { id: 'section-hero', label: 'Beranda' },
  bento: { id: 'section-bento', label: 'Akses Cepat' },
  track_finder: { id: 'section-track-finder', label: 'Peminatan' },
  programs: { id: 'section-programs', label: 'Program Keahlian' },
  announcements: { id: 'section-announcements', label: 'Informasi' },
  achievements: { id: 'section-achievements', label: 'Prestasi' },
  facilities: { id: 'section-facilities', label: 'Fasilitas' },
  extracurricular: { id: 'section-extracurricular', label: 'Ekstrakurikuler' },
  testimonials: { id: 'section-testimonials', label: 'Testimoni' },
  faq: { id: 'section-faq', label: 'FAQ' },
  cta: { id: 'section-cta', label: 'Pendaftaran' },
};

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

const scrollMode = computed(() => getSetting('home_scroll_mode', 'natural'));
const isProportionalSnap = computed(() => scrollMode.value === 'snap');
const showSideNavDots = computed(() => Boolean(getSetting('home_side_nav_dots', true)));

const visibleNavSections = computed<SectionNavItem[]>(() => {
  const items: SectionNavItem[] = [];
  for (const secKey of DEFAULT_HOME_SECTIONS) {
    if (isSectionVisible(secKey)) {
      const meta = SECTION_META[secKey];
      if (meta) {
        items.push(meta);
      }
    }
  }
  return items;
});

const updateHtmlSnapClass = (enable: boolean) => {
  if (typeof document === 'undefined') return;
  if (enable) {
    document.documentElement.classList.add('sarangenge-snap-scroll');
  } else {
    document.documentElement.classList.remove('sarangenge-snap-scroll');
  }
};

watch(
  () => isProportionalSnap.value && !hasBuilderBlocks.value && !cmsBody.value,
  (shouldEnable) => {
    updateHtmlSnapClass(shouldEnable);
  },
  { immediate: true }
);

onUnmounted(() => {
  updateHtmlSnapClass(false);
});
</script>

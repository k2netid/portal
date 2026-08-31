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
      <Hero />
      <PluginSlot name="after_hero" class="w-full" />

      <SchoolBentoSection />
      <ProgramsSection />
      <AnnouncementsSection />
      <AchievementsSection />
      <FacilitiesSection />
      <ExtracurricularSection />
      <TestimonialsSection />
      <FaqSection />
      <CtaSection />
    </template>
  </div>
</template>

<script setup lang="ts">
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import { PluginSlot } from '@/shared/components';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';

// Section components
import Hero from '@/modules/Layout/views/themes/sarangenge/components/sections/Hero.vue';
import SchoolBentoSection from '@/modules/Layout/views/themes/sarangenge/components/sections/SchoolBentoSection.vue';
import ProgramsSection from '@/modules/Layout/views/themes/sarangenge/components/sections/ProgramsSection.vue';
import AnnouncementsSection from '@/modules/Layout/views/themes/sarangenge/components/sections/AnnouncementsSection.vue';
import AchievementsSection from '@/modules/Layout/views/themes/sarangenge/components/sections/AchievementsSection.vue';
import FacilitiesSection from '@/modules/Layout/views/themes/sarangenge/components/sections/FacilitiesSection.vue';
import ExtracurricularSection from '@/modules/Layout/views/themes/sarangenge/components/sections/ExtracurricularSection.vue';
import TestimonialsSection from '@/modules/Layout/views/themes/sarangenge/components/sections/TestimonialsSection.vue';
import FaqSection from '@/modules/Layout/views/themes/sarangenge/components/sections/FaqSection.vue';
import CtaSection from '@/modules/Layout/views/themes/sarangenge/components/sections/CtaSection.vue';

const { displaySchoolName } = useSarangengeIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('home');
</script>

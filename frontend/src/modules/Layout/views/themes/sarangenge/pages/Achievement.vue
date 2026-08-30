<template>
  <div class="sarangenge-theme flex-1 flex flex-col py-12 sm:py-16">
    <BlockRenderer
      v-if="hasBuilderBlocks"
      :blocks="builderBlocks"
      :context="{ post: pageData, site: { name: 'Jejakawan' } }"
    />
    <SafeHtml
      v-else-if="cmsBody"
      class="container mx-auto px-4 py-16"
      :html="cmsBody"
      mode="publishing"
    />
    <template v-else>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 w-full">
      <div class="text-center space-y-4">
        <h1 class="text-4xl sm:text-5xl font-extrabold text-foreground font-heading">
          {{ t('theme.sarangenge.pages.achievement.title', 'Milestones & Awards') }}
        </h1>
        <p class="text-lg text-muted-foreground">
          {{ t('theme.sarangenge.pages.achievement.subtitle', 'Celebrating our growth and recognized industry impact.') }}
        </p>
      </div>

      <div class="space-y-6">
        <Card
          v-for="ach in achievements"
          :key="ach.year"
          :hover="true"
          class="space-y-2"
        >
          <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-primary text-primary-foreground">
              {{ ach.year }}
            </span>
            <h3 class="text-xl font-bold text-foreground font-heading">
              {{ ach.title }}
            </h3>
          </div>
          <p class="text-sm text-muted-foreground leading-relaxed pl-12">
            {{ ach.description }}
          </p>
        </Card>
      </div>
    </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import SafeHtml from '@/modules/Core/System/components/ui/SafeHtml.vue';
import { Card } from '@/modules/Layout/views/themes/sarangenge/ui';

const { t } = useI18n();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('achievement');

const achievements = [
  {
    year: '2026',
    title: 'Enterprise High-Performance CMS Award',
    description: 'Recognized for revolutionary modular architecture, instant live customizer reactivity, and strict security posture.',
  },
  {
    year: '2025',
    title: '1,000,000+ Monthly Pageviews Milestone',
    description: 'Empowering publications and organizations across Southeast Asia with zero downtime and sub-second page loads.',
  },
];
</script>

<template>
  <div class="zenith-theme flex-1 flex flex-col py-12 sm:py-16">
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
          {{ t('theme.zenith.pages.career_center.title', 'Join Our Team') }}
        </h1>
        <p class="text-lg text-muted-foreground">
          {{ t('theme.zenith.pages.career_center.subtitle', 'Build the future of digital publishing with us.') }}
        </p>
      </div>

      <div class="space-y-6">
        <Card
          v-for="job in jobs"
          :key="job.title"
          :hover="true"
          class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
        >
          <div>
            <h3 class="text-xl font-bold text-foreground font-heading">
              {{ job.title }}
            </h3>
            <p class="text-xs text-muted-foreground mt-1">
              {{ job.type }} • {{ job.location }}
            </p>
          </div>

          <Button
            as="router-link"
            to="/contact"
            variant="outline"
            size="sm"
          >
            Apply Now
          </Button>
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
import { Card, Button } from '@/modules/Layout/views/themes/zenith/ui';

const { t } = useI18n();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('career');

const jobs = [
  { title: 'Senior Frontend Engineer (Vue 3 / TypeScript)', type: 'Full-time', location: 'Remote' },
  { title: 'Principal Backend Architect (Laravel / PHP 8.4)', type: 'Full-time', location: 'Remote' },
  { title: 'Product & UI Designer', type: 'Full-time', location: 'Remote / Hybrid' },
];
</script>

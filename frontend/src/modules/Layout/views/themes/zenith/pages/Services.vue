<template>
  <div class="zenith-theme flex-1 flex flex-col py-12 sm:py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 w-full">
      <div class="text-center space-y-4 max-w-3xl mx-auto">
        <h1 class="text-4xl sm:text-5xl font-extrabold text-foreground font-heading">
          {{ t('theme.zenith.pages.services.title', 'Professional Services') }}
        </h1>
        <p class="text-lg text-muted-foreground">
          {{ t('theme.zenith.pages.services.subtitle', 'End-to-end expertise in digital design, engineering, and content strategy.') }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <Card
          v-for="serv in services"
          :key="serv.title"
          :hover="true"
          class="space-y-4"
        >
          <h3 class="text-xl font-bold text-foreground font-heading">
            {{ serv.title }}
          </h3>
          <p class="text-sm text-muted-foreground leading-relaxed">
            {{ serv.description }}
          </p>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { Card } from '@/modules/Layout/views/themes/zenith/ui';

const { t } = useI18n();

const services = [
  {
    title: 'Custom Theme Engineering',
    description: 'Bespoke, high-performance themes crafted with Tailwind CSS and Vue 3 to match your exact brand identity.',
  },
  {
    title: 'Enterprise Migration',
    description: 'Seamless content and user migration from WordPress, Drupal, Ghost, or legacy bespoke CMS platforms.',
  },
  {
    title: 'Performance & SEO Audit',
    description: 'Deep optimization for Core Web Vitals, sub-second TTFB, and search engine discoverability.',
  },
];
</script>

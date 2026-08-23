<template>
  <div class="zenith-theme flex-1 flex flex-col py-12 sm:py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 w-full">
      <div class="text-center space-y-4">
        <h1 class="text-4xl sm:text-5xl font-extrabold text-foreground font-heading">
          {{ t('theme.zenith.pages.about.title', 'About Us') }}
        </h1>
        <p class="text-lg text-muted-foreground leading-relaxed max-w-2xl mx-auto">
          {{ t('theme.zenith.pages.about.subtitle', 'We design high-impact digital experiences for forward-thinking teams.') }}
        </p>
      </div>

      <Card class="space-y-6">
        <h2 class="text-2xl font-bold text-foreground font-heading">
          {{ t('theme.zenith.pages.about.missionTitle', 'Our Mission') }}
        </h2>
        <p class="text-base text-muted-foreground leading-relaxed">
          {{ t('theme.zenith.pages.about.missionDesc', 'To democratize high-performance web publishing with elegant, robust architecture.') }}
        </p>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { Card } from '@/modules/Layout/views/themes/zenith/ui';

const { t } = useI18n();
</script>

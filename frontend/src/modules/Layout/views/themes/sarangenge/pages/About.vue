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
          {{ t('theme.sarangenge.pages.about.title', 'About Us') }}
        </h1>
        <p class="text-lg text-muted-foreground leading-relaxed max-w-2xl mx-auto">
          {{ t('theme.sarangenge.pages.about.subtitle', 'We design high-impact digital experiences for forward-thinking teams.') }}
        </p>
      </div>

      <Card class="space-y-6">
        <h2 class="text-2xl font-bold text-foreground font-heading">
          {{ t('theme.sarangenge.pages.about.missionTitle', 'Our Mission') }}
        </h2>
        <p class="text-base text-muted-foreground leading-relaxed">
          {{ t('theme.sarangenge.pages.about.missionDesc', 'To democratize high-performance web publishing with elegant, robust architecture.') }}
        </p>
      </Card>
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
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('about');
</script>

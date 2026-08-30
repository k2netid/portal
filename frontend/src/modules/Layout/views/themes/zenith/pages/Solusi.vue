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
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 w-full">
      <div class="text-center space-y-4 max-w-3xl mx-auto">
        <h1 class="text-4xl sm:text-5xl font-extrabold text-foreground font-heading">
          {{ t('theme.zenith.pages.solusi.title', 'Our Solutions') }}
        </h1>
        <p class="text-lg text-muted-foreground">
          {{ t('theme.zenith.pages.solusi.subtitle', 'Scalable systems tailored to your organizational goals.') }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <Card
          v-for="sol in solutions"
          :key="sol.title"
          :hover="true"
          class="space-y-4"
        >
          <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shadow-inner">
            <component
              :is="sol.icon"
              class="w-6 h-6"
            />
          </div>
          <h3 class="text-2xl font-bold text-foreground font-heading">
            {{ sol.title }}
          </h3>
          <p class="text-sm text-muted-foreground leading-relaxed">
            {{ sol.description }}
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
import { Card } from '@/modules/Layout/views/themes/zenith/ui';
import { Globe, Shield, Sparkles, Cpu } from 'lucide-vue-next';

const { t } = useI18n();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('solusi');

const solutions = [
  {
    icon: Globe,
    title: 'Enterprise Publishing',
    description: 'High-throughput editorial workflows designed for newsrooms, magazines, and content-driven enterprises.',
  },
  {
    icon: Cpu,
    title: 'Headless & Decoupled APIs',
    description: 'Consume your content anywhere with ultra-low latency GraphQL and REST endpoints.',
  },
  {
    icon: Sparkles,
    title: 'AI-Enhanced Workflows',
    description: 'Automated drafting, taxonomy suggestions, and localized translation pipelines.',
  },
  {
    icon: Shield,
    title: 'Sovereign Data & Privacy',
    description: 'GDPR-compliant analytics and enterprise access controls built for privacy-conscious organizations.',
  },
];
</script>

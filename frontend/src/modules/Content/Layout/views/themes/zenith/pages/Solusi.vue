<template>
  <div class="zenith-theme flex-1 flex flex-col">
    <Header />

    <main class="flex-1 py-16 sm:py-24">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
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
    </main>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Header from '@/modules/Content/Layout/views/themes/zenith/components/layout/Header.vue';
import Footer from '@/modules/Content/Layout/views/themes/zenith/components/layout/Footer.vue';
import { Card } from '@/modules/Content/Layout/views/themes/zenith/ui';
import { Globe, Shield, Sparkles, Cpu } from 'lucide-vue-next';

const { t } = useI18n();

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

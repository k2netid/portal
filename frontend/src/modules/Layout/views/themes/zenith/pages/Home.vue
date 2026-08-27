<template>
  <div class="zenith-theme flex-1 flex flex-col">
    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-8 pb-16 sm:pt-16 sm:pb-24">
      <!-- Subtle background glow -->
      <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-primary/10 blur-[120px] rounded-full pointer-events-none" />

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <!-- Top Badge -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-primary/20 bg-primary/5 text-primary text-xs font-semibold mb-8 shadow-sm">
          <Sparkles class="w-3.5 h-3.5" />
          <span>{{ heroBadge }}</span>
        </div>

        <!-- Hero Headline -->
        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-foreground max-w-4xl mx-auto leading-[1.1] font-heading">
          {{ heroTitle }}
        </h1>

        <!-- Hero Subtitle -->
        <p class="mt-6 text-base sm:text-xl text-muted-foreground max-w-2xl mx-auto leading-relaxed">
          {{ heroSubtitle }}
        </p>

        <!-- CTA Actions -->
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
          <Button
            as="router-link"
            :to="heroCtaLink"
            size="lg"
            variant="primary"
            class="w-full sm:w-auto"
          >
            {{ heroCtaText }}
            <ArrowRight class="w-4 h-4 ml-1" />
          </Button>
          <Button
            as="router-link"
            to="/about"
            size="lg"
            variant="outline"
            class="w-full sm:w-auto"
          >
            {{ t('theme.zenith.common.learnMore', 'Learn More') }}
          </Button>
        </div>

        <!-- Hero Showcase Card -->
        <div class="mt-16 sm:mt-20 max-w-5xl mx-auto rounded-3xl border border-border/60 bg-card/60 backdrop-blur-2xl p-4 sm:p-6 shadow-2xl shadow-primary/5">
          <div class="rounded-2xl border border-border/40 bg-background/80 overflow-hidden aspect-[16/9] flex items-center justify-center relative group">
            <div class="absolute inset-0 bg-gradient-to-tr from-primary/5 via-transparent to-primary/10" />
            <div class="relative z-10 text-center p-8 space-y-4">
              <div class="w-16 h-16 rounded-2xl bg-primary/10 text-primary mx-auto flex items-center justify-center shadow-inner">
                <Zap class="w-8 h-8" />
              </div>
              <h3 class="text-xl sm:text-2xl font-bold text-foreground font-heading">
                High-Performance Digital Foundation
              </h3>
              <p class="text-sm text-muted-foreground max-w-md mx-auto">
                Built on Vue 3, Tailwind CSS, and Laravel for sub-second responsiveness and zero-latency content delivery.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-muted/20 border-y border-border/40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
          <h2 class="text-3xl sm:text-4xl font-bold text-foreground font-heading">
            {{ t('theme.zenith.pages.home.featuresTitle', 'Why Choose Zenith') }}
          </h2>
          <p class="text-muted-foreground text-base sm:text-lg">
            {{ t('theme.zenith.pages.home.featuresSubtitle', 'Engineered for speed, crafted for aesthetic excellence.') }}
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <Card
            v-for="feat in features"
            :key="feat.title"
            :hover="true"
            class="space-y-4"
          >
            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shadow-inner">
              <component
                :is="feat.icon"
                class="w-6 h-6"
              />
            </div>
            <h3 class="text-xl font-bold text-foreground font-heading">
              {{ feat.title }}
            </h3>
            <p class="text-sm text-muted-foreground leading-relaxed">
              {{ feat.description }}
            </p>
          </Card>
        </div>
      </div>
    </section>

    <!-- CTA Banner Section -->
    <section class="py-20 sm:py-28 relative overflow-hidden">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-primary/30 bg-gradient-to-b from-primary/10 to-transparent p-8 sm:p-14 text-center space-y-6 relative overflow-hidden">
          <h2 class="text-3xl sm:text-5xl font-extrabold text-foreground font-heading max-w-2xl mx-auto">
            {{ t('theme.zenith.pages.home.ctaTitle', 'Ready to elevate your digital presence?') }}
          </h2>
          <p class="text-base sm:text-lg text-muted-foreground max-w-xl mx-auto">
            {{ t('theme.zenith.pages.home.ctaSubtitle', 'Join thousands of creators and businesses powered by Jejakawan Core Engine.') }}
          </p>
          <div class="pt-4">
            <Button
              as="router-link"
              to="/contact"
              size="lg"
              variant="primary"
            >
              {{ t('theme.zenith.pages.home.ctaButton', 'Start Today') }}
              <ArrowRight class="w-4 h-4 ml-1" />
            </Button>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { Button, Card } from '@/modules/Layout/views/themes/zenith/ui';
import { Sparkles, ArrowRight, Zap, ShieldCheck, Gauge, Layers } from 'lucide-vue-next';

const { t } = useI18n();
const { getSetting } = useTheme();

const heroBadge = computed(() => {
  return String(getSetting('hero_badge_text') || t('theme.zenith.pages.home.heroBadge', 'Zenith Edition'));
});

const heroTitle = computed(() => {
  return String(getSetting('hero_title') || t('theme.zenith.pages.home.heroTitle', 'Modern digital experiences built with precision and speed.'));
});

const heroSubtitle = computed(() => {
  return String(getSetting('hero_subtitle') || t('theme.zenith.pages.home.heroSubtitle', 'Empower your brand with cutting-edge performance, clean aesthetics, and effortless content management.'));
});

const heroCtaText = computed(() => {
  return String(getSetting('hero_primary_cta_text') || t('theme.zenith.pages.home.heroCta', 'Get Started'));
});

const heroCtaLink = computed(() => {
  return String(getSetting('hero_primary_cta_link') || '/blog');
});

const features = [
  {
    icon: Gauge,
    title: 'Ultra-Fast Performance',
    description: 'Engineered with optimized bundle chunks, pre-rendering, and intelligent token caching for instantaneous navigation.',
  },
  {
    icon: ShieldCheck,
    title: 'Enterprise Security',
    description: 'Protected with Content Security Policy, strict data sanitization, passkeys authentication, and anti-abuse safeguards.',
  },
  {
    icon: Layers,
    title: 'Flexible Modular Architecture',
    description: 'Fully extensible block builder, live reactive theme customizer, and comprehensive API integrations.',
  },
];
</script>

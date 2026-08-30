<template>
  <div class="sarangenge-theme flex-1 flex flex-col">
    <BlockRenderer
      v-if="hasBuilderBlocks"
      :blocks="builderBlocks"
      :context="{ post: pageData, site: { name: 'Sarangenge' } }"
    />

    <SafeHtml
      v-else-if="cmsBody"
      class="container mx-auto px-4 py-16"
      :html="cmsBody"
      mode="publishing"
    />

    <template v-else>
      <!-- Hero: brand-first, full-bleed, one CTA group -->
      <section class="sarangenge-hero relative">
        <div
          class="sarangenge-hero__media"
          aria-hidden="true"
        />
        <div class="relative z-10 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pb-14 sm:pb-20 pt-28 sm:pt-36">
          <p class="sarangenge-hero__brand sarangenge-rise text-white">
            Sarangenge
          </p>
          <h1 class="sarangenge-rise sarangenge-rise-delay-1 mt-5 max-w-3xl text-2xl sm:text-3xl lg:text-4xl font-semibold leading-snug text-white/95 font-heading">
            {{ heroTitle }}
          </h1>
          <p class="sarangenge-rise sarangenge-rise-delay-2 mt-4 max-w-xl text-base sm:text-lg text-white/80 leading-relaxed">
            {{ heroSubtitle }}
          </p>
          <div class="sarangenge-rise sarangenge-rise-delay-3 mt-8 flex flex-col sm:flex-row gap-3">
            <Button
              as="router-link"
              :to="heroCtaLink"
              size="lg"
              variant="secondary"
              class="!bg-white !text-[var(--sarangenge-teal-deep)] hover:!bg-[var(--sarangenge-sun-soft)] shadow-lg"
            >
              {{ heroCtaText }}
              <ArrowRight class="w-4 h-4 ml-1" />
            </Button>
            <Button
              as="router-link"
              to="/solusi"
              size="lg"
              variant="outline"
              class="!border-white/40 !text-white hover:!bg-white/10"
            >
              {{ t('theme.sarangenge.pages.home.heroSecondary', 'Browse programs') }}
            </Button>
          </div>
        </div>
      </section>

      <!-- Bento campus access -->
      <section class="py-16 sm:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="max-w-2xl mb-10 space-y-2">
            <h2 class="text-3xl sm:text-4xl font-bold text-foreground font-heading">
              {{ t('theme.sarangenge.pages.home.bentoTitle', 'Campus quick access') }}
            </h2>
            <p class="text-muted-foreground text-base sm:text-lg">
              {{ t('theme.sarangenge.pages.home.bentoSubtitle', 'Essential school paths in a 2026-style bento layout.') }}
            </p>
          </div>

          <div class="sarangenge-bento">
            <router-link
              to="/contact"
              class="sarangenge-bento__cell sarangenge-bento__cell--teal sarangenge-bento__wide"
            >
              <div class="space-y-2">
                <GraduationCap class="w-7 h-7 opacity-90" />
                <h3 class="text-xl font-bold font-heading">
                  {{ t('theme.sarangenge.pages.home.tilePpdb', 'Admissions') }}
                </h3>
                <p class="text-sm text-white/80">
                  {{ t('theme.sarangenge.pages.home.tilePpdbDesc', 'Dates, requirements, and entry forms.') }}
                </p>
              </div>
              <ArrowUpRight class="w-5 h-5 self-end opacity-80" />
            </router-link>

            <router-link
              to="/solusi"
              class="sarangenge-bento__cell sarangenge-bento__tall"
            >
              <div class="space-y-2">
                <BookOpen class="w-6 h-6 text-[var(--sarangenge-teal)]" />
                <h3 class="text-lg font-bold font-heading">
                  {{ t('theme.sarangenge.pages.home.tileProgram', 'Programs') }}
                </h3>
                <p class="text-sm text-muted-foreground">
                  {{ t('theme.sarangenge.pages.home.tileProgramDesc', 'Curriculum and talent tracks.') }}
                </p>
              </div>
            </router-link>

            <router-link
              to="/achievement"
              class="sarangenge-bento__cell sarangenge-bento__cell--sun"
            >
              <div class="space-y-2">
                <Trophy class="w-6 h-6 text-[var(--sarangenge-teal-deep)]" />
                <h3 class="text-lg font-bold font-heading">
                  {{ t('theme.sarangenge.pages.home.tilePrestasi', 'Achievements') }}
                </h3>
                <p class="text-sm text-muted-foreground">
                  {{ t('theme.sarangenge.pages.home.tilePrestasiDesc', 'Academic and extracurricular wins.') }}
                </p>
              </div>
            </router-link>

            <router-link
              to="/blog"
              class="sarangenge-bento__cell"
            >
              <div class="space-y-2">
                <Newspaper class="w-6 h-6 text-[var(--sarangenge-teal)]" />
                <h3 class="text-lg font-bold font-heading">
                  {{ t('theme.sarangenge.pages.home.tileBerita', 'News') }}
                </h3>
                <p class="text-sm text-muted-foreground">
                  {{ t('theme.sarangenge.pages.home.tileBeritaDesc', 'Announcements and campus stories.') }}
                </p>
              </div>
            </router-link>

            <router-link
              to="/about"
              class="sarangenge-bento__cell"
            >
              <div class="space-y-2">
                <School class="w-6 h-6 text-[var(--sarangenge-teal)]" />
                <h3 class="text-lg font-bold font-heading">
                  {{ t('theme.sarangenge.pages.home.tileProfil', 'Profile') }}
                </h3>
                <p class="text-sm text-muted-foreground">
                  {{ t('theme.sarangenge.pages.home.tileProfilDesc', 'Vision, mission, and culture.') }}
                </p>
              </div>
            </router-link>

            <router-link
              to="/contact"
              class="sarangenge-bento__cell sarangenge-bento__wide"
            >
              <div class="space-y-2">
                <MapPin class="w-6 h-6 text-[var(--sarangenge-teal)]" />
                <h3 class="text-lg font-bold font-heading">
                  {{ t('theme.sarangenge.pages.home.tileKontak', 'Contact') }}
                </h3>
                <p class="text-sm text-muted-foreground">
                  {{ t('theme.sarangenge.pages.home.tileKontakDesc', 'Address, phone, and messages.') }}
                </p>
              </div>
            </router-link>
          </div>
        </div>
      </section>

      <!-- Why section -->
      <section class="py-16 sm:py-20 bg-[var(--sarangenge-sky)]/70 border-y border-border/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="max-w-2xl mb-12 space-y-3">
            <h2 class="text-3xl sm:text-4xl font-bold text-foreground font-heading">
              {{ t('theme.sarangenge.pages.home.featuresTitle', 'Why Sarangenge for schools') }}
            </h2>
            <p class="text-muted-foreground text-base sm:text-lg">
              {{ t('theme.sarangenge.pages.home.featuresSubtitle', 'Built for parents, students, and staff.') }}
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <article
              v-for="feat in features"
              :key="feat.title"
              class="sarangenge-panel p-6 space-y-4"
            >
              <div class="w-11 h-11 rounded-[var(--sarangenge-radius-sm)] bg-[var(--sarangenge-teal)]/10 text-[var(--sarangenge-teal)] flex items-center justify-center">
                <component
                  :is="feat.icon"
                  class="w-5 h-5"
                />
              </div>
              <h3 class="text-xl font-bold text-foreground font-heading">
                {{ feat.title }}
              </h3>
              <p class="text-sm text-muted-foreground leading-relaxed">
                {{ feat.description }}
              </p>
            </article>
          </div>
        </div>
      </section>

      <!-- Closing CTA -->
      <section class="py-20 sm:py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="sarangenge-panel overflow-hidden relative p-8 sm:p-14 text-center space-y-6">
            <div
              class="absolute inset-0 opacity-90 pointer-events-none"
              style="background: linear-gradient(135deg, color-mix(in oklab, var(--sarangenge-teal) 12%, transparent), color-mix(in oklab, var(--sarangenge-sun) 16%, transparent));"
              aria-hidden="true"
            />
            <div class="relative space-y-6">
              <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading max-w-2xl mx-auto">
                {{ t('theme.sarangenge.pages.home.ctaTitle', 'Ready to meet our school?') }}
              </h2>
              <p class="text-base sm:text-lg text-muted-foreground max-w-xl mx-auto">
                {{ t('theme.sarangenge.pages.home.ctaSubtitle', 'Start with the profile or talk to admissions.') }}
              </p>
              <Button
                as="router-link"
                to="/contact"
                size="lg"
                variant="primary"
              >
                {{ t('theme.sarangenge.pages.home.ctaButton', 'Contact school') }}
                <ArrowRight class="w-4 h-4 ml-1" />
              </Button>
            </div>
          </div>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import SafeHtml from '@/modules/Core/System/components/ui/SafeHtml.vue';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import {
  ArrowRight,
  ArrowUpRight,
  BookOpen,
  GraduationCap,
  LayoutGrid,
  Accessibility,
  Sparkles,
  Trophy,
  Newspaper,
  School,
  MapPin,
} from 'lucide-vue-next';

const { t } = useI18n();
const { getSetting } = useTheme();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('home');

const heroTitle = computed(() => {
  return String(getSetting('hero_title') || t('theme.sarangenge.pages.home.heroTitle'));
});

const heroSubtitle = computed(() => {
  return String(getSetting('hero_subtitle') || t('theme.sarangenge.pages.home.heroSubtitle'));
});

const heroCtaText = computed(() => {
  return String(getSetting('hero_primary_cta_text') || t('theme.sarangenge.pages.home.heroCta'));
});

const heroCtaLink = computed(() => {
  return String(getSetting('hero_primary_cta_link') || '/contact');
});

const features = computed(() => [
  {
    icon: LayoutGrid,
    title: t('theme.sarangenge.pages.home.feat1Title'),
    description: t('theme.sarangenge.pages.home.feat1Desc'),
  },
  {
    icon: Accessibility,
    title: t('theme.sarangenge.pages.home.feat2Title'),
    description: t('theme.sarangenge.pages.home.feat2Desc'),
  },
  {
    icon: Sparkles,
    title: t('theme.sarangenge.pages.home.feat3Title'),
    description: t('theme.sarangenge.pages.home.feat3Desc'),
  },
]);
</script>

<template>
  <section
    data-ja-customizer-target="hero"
    class="sarangenge-hero px-4 sm:px-6 lg:px-8 py-3 sm:py-4 lg:py-5 min-h-[max(600px,calc(100dvh-4.5rem))] lg:h-[calc(100dvh-4.5rem)] lg:max-h-[calc(100dvh-4.5rem)] flex flex-col justify-between relative overflow-hidden"
    :class="heroBgClass"
    :style="heroBgStyle"
  >
    <!-- Custom Image Background Layer if configured -->
    <div
      v-if="heroBgType === 'custom_image' && heroBgImage"
      class="sarangenge-hero__custom-bg absolute inset-0 bg-cover bg-center z-0"
      :style="{ backgroundImage: `url(${heroBgImage})` }"
      aria-hidden="true"
    />

    <!-- Dynamic Overlay Layer -->
    <div
      class="sarangenge-hero__overlay absolute inset-0 pointer-events-none transition-opacity duration-300"
      :style="{
        backgroundColor: '#0a1128',
        opacity: heroOverlayFinalOpacity,
        zIndex: 1,
      }"
      aria-hidden="true"
    />

    <!-- Grid / Media overlay texture -->
    <div
      class="sarangenge-hero__media"
      aria-hidden="true"
    />

    <div
      class="max-w-7xl mx-auto w-full relative z-10 flex-1 flex flex-col justify-between gap-3 lg:gap-4 my-auto min-h-0"
      @mouseenter="stopSliderAutoplay"
      @mouseleave="startSliderAutoplay"
    >
      <!-- Main Stage (2-Column Grid on Desktop) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10 items-center my-auto py-1 lg:py-2 min-h-0">
        <!-- Left Column: Copy & Actions -->
        <div
          class="space-y-3.5 lg:space-y-4"
          :class="heroAnimationEnabled ? 'lg:col-span-7' : 'lg:col-span-12 max-w-3xl'"
        >
          <!-- Eyebrow Badge (Sunflower Dawn) -->
          <div
            ref="heroBadgeRef"
            class="flex flex-wrap items-center gap-3"
          >
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-semibold bg-amber-400/15 text-amber-300 border border-amber-400/30 backdrop-blur-md shadow-sm">
              <span class="sarangenge-status-dot" />
              <SunMedium class="w-3.5 h-3.5 text-amber-400" />
              {{ activeSlide.badge }}
            </span>
          </div>

          <!-- Headline & Description with Smooth SplitText Transition -->
          <div class="space-y-2.5 sm:space-y-3">
            <h1
              ref="heroTitleRef"
              class="text-xl sm:text-3xl lg:text-[2.25rem] xl:text-[2.55rem] font-bold tracking-tight text-white font-heading leading-[1.14]"
            >
              <SarangengeSplitText :key="activeSlide.title" :text="activeSlide.title" />
            </h1>
            <p
              ref="heroSubtitleRef"
              class="text-xs sm:text-sm text-slate-200/90 max-w-xl leading-relaxed font-normal"
            >
              {{ activeSlide.subtitle }}
            </p>
          </div>

          <!-- Call to Action Buttons -->
          <div
            ref="heroCtaRef"
            class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-0.5"
          >
            <a
              v-if="isExternalPrimary"
              :href="primaryTargetUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-[var(--sarangenge-radius-sm,0.85rem)] bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold shadow-lg shadow-amber-500/20 transition-all duration-200 text-xs sm:text-sm cursor-pointer"
            >
              <GraduationCap class="w-4 h-4 mr-1" />
              {{ activeSlide.ctaText || heroPrimaryText }}
            </a>
            <Button
              v-else
              as="router-link"
              :to="primaryTargetUrl"
              variant="primary"
              size="md"
              class="!py-2.5 !px-6 !bg-amber-500 hover:!bg-amber-400 !text-slate-950 font-bold shadow-lg shadow-amber-500/20 border-none transition-all text-xs sm:text-sm"
            >
              <GraduationCap class="w-4 h-4 mr-1" />
              {{ activeSlide.ctaText || heroPrimaryText }}
            </Button>

            <Button
              as="router-link"
              :to="secondaryTargetUrl"
              variant="outline"
              size="md"
              class="!py-2.5 !px-5 !border-white/25 !bg-white/10 !text-white hover:!bg-white/20 backdrop-blur-md font-semibold transition-all text-xs sm:text-sm"
            >
              {{ heroSecondaryText }}
              <ArrowRight class="w-3.5 h-3.5 ml-1.5" />
            </Button>
          </div>

          <!-- Slider Navigation Tabs (When Slider Enabled & Multiple Slides Exist) -->
          <div
            v-if="heroSliderEnabled && activeSlides.length > 1"
            class="pt-1.5 flex items-center gap-2.5 flex-wrap"
          >
            <div class="flex items-center gap-2 flex-wrap">
              <button
                v-for="(slide, sIdx) in activeSlides"
                :key="slide.id"
                type="button"
                class="flex items-center gap-1.5 px-3 py-1 rounded-full border text-[11px] font-mono transition-all cursor-pointer"
                :class="currentSlideIndex === sIdx
                  ? 'bg-amber-500/20 border-amber-400/60 text-amber-200 shadow-sm shadow-amber-500/20 font-bold'
                  : 'bg-slate-900/60 border-white/10 text-slate-400 hover:text-slate-200 hover:border-white/20'"
                @click="setSlide(sIdx)"
              >
                <span
                  class="w-1.5 h-1.5 rounded-full"
                  :class="currentSlideIndex === sIdx ? 'bg-amber-400 animate-pulse' : 'bg-slate-600'"
                />
                <span class="font-semibold">0{{ sIdx + 1 }}</span>
                <span class="truncate max-w-[120px] sm:max-w-[160px]">{{ slide.badge }}</span>
              </button>
            </div>

            <div class="flex items-center gap-1.5 ml-auto sm:ml-0">
              <button
                type="button"
                class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-slate-900/80 border border-white/15 flex items-center justify-center text-slate-300 hover:text-white hover:border-amber-400/50 transition-colors cursor-pointer"
                :aria-label="t('pages.home.heroPrevSlide', 'Slide sebelumnya')"
                @click="prevSlide"
              >
                <ChevronLeft class="w-3.5 h-3.5" />
              </button>
              <button
                type="button"
                class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-slate-900/80 border border-white/15 flex items-center justify-center text-slate-300 hover:text-white hover:border-amber-400/50 transition-colors cursor-pointer"
                :aria-label="t('pages.home.heroNextSlide', 'Slide berikutnya')"
                @click="nextSlide"
              >
                <ChevronRight class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>

        <!-- Right Column: Interactive Visual Stage -->
        <div
          v-if="heroAnimationEnabled"
          class="lg:col-span-5 w-full flex justify-center lg:justify-end"
        >
          <SarangengeHeroVisual
            :key="activeSlide.animation || heroAnimationType"
            :type="activeSlide.animation || heroAnimationType"
          />
        </div>
      </div>

      <!-- Animated Scroll Indicator -->
      <div
        v-if="heroShowScroll"
        class="hidden xl:flex items-center justify-center gap-3 py-0.5 text-[9px] font-mono text-amber-200/80 uppercase tracking-widest"
      >
        <div class="w-8 h-[1px] bg-amber-400/30 relative overflow-hidden">
          <div class="absolute inset-0 bg-amber-400 animate-scroll-line" />
        </div>
        <span>{{ scrollCueText }}</span>
        <div class="w-8 h-[1px] bg-amber-400/30 relative overflow-hidden">
          <div class="absolute inset-0 bg-amber-400 animate-scroll-line" />
        </div>
      </div>

      <!-- Bottom News & Campus Warta Carousel Ticker (Default Mode Like Layung) -->
      <div
        v-if="(heroBottomMode === 'news' || heroBottomMode === 'both') && heroNewsEnabled && carouselItems.length > 0"
        ref="heroNewsRef"
        data-ja-customizer-target="hero-news"
        class="pt-3 lg:pt-4 border-t border-white/15 space-y-2.5 shrink-0"
        @mouseenter="stopNewsAutoplay"
        @mouseleave="startNewsAutoplay"
      >
        <div class="flex items-center justify-between gap-3">
          <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400 font-mono flex items-center gap-2">
            <Sparkles class="w-3.5 h-3.5 text-amber-400" />
            {{ newsSectionLabel }}
          </span>
          <router-link
            to="/blog"
            class="text-[11px] font-semibold text-amber-400 hover:text-amber-300 transition-colors inline-flex items-center gap-1 font-mono"
          >
            {{ t('pages.home.viewAllNews', 'Semua Warta') }}
            <ArrowUpRight class="w-3.5 h-3.5" />
          </router-link>
        </div>

        <div class="flex items-stretch gap-3">
          <div
            v-if="canRotateNews"
            class="flex items-center shrink-0"
          >
            <button
              type="button"
              class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-slate-950/90 backdrop-blur-md border border-white/15 flex items-center justify-center text-slate-400 hover:text-amber-400 hover:border-amber-500/50 transition-all cursor-pointer shrink-0 shadow-lg"
              :aria-label="t('pages.home.heroNewsPrev', 'Berita sebelumnya')"
              @click.prevent="advanceNews(-1)"
            >
              <ChevronLeft class="w-4 h-4" />
            </button>
          </div>

          <div class="flex-1 min-w-0 overflow-hidden">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 text-slate-300">
              <router-link
                v-for="item in carouselItems"
                :key="item.id"
                :to="item.url"
                class="group flex items-stretch gap-3 overflow-hidden rounded-xl border border-white/10 bg-slate-950/50 hover:bg-slate-900/80 hover:border-amber-400/40 p-2.5 sm:p-3 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500/60 backdrop-blur-sm"
              >
                <div class="w-14 sm:w-16 h-11 sm:h-12 overflow-hidden shrink-0 rounded-lg border border-white/10 bg-slate-900 flex items-center justify-center">
                  <img
                    v-if="item.image"
                    :src="item.image"
                    :alt="item.title"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    loading="lazy"
                    decoding="async"
                    referrerpolicy="no-referrer"
                  >
                  <div
                    v-else
                    class="w-full h-full flex items-center justify-center bg-slate-900/80 text-amber-500/50"
                  >
                    <Newspaper class="w-4 h-4" />
                  </div>
                </div>
                <div class="min-w-0 flex-1 flex flex-col justify-center space-y-1 py-0.5">
                  <div class="flex items-center justify-between gap-1.5">
                    <span class="text-[9px] sm:text-[10px] font-medium uppercase tracking-wider text-amber-400 font-mono truncate">
                      {{ item.category }}
                    </span>
                    <ArrowUpRight class="w-3.5 h-3.5 text-slate-500 group-hover:text-amber-400 shrink-0 transition-colors" />
                  </div>
                  <h4 class="text-xs sm:text-[13px] font-medium text-slate-100 leading-snug line-clamp-2 group-hover:text-amber-200 transition-colors tracking-normal">
                    {{ item.title }}
                  </h4>
                  <span
                    v-if="item.date"
                    class="text-[9px] sm:text-[10px] text-slate-400 font-mono block font-normal"
                  >
                    {{ item.date }}
                  </span>
                </div>
              </router-link>
            </div>
          </div>

          <div
            v-if="canRotateNews"
            class="flex items-center shrink-0"
          >
            <button
              type="button"
              class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-slate-950/90 backdrop-blur-md border border-white/15 flex items-center justify-center text-slate-400 hover:text-amber-400 hover:border-amber-500/50 transition-all cursor-pointer shrink-0 shadow-lg"
              :aria-label="t('pages.home.heroNewsNext', 'Berita berikutnya')"
              @click.prevent="advanceNews(1)"
            >
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Bottom Scholastic Dawn Metrics / Telemetry Bar (When Bottom Mode is stats or both) -->
      <div
        v-if="heroBottomMode === 'stats' || heroBottomMode === 'both'"
        ref="heroStatsRef"
        data-ja-customizer-target="hero-stats"
        class="pt-3 lg:pt-4 border-t border-white/15 space-y-2.5 shrink-0"
      >
        <div class="flex items-center justify-between gap-3">
          <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-300/90 font-mono flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
            {{ statsSectionLabel }}
          </span>
          <span class="text-[10px] text-slate-400 font-mono hidden sm:inline">
            {{ statsSubLabel }}
          </span>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
          <div class="rounded-xl border border-white/10 bg-slate-950/40 p-3 sm:p-3.5 relative overflow-hidden group hover:border-amber-400/40 transition-colors">
            <div class="text-lg sm:text-xl font-bold font-mono text-amber-400 tracking-tight flex items-baseline gap-1">
              {{ stat1Val }}
            </div>
            <div class="text-[9px] sm:text-[10px] font-medium text-slate-300 uppercase font-mono tracking-wider mt-0.5">
              {{ stat1Label }}
            </div>
          </div>
          <div class="rounded-xl border border-white/10 bg-slate-950/40 p-3 sm:p-3.5 relative overflow-hidden group hover:border-amber-400/40 transition-colors">
            <div class="text-lg sm:text-xl font-bold font-mono text-amber-400 tracking-tight flex items-baseline gap-1">
              {{ stat2Val }}
            </div>
            <div class="text-[9px] sm:text-[10px] font-medium text-slate-300 uppercase font-mono tracking-wider mt-0.5">
              {{ stat2Label }}
            </div>
          </div>
          <div class="rounded-xl border border-white/10 bg-slate-950/40 p-3 sm:p-3.5 relative overflow-hidden group hover:border-amber-400/40 transition-colors">
            <div class="text-lg sm:text-xl font-bold font-mono text-amber-400 tracking-tight flex items-baseline gap-1">
              {{ stat3Val }}
            </div>
            <div class="text-[9px] sm:text-[10px] font-medium text-slate-300 uppercase font-mono tracking-wider mt-0.5">
              {{ stat3Label }}
            </div>
          </div>
          <div class="rounded-xl border border-white/10 bg-slate-950/40 p-3 sm:p-3.5 relative overflow-hidden group hover:border-emerald-400/40 transition-colors">
            <div class="text-lg sm:text-xl font-bold font-mono text-emerald-400 tracking-tight flex items-baseline gap-1">
              {{ stat4Val }}
            </div>
            <div class="text-[9px] sm:text-[10px] font-medium text-slate-300 uppercase font-mono tracking-wider mt-0.5">
              {{ stat4Label }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import {
  SunMedium,
  GraduationCap,
  ArrowRight,
  ArrowUpRight,
  Sparkles,
  ChevronLeft,
  ChevronRight,
  Newspaper,
} from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import { useThemeDataBindings } from '@/modules/Layout/composables/useThemeDataBindings';
import SarangengeHeroVisual from '@/modules/Layout/views/themes/sarangenge/components/sections/SarangengeHeroVisual.vue';
import SarangengeSplitText from '@/modules/Layout/views/themes/sarangenge/components/shared/SarangengeSplitText.vue';
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';

const { t } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();
const { displayAccreditation, ppdbPortalUrl } = useSarangengeIdentity();
const { isAnimationEnabled, splitTextRevealSafe, staggerChildren, fadeInUp } = useThemeMotion();

// Template Refs for Motion
const heroTitleRef = ref<HTMLElement>();
const heroBadgeRef = ref<HTMLElement>();
const heroSubtitleRef = ref<HTMLElement>();
const heroCtaRef = ref<HTMLElement>();
const heroStatsRef = ref<HTMLElement>();
const heroNewsRef = ref<HTMLElement>();

// Hero Animation & Slider Settings
const heroAnimationEnabled = computed(() => getSetting('hero_animation_enabled', true) !== false);
const heroAnimationType = computed(() => String(getSetting('hero_animation_type', 'engineering') || 'engineering'));
const heroSliderEnabled = computed(() => getSetting('hero_slider_enabled', true) !== false);
const heroSliderAutoplay = computed(() => getSetting('hero_slider_autoplay', true) !== false);
const heroSliderIntervalMs = computed(() => Math.max(3000, Number(getSetting('hero_slider_interval', 7)) * 1000));

// Background Settings
const heroBgType = computed(() => String(getSetting('hero_bg_type', 'preset') || 'preset'));
const heroBgPreset = computed(() => String(getSetting('hero_bg_preset', 'sunrise_teal') || 'sunrise_teal'));
const heroBgImage = computed(() => String(getSetting('hero_bg_image', '') || ''));
const heroBgOverlayOpacity = computed(() => Number(getSetting('hero_bg_overlay_opacity', 75)) / 100);

const heroBgClass = computed(() => {
  if (heroBgType.value === 'preset') {
    return `sarangenge-hero--${heroBgPreset.value}`;
  }
  return '';
});

const heroBgStyle = computed(() => {
  if (heroBgType.value === 'custom_image' && heroBgImage.value) {
    return {
      backgroundImage: `url(${heroBgImage.value})`,
      backgroundSize: 'cover',
      backgroundPosition: 'center',
    };
  }
  return {};
});

const heroOverlayFinalOpacity = computed(() => {
  if (heroBgType.value === 'custom_image') {
    return heroBgOverlayOpacity.value;
  }
  return 0.35;
});

// Single-Slide Text Fallbacks (Backwards-compatible with customizer keys)
const heroBadge = computed(() => {
  return (getSetting('hero_badge_text', '') as string) || t('pages.home.heroBadge', 'PPDB JABAR 2026/2027');
});

const heroTitle = computed(() => {
  return (getSetting('hero_title', '') as string) || t('pages.home.heroTitle', 'Mekar Bersama Cahaya Pagi — Membentuk Generasi Vokasi Cerdas & Berkarakter.');
});

const heroSubtitle = computed(() => {
  return (getSetting('hero_subtitle', '') as string) || t('pages.home.heroSubtitle', 'Pendidikan kejuruan berstandar industri dengan bengkel praktik modern, teaching factory, dan penyiapan karir masa depan.');
});

const heroPrimaryText = computed(() => {
  return (getSetting('hero_primary_cta_text', '') as string) || t('pages.home.heroCta', 'Daftar PPDB Jabar (Resmi)');
});

const heroPrimaryLink = computed(() => {
  return (getSetting('hero_primary_cta_link', '') as string) || ppdbPortalUrl.value;
});

const heroSecondaryText = computed(() => {
  return (getSetting('hero_secondary_cta_text', '') as string) || t('pages.home.heroSecondary', 'Program Keahlian');
});

const secondaryTargetUrl = computed(() => {
  return (getSetting('hero_secondary_cta_link', '') as string) || '/programs';
});

// Default Vocational Multi-Slides for SMKN 6 Bandung
const defaultManualSlides = computed(() => [
  {
    id: 'ppdb-2026',
    badge: t('pages.home.slidePpdbBadge', 'PPDB JABAR 2026/2027'),
    title: t('pages.home.slidePpdbTitle', 'Mekar Bersama Cahaya Pagi — Membentuk Generasi Vokasi Cerdas & Berkarakter.'),
    subtitle: t('pages.home.slidePpdbSubtitle', 'Pendidikan kejuruan berstandar industri dengan bengkel praktik modern, teaching factory, dan penyiapan karir masa depan.'),
    ctaText: t('pages.home.heroCta', 'Daftar PPDB Jabar (Resmi)'),
    ctaUrl: heroPrimaryLink.value,
    animation: 'engineering',
  },
  {
    id: 'programs-tefa',
    badge: t('pages.home.slideTefaBadge', 'SMK PUSAT KEUNGGULAN'),
    title: t('pages.home.slideTefaTitle', '6 Program Keahlian Selaras Industri & Sertifikasi Profesi BNSP.'),
    subtitle: t('pages.home.slideTefaSubtitle', 'Desain Pemodelan Bangunan (BIM), Instalasi Listrik, Mesin Manufaktur CNC, Otomotif Kendaraan Ringan, Elektronika, dan Fabrikasi Logam.'),
    ctaText: t('pages.home.slideTefaCta', 'Jelajahi 6 Program'),
    ctaUrl: '/programs',
    animation: 'architecture',
  },
  {
    id: 'dudi-career',
    badge: t('pages.home.slideDudiBadge', 'LINK & MATCH DUDI'),
    title: t('pages.home.slideDudiTitle', 'Bursa Kerja Khusus & 98.5% Keterserapan Lulusan di Dunia Industri.'),
    subtitle: t('pages.home.slideDudiSubtitle', 'Kemitraan strategis dengan puluhan industri manufaktur, kontraktor sipil nasional, serta perusahaan teknologi terkemuka.'),
    ctaText: t('pages.home.slideDudiCta', 'Bursa Kerja & Alumni'),
    ctaUrl: '/career',
    animation: 'automotive',
  },
]);

// Dynamic CMS Data Binding for Slides
const { data: dynamicHeroSlides, hasBinding: hasHeroSlidesBinding } = useThemeDataBindings('hero', 'slides');

const activeSlides = computed(() => {
  if (hasHeroSlidesBinding.value && Array.isArray(dynamicHeroSlides.value) && dynamicHeroSlides.value.length > 0) {
    return (dynamicHeroSlides.value as Record<string, unknown>[]).map((item, idx) => ({
      id: String(item.id || idx),
      badge: String(item.badge || item.category || 'SMKN 6 BANDUNG'),
      title: String(item.title || item.name || ''),
      subtitle: String(item.subtitle || item.description || item.excerpt || ''),
      ctaText: String(item.cta_text || item.button_text || heroPrimaryText.value),
      ctaUrl: String(item.cta_url || item.url || heroPrimaryLink.value),
      animation: String(item.animation || (idx === 0 ? 'engineering' : idx === 1 ? 'architecture' : 'automotive')),
    }));
  }
  return defaultManualSlides.value;
});

const currentSlideIndex = ref(0);
const activeSlide = computed(() => {
  if (!heroSliderEnabled.value) {
    return {
      badge: heroBadge.value,
      title: heroTitle.value,
      subtitle: heroSubtitle.value,
      ctaText: heroPrimaryText.value,
      ctaUrl: heroPrimaryLink.value,
      animation: heroAnimationType.value,
    };
  }
  const slides = activeSlides.value;
  return slides[currentSlideIndex.value] || slides[0] || {
    badge: heroBadge.value,
    title: heroTitle.value,
    subtitle: heroSubtitle.value,
    ctaText: heroPrimaryText.value,
    ctaUrl: heroPrimaryLink.value,
    animation: heroAnimationType.value,
  };
});

const setSlide = (idx: number) => {
  const total = activeSlides.value.length;
  if (total <= 0) return;
  currentSlideIndex.value = (idx + total) % total;
};

const nextSlide = () => {
  setSlide(currentSlideIndex.value + 1);
};

const prevSlide = () => {
  setSlide(currentSlideIndex.value - 1);
};

let sliderAutoplayTimer: ReturnType<typeof setInterval> | number | null = null;

const stopSliderAutoplay = () => {
  if (sliderAutoplayTimer) {
    clearInterval(sliderAutoplayTimer);
    sliderAutoplayTimer = null;
  }
};

const startSliderAutoplay = () => {
  if (!heroSliderEnabled.value || !heroSliderAutoplay.value || activeSlides.value.length <= 1) return;
  stopSliderAutoplay();
  sliderAutoplayTimer = setInterval(() => {
    nextSlide();
  }, heroSliderIntervalMs.value);
};

const primaryTargetUrl = computed(() => activeSlide.value.ctaUrl || heroPrimaryLink.value);
const isExternalPrimary = computed(() => {
  const url = primaryTargetUrl.value;
  return typeof url === 'string' && (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('mailto:'));
});

// Bottom Mode & Scroll Indicators (Default to 'news' like Layung)
const heroBottomMode = computed(() => String(getSetting('hero_bottom_mode', 'news') || 'news'));
const heroShowScroll = computed(() => getSetting('hero_show_scroll', true) !== false);
const scrollCueText = computed(() => t('pages.home.scrollCue', 'JELAJAHI SMKN 6 BANDUNG'));
const statsSectionLabel = computed(() => t('pages.home.statsSectionLabel', 'METRIK & KINERJA PUSAT KEUNGGULAN'));
const statsSubLabel = computed(() => t('pages.home.statsSubLabel', 'Status Vokasi & Penjaminan Mutu'));
const newsSectionLabel = computed(() => t('pages.home.newsSectionLabel', 'WARTA KAMPUS & PPDB 2026'));

const stat1Val = computed(() => (getSetting('hero_stat_1_val', '100%') as string) || '100%');
const stat1Label = computed(() => (getSetting('hero_stat_1_label', 'Keterserapan DUDI & Kuliah') as string) || 'Keterserapan DUDI & Kuliah');
const stat2Val = computed(() => (getSetting('hero_stat_2_val', '6') as string) || '6');
const stat2Label = computed(() => (getSetting('hero_stat_2_label', 'Program Keahlian Vokasi') as string) || 'Program Keahlian Vokasi');
const stat3Val = computed(() => (getSetting('hero_stat_3_val', '1:12') as string) || '1:12');
const stat3Label = computed(() => (getSetting('hero_stat_3_label', 'Rasio Guru & Siswa') as string) || 'Rasio Guru & Siswa');
const stat4Val = computed(() => (getSetting('hero_stat_4_val', displayAccreditation.value) as string) || displayAccreditation.value);
const stat4Label = computed(() => (getSetting('hero_stat_4_label', 'Akreditasi BAN-S/M') as string) || 'Akreditasi BAN-S/M');

// Dynamic CMS Data Binding for Hero News
const heroNewsEnabled = computed(() => getSetting('hero_news_enabled', true) !== false);
const { data: dynamicHeroNews, hasBinding: hasHeroNewsBinding } = useThemeDataBindings('hero', 'news');

export type SchoolNewsItem = {
  id: string | number;
  title: string;
  category: string;
  date: string;
  url: string;
  image?: string;
};

const defaultSchoolNews = computed<SchoolNewsItem[]>(() => [
  {
    id: 'ppdb-info',
    title: t('pages.home.newsPpdbTitle', 'Sosialisasi Alur & Tata Cara Pendaftaran PPDB Jawa Barat 2026/2027'),
    category: 'PPDB',
    date: 'Juni 2026',
    url: ppdbPortalUrl.value || '/blog',
  },
  {
    id: 'lks-champions',
    title: t('pages.home.newsLksTitle', 'Siswa SMKN 6 Raih Medali Emas Lomba Kompetensi Siswa (LKS) Tingkat Provinsi'),
    category: 'Prestasi',
    date: 'Mei 2026',
    url: '/blog',
  },
  {
    id: 'dudi-partnership',
    title: t('pages.home.newsDudiTitle', 'Penandatanganan MoU Kelas Khusus Industri Bersama Mitra DUDI Terkemuka'),
    category: 'Kerjasama',
    date: 'Mei 2026',
    url: '/programs',
  },
  {
    id: 'tefa-expo',
    title: t('pages.home.newsTefaTitle', 'Pameran Produk Unggulan Teaching Factory & Uji Sertifikasi BNSP 2026'),
    category: 'Vokasi',
    date: 'April 2026',
    url: '/blog',
  },
]);

const fetchedPosts = ref<SchoolNewsItem[]>([]);

const allNewsItems = computed<SchoolNewsItem[]>(() => {
  if (hasHeroNewsBinding.value && Array.isArray(dynamicHeroNews.value) && dynamicHeroNews.value.length > 0) {
    return (dynamicHeroNews.value as Record<string, unknown>[]).map((item, idx) => ({
      id: String(item.id || idx),
      title: String(item.title || item.name || ''),
      category: String(item.category || item.badge || 'Warta'),
      date: String(item.date || item.created_at || ''),
      url: String(item.url || (item.slug ? `/blog/${item.slug}` : '/blog')),
      image: typeof item.image === 'string' ? item.image : typeof item.thumbnail === 'string' ? item.thumbnail : undefined,
    }));
  }
  if (fetchedPosts.value.length > 0) {
    return fetchedPosts.value;
  }
  return defaultSchoolNews.value;
});

// News Carousel Logic (Like Layung)
const newsIndex = ref(0);
const newsSlotCount = ref(4);

const updateNewsSlotCount = () => {
  if (typeof window === 'undefined') return;
  const w = window.innerWidth;
  if (w < 640) newsSlotCount.value = 1;
  else if (w < 1024) newsSlotCount.value = 2;
  else newsSlotCount.value = 4;
};

// Controls always active if more than 1 item (matching Layung)
const canRotateNews = computed(() => allNewsItems.value.length > 1);

const carouselItems = computed<SchoolNewsItem[]>(() => {
  const items = allNewsItems.value;
  if (items.length === 0) return [];
  const count = Math.min(newsSlotCount.value, items.length);
  const result: SchoolNewsItem[] = [];
  for (let i = 0; i < count; i++) {
    const idx = (newsIndex.value + i) % items.length;
    const item = items[idx];
    if (item) {
      result.push(item);
    }
  }
  return result;
});

const advanceNews = (delta: number) => {
  const total = allNewsItems.value.length;
  if (total <= 0) return;
  newsIndex.value = (newsIndex.value + delta + total) % total;
};

let newsAutoplayTimer: ReturnType<typeof setInterval> | number | null = null;

const stopNewsAutoplay = () => {
  if (newsAutoplayTimer) {
    clearInterval(newsAutoplayTimer);
    newsAutoplayTimer = null;
  }
};

const startNewsAutoplay = () => {
  if (!canRotateNews.value) return;
  stopNewsAutoplay();
  newsAutoplayTimer = setInterval(() => {
    advanceNews(1);
  }, 6000);
};

onMounted(async () => {
  updateNewsSlotCount();
  if (typeof window !== 'undefined') {
    window.addEventListener('resize', updateNewsSlotCount);
  }

  startSliderAutoplay();
  startNewsAutoplay();

  if (isAnimationEnabled()) {
    if (heroBadgeRef.value) {
      fadeInUp(heroBadgeRef.value, { delay: 0.05, duration: 0.5 });
    }
    if (heroTitleRef.value) {
      splitTextRevealSafe(heroTitleRef.value, { delay: 0.1, duration: 0.6 });
    }
    if (heroSubtitleRef.value) {
      fadeInUp(heroSubtitleRef.value, { delay: 0.2, duration: 0.5 });
    }
    if (heroCtaRef.value) {
      fadeInUp(heroCtaRef.value, { delay: 0.25, duration: 0.5 });
    }
    if (heroStatsRef.value) {
      staggerChildren(heroStatsRef.value, '> div', { delay: 0.35, stagger: 0.08 });
    }
    if (heroNewsRef.value) {
      staggerChildren(heroNewsRef.value, '> div:last-child > a', { delay: 0.35, stagger: 0.08 });
    }
  }

  try {
    const res = await api.get(publishingPaths.publicContents, {
      params: { type: 'post', per_page: 8, status: 'published', sort: '-published_at' },
    });
    const posts = res?.data?.data || res?.data;
    if (Array.isArray(posts) && posts.length > 0) {
      fetchedPosts.value = posts.map((p: any) => ({
        id: p.id,
        title: p.title,
        category: p.category?.name || p.category || 'Warta',
        date: p.published_at || p.created_at ? new Date(p.published_at || p.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '',
        url: `/blog/${p.slug || p.id}`,
        image: p.featured_image || p.thumbnail || undefined,
      }));
    }
  } catch {
    // Keep defaultSchoolNews
  }
});

onBeforeUnmount(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('resize', updateNewsSlotCount);
  }
  stopSliderAutoplay();
  stopNewsAutoplay();
});
</script>

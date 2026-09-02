<template>
  <section
    data-ja-customizer-target="hero"
    class="layung-hero px-4 sm:px-6 lg:px-8 py-8 sm:py-10 lg:py-12 min-h-[max(660px,calc(100dvh-4.5rem))] flex flex-col justify-between relative overflow-hidden"
    :class="heroBgClass"
    :style="heroBgStyle"
  >
    <!-- Background Texture & Grid Layer -->
    <div
      v-if="heroBgType === 'custom_image' && heroBgImage"
      class="absolute inset-0 bg-slate-950 pointer-events-none transition-opacity duration-300"
      :style="{ opacity: heroBgOverlayOpacity }"
    />
    <div class="layung-hero__grid" />

    <div
      class="max-w-7xl mx-auto w-full relative z-10 flex-1 flex flex-col justify-between gap-6 lg:gap-8 my-auto"
      @mouseenter="stopSliderAutoplay"
      @mouseleave="startSliderAutoplay"
    >
      <!-- Main Stage (2-Column Grid on Desktop) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center my-auto py-2">
        <!-- Left Column: Copy & Actions -->
        <div
          class="space-y-6"
          :class="heroAnimationEnabled ? 'lg:col-span-7' : 'lg:col-span-12 max-w-3xl'"
        >
          <!-- Badge -->
          <div
            ref="heroBadgeRef"
            class="flex flex-wrap items-center gap-3"
          >
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-medium bg-sky-500/15 text-sky-300 border border-sky-500/30">
              <span class="layung-status-dot" />
              {{ activeSlide.badge }}
            </span>
          </div>

          <!-- Headline & Description with Smooth Transition -->
          <div class="space-y-4">
            <h1
              ref="heroTitleRef"
              class="text-[1.65rem] sm:text-[2.25rem] lg:text-[2.65rem] font-medium tracking-tight text-white font-heading leading-[1.14]"
            >
              <LayungSplitText :key="activeSlide.title" :text="activeSlide.title" />
            </h1>
            <p
              ref="heroSubtitleRef"
              class="text-[13px] sm:text-sm text-slate-300/90 max-w-xl leading-relaxed font-normal"
            >
              {{ activeSlide.subtitle }}
            </p>
          </div>

          <!-- Call to Action Buttons -->
          <div
            ref="heroCtaRef"
            class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-1"
          >
            <Button
              as="router-link"
              :to="activeSlide.ctaUrl || contactHref"
              variant="primary"
              size="md"
              class="!py-3 !px-6 font-semibold shadow-lg shadow-sky-500/20 hover:shadow-sky-500/30 transition-all"
            >
              <Mail class="w-4 h-4 mr-1.5" />
              {{ activeSlide.ctaText || heroContactCtaText }}
            </Button>
            <Button
              v-if="nocWhatsAppUrl"
              as="a"
              :href="nocWhatsAppUrl"
              target="_blank"
              rel="noopener noreferrer"
              variant="cyber"
              size="md"
              class="!py-3 !px-6 font-semibold"
            >
              <MessageCircle class="w-4 h-4 mr-1.5" />
              {{ heroWhatsAppCtaText }}
            </Button>
          </div>

          <!-- Slider Navigation Tabs (When Slider Enabled) -->
          <div
            v-if="heroSliderEnabled && activeSlides.length > 1"
            class="pt-3 flex items-center gap-3 flex-wrap"
          >
            <div class="flex items-center gap-2 flex-wrap">
              <button
                v-for="(slide, sIdx) in activeSlides"
                :key="slide.id"
                type="button"
                class="flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs font-mono transition-all cursor-pointer"
                :class="currentSlideIndex === sIdx
                  ? 'bg-sky-500/15 border-sky-500/50 text-white shadow-sm shadow-sky-500/20'
                  : 'bg-slate-900/60 border-slate-800 text-slate-400 hover:text-slate-200 hover:border-slate-700'"
                @click="setSlide(sIdx)"
              >
                <span
                  class="w-1.5 h-1.5 rounded-full"
                  :class="currentSlideIndex === sIdx ? 'bg-sky-400 animate-pulse' : 'bg-slate-600'"
                />
                <span class="font-semibold">0{{ sIdx + 1 }}</span>
                <span class="truncate max-w-[130px] sm:max-w-[180px]">{{ slide.badge }}</span>
              </button>
            </div>

            <div class="flex items-center gap-1.5 ml-auto sm:ml-0">
              <button
                type="button"
                class="w-7 h-7 rounded-lg bg-slate-900/80 border border-slate-800 flex items-center justify-center text-slate-400 hover:text-white hover:border-sky-500/40 transition-colors"
                :aria-label="t('hero.prevSlide', 'Slide sebelumnya')"
                @click="prevSlide"
              >
                <ChevronLeft class="w-4 h-4" />
              </button>
              <button
                type="button"
                class="w-7 h-7 rounded-lg bg-slate-900/80 border border-slate-800 flex items-center justify-center text-slate-400 hover:text-white hover:border-sky-500/40 transition-colors"
                :aria-label="t('hero.nextSlide', 'Slide berikutnya')"
                @click="nextSlide"
              >
                <ChevronRight class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Right Column: Interactive Visual Animation Stage -->
        <div
          v-if="heroAnimationEnabled"
          class="lg:col-span-5 w-full flex justify-center lg:justify-end"
        >
          <HeroVisualAnimation
            :key="activeSlide.animation || heroAnimationType"
            :type="activeSlide.animation || heroAnimationType"
          />
        </div>
      </div>

      <!-- Bottom News & Promo Ticker -->
      <div
        v-if="heroNewsEnabled && carouselItems.length > 0"
        ref="heroNewsRef"
        data-ja-customizer-target="hero-news"
        data-ja-customizer-mode="bindings"
        class="pt-6 border-t border-slate-800/80 space-y-4"
        @mouseenter="stopAutoplay"
        @mouseleave="startAutoplay"
      >
        <div class="flex items-center justify-between gap-3">
          <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 font-mono">
            {{ sectionLabel }}
          </span>
          <router-link
            :to="viewAllHref"
            class="text-[11px] font-semibold text-sky-400 hover:text-sky-300 transition-colors inline-flex items-center gap-1 font-mono"
          >
            {{ viewAllLabel }}
            <ArrowUpRight class="w-3.5 h-3.5" />
          </router-link>
        </div>

        <div class="flex items-stretch gap-3">
          <div
            v-if="canRotate"
            class="flex items-center shrink-0"
          >
            <button
              type="button"
              class="w-10 h-10 rounded-full bg-slate-950/90 backdrop-blur-md border border-slate-700/80 flex items-center justify-center text-slate-400 hover:text-sky-400 hover:border-sky-500/50 transition-all cursor-pointer shrink-0"
              :aria-label="t('hero.newsPrevious', 'Berita sebelumnya')"
              @click.prevent="advanceNews(-1)"
            >
              <ChevronLeft class="w-4 h-4" />
            </button>
          </div>

          <div class="flex-1 min-w-0 overflow-hidden">
            <div
              ref="heroNewsCardsRef"
              class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-slate-300"
            >
              <router-link
                v-for="item in carouselItems"
                :key="`${carouselEpoch}-${item.id}`"
                :to="item.url"
                class="group flex items-stretch gap-3 overflow-hidden rounded-xl border border-slate-800/80 bg-slate-950/40 hover:bg-slate-900/70 hover:border-sky-500/30 p-3 sm:p-3.5 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500/60"
              >
                <div class="w-[4.5rem] sm:w-20 h-[3.25rem] sm:h-[3.75rem] overflow-hidden shrink-0 rounded-lg border border-slate-800/80 bg-slate-900">
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
                    class="w-full h-full flex items-center justify-center bg-slate-900/80 text-sky-500/40"
                  >
                    <Newspaper class="w-5 h-5" />
                  </div>
                </div>
                <div class="min-w-0 flex-1 flex flex-col justify-center space-y-1.5 py-0.5">
                  <div class="flex items-center justify-between gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-sky-400/90 font-mono truncate">
                      {{ item.category }}
                    </span>
                    <ArrowUpRight class="w-3.5 h-3.5 text-slate-600 group-hover:text-sky-400 shrink-0 transition-colors" />
                  </div>
                  <strong class="block text-sm font-semibold text-white font-heading leading-snug line-clamp-2 group-hover:text-sky-200 transition-colors">
                    {{ item.title }}
                  </strong>
                  <span
                    v-if="item.date"
                    class="text-[10px] text-slate-500 font-mono block"
                  >
                    {{ item.date }}
                  </span>
                </div>
              </router-link>
            </div>
          </div>

          <div
            v-if="canRotate"
            class="flex items-center shrink-0"
          >
            <button
              type="button"
              class="w-10 h-10 rounded-full bg-slate-950/90 backdrop-blur-md border border-slate-700/80 flex items-center justify-center text-slate-400 hover:text-sky-400 hover:border-sky-500/50 transition-all cursor-pointer shrink-0"
              :aria-label="t('hero.newsNext', 'Berita berikutnya')"
              @click.prevent="advanceNews(1)"
            >
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
import { Mail, MessageCircle, ArrowUpRight, ChevronLeft, ChevronRight, Newspaper } from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Button } from '@/modules/Layout/views/themes/layung/ui';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion';
import { useLayungIdentity } from '@/modules/Layout/views/themes/layung/composables/useLayungIdentity';
import { useLayungHeroNews } from '@/modules/Layout/views/themes/layung/composables/useLayungHeroNews';
import { useThemeDataBindings } from '@/modules/Layout/composables/useThemeDataBindings';
import HeroVisualAnimation from '@/modules/Layout/views/themes/layung/components/sections/HeroVisualAnimation.vue';
import {
  STALE_HERO_BADGES,
  STALE_HERO_CTAS,
  STALE_HERO_SUBTITLES,
  STALE_HERO_TITLES,
  resolveLayungLocalizedCopy,
  resolveThemeHref,
} from '@/modules/Layout/views/themes/layung/composables/resolveLayungLocalizedCopy';
import LayungSplitText from '@/modules/Layout/views/themes/layung/components/shared/LayungSplitText.vue';

const carouselSlotCount = ref(4);
const updateCarouselSlotCount = () => {
  if (typeof window === 'undefined') return;
  const width = window.innerWidth;
  if (width < 640) carouselSlotCount.value = 1;
  else if (width < 1024) carouselSlotCount.value = 2;
  else carouselSlotCount.value = 4;
};

const {
  heroNewsEnabled,
  carouselItems,
  canRotate,
  heroNewsAutoplayMs,
  nextIndex,
  prevIndex,
  viewAllHref,
  viewAllLabel,
  sectionLabel,
} = useLayungHeroNews(carouselSlotCount);

const { t, locale } = useThemeI18n('layung');
const { getSetting } = useTheme();
const { createTimeline, splitTextRevealSafe, staggerChildren, motion } = useThemeMotion();
const { nocWhatsAppUrl } = useLayungIdentity();

// Hero Animation & Slider Settings
const heroAnimationEnabled = computed(() => getSetting('hero_animation_enabled', true) !== false);
const heroAnimationType = computed(() => String(getSetting('hero_animation_type', 'network') || 'network'));

const heroSliderEnabled = computed(() => getSetting('hero_slider_enabled', false) === true);
const heroSliderAutoplay = computed(() => getSetting('hero_slider_autoplay', true) !== false);
const heroSliderIntervalMs = computed(() => Math.max(3000, Number(getSetting('hero_slider_interval', 5)) * 1000));

// Background Customizer Settings
const heroBgType = computed(() => String(getSetting('hero_bg_type', 'preset') || 'preset'));
const heroBgPreset = computed(() => String(getSetting('hero_bg_preset', 'coastal_cyber') || 'coastal_cyber'));
const heroBgImage = computed(() => String(getSetting('hero_bg_image', '') || ''));
const heroBgOverlayOpacity = computed(() => Number(getSetting('hero_bg_overlay_opacity', 65)) / 100);

const heroBgClass = computed(() => {
  if (heroBgType.value === 'preset') {
    return `layung-hero--${heroBgPreset.value}`;
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

// Dynamic CMS Data Binding for Slides
const { data: dynamicHeroSlides, hasBinding: hasHeroSlidesBinding } = useThemeDataBindings('hero', 'slides');

const heroBadgeRef = ref<HTMLElement>();
const heroTitleRef = ref<HTMLElement>();
const heroSubtitleRef = ref<HTMLElement>();
const heroCtaRef = ref<HTMLElement>();
const heroNewsRef = ref<HTMLElement>();
const heroNewsCardsRef = ref<HTMLElement>();

const carouselEpoch = ref(0);
const isAnimating = ref(false);
let newsTimer: ReturnType<typeof setInterval> | number | null = null;
let sliderAutoplayTimer: ReturnType<typeof setInterval> | number | null = null;

const copy = (key: string, fallback: string, stale: readonly string[] = []) =>
  resolveLayungLocalizedCopy({
    getSetting,
    locale: String(locale.value),
    key,
    fallback,
    stale,
  });

const heroBadgeText = computed(() =>
  copy('hero_badge_text', t('hero.badge', 'Provide different IT solution'), STALE_HERO_BADGES),
);

const heroTitle = computed(() =>
  copy(
    'hero_title',
    t('hero.headline', 'Internet Service Provider dan Managed Service Provider'),
    STALE_HERO_TITLES,
  ),
);

const heroSubtitle = computed(() =>
  copy(
    'hero_subtitle',
    t(
      'hero.subheadline',
      'Bukan hanya konektivitas jaringan. K2NET membantu Anda mulai dari koneksi internet hingga layanan IT di lingkungan kerja Anda.',
    ),
    STALE_HERO_SUBTITLES,
  ),
);

const heroContactCtaText = computed(() =>
  copy('hero_primary_cta_text', t('hero.ctaPrimary', 'Hubungi Kami'), STALE_HERO_CTAS),
);

const heroWhatsAppCtaText = computed(() =>
  copy('hero_secondary_cta_text', t('hero.ctaSecondary', 'Chat WhatsApp'), STALE_HERO_CTAS),
);

const contactHref = computed(() =>
  resolveThemeHref(getSetting('hero_contact_url', ''), '/contact'),
);

// Fallback manual slides for K2NET core business
const defaultManualSlides = computed(() => [
  {
    id: 'isp',
    badge: 'K2NET DEDICATED',
    title: t('hero.slideIspTitle', 'Internet Service Provider Berbasis Fiber Optik'),
    subtitle: t('hero.slideIspDesc', 'Konektivitas simetris 1:1 tanpa FUP dengan dukungan latensi rendah dan jaminan SLA 99.98%.'),
    ctaText: t('hero.slideIspCta', 'Lihat Paket Internet'),
    ctaUrl: '/pricing',
    animation: 'network',
  },
  {
    id: 'msp',
    badge: 'MANAGED IT SERVICES',
    title: t('hero.slideMspTitle', 'Solusi Pengelolaan Infrastruktur & Keamanan IT'),
    subtitle: t('hero.slideMspDesc', 'Dari pengawasan jaringan 24/7 NOC, mitigasi serangan siber, hingga pemeliharaan sistem data center Anda.'),
    ctaText: t('hero.slideMspCta', 'Konsultasi Layanan'),
    ctaUrl: '/solusi',
    animation: 'datacenter',
  },
  {
    id: 'wireless',
    badge: 'ENTERPRISE WI-FI 6/7',
    title: t('hero.slideWifiTitle', 'Infrastruktur Nirkabel & Pengadaan Perangkat IT'),
    subtitle: t('hero.slideWifiDesc', 'Desain jaringan wireless kantor berkapasitas tinggi, seamless roaming, dan pengadaan perangkat resmi bergaransi.'),
    ctaText: t('hero.slideWifiCta', 'Hubungi Sales'),
    ctaUrl: contactHref.value,
    animation: 'wireless',
  },
]);

const activeSlides = computed(() => {
  if (hasHeroSlidesBinding.value && Array.isArray(dynamicHeroSlides.value) && dynamicHeroSlides.value.length > 0) {
    return (dynamicHeroSlides.value as Record<string, unknown>[]).map((item, idx) => ({
      id: String(item.id || idx),
      badge: String(item.badge || item.category || 'K2NET SOLUTION'),
      title: String(item.title || item.name || ''),
      subtitle: String(item.description || item.excerpt || item.subtitle || ''),
      ctaText: String(item.cta_text || item.button_text || heroContactCtaText.value),
      ctaUrl: String(item.cta_url || item.url || contactHref.value),
      animation: String(item.animation || (idx % 2 === 0 ? 'network' : 'datacenter')),
    }));
  }
  return defaultManualSlides.value;
});

const currentSlideIndex = ref(0);
const activeSlide = computed(() => {
  if (!heroSliderEnabled.value) {
    return {
      badge: heroBadgeText.value,
      title: heroTitle.value,
      subtitle: heroSubtitle.value,
      ctaText: heroContactCtaText.value,
      ctaUrl: contactHref.value,
      animation: heroAnimationType.value,
    };
  }
  const slides = activeSlides.value;
  return slides[currentSlideIndex.value] || slides[0] || {
    badge: heroBadgeText.value,
    title: heroTitle.value,
    subtitle: heroSubtitle.value,
    ctaText: heroContactCtaText.value,
    ctaUrl: contactHref.value,
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

const stopSliderAutoplay = () => {
  if (sliderAutoplayTimer) {
    clearInterval(sliderAutoplayTimer);
    sliderAutoplayTimer = null;
  }
};

const startSliderAutoplay = () => {
  stopSliderAutoplay();
  if (!heroSliderEnabled.value || !heroSliderAutoplay.value || activeSlides.value.length <= 1) return;
  sliderAutoplayTimer = window.setInterval(() => {
    nextSlide();
  }, heroSliderIntervalMs.value);
};

watch([heroSliderEnabled, heroSliderAutoplay, heroSliderIntervalMs], () => {
  if (heroSliderEnabled.value && heroSliderAutoplay.value) {
    startSliderAutoplay();
  } else {
    stopSliderAutoplay();
  }
});

const getNewsCards = () => heroNewsCardsRef.value?.querySelectorAll(':scope > a') ?? [];

const runEnterAnimation = (direction: 1 | -1) => {
  const cards = getNewsCards();
  if (!cards.length) return;
  motion.fromTo(
    cards,
    { opacity: 0, x: direction * 28, scale: 0.98 },
    {
      opacity: 1,
      x: 0,
      scale: 1,
      duration: 0.45,
      stagger: 0.06,
      ease: 'power3.out',
      clearProps: 'transform',
    },
  );
};

const bumpCarouselIndex = (direction: 1 | -1) => {
  carouselEpoch.value += 1;
  if (direction > 0) nextIndex();
  else prevIndex();
};

const advanceNews = (direction: 1 | -1) => {
  if (isAnimating.value) return;

  const cards = getNewsCards();
  if (!cards.length || !canRotate.value) {
    bumpCarouselIndex(direction);
    return;
  }

  isAnimating.value = true;
  motion.killTweensOf(cards);
  motion.to(cards, {
    opacity: 0,
    x: direction * -28,
    scale: 0.98,
    duration: 0.32,
    stagger: 0.04,
    ease: 'power2.in',
    onComplete: () => {
      bumpCarouselIndex(direction);
      nextTick(() => {
        isAnimating.value = false;
        runEnterAnimation(direction);
      });
    },
  });
};

const stopAutoplay = () => {
  if (newsTimer) {
    clearInterval(newsTimer);
    newsTimer = null;
  }
};

const startAutoplay = () => {
  stopAutoplay();
  if (!canRotate.value) return;
  newsTimer = window.setInterval(() => advanceNews(1), heroNewsAutoplayMs.value);
};

watch(canRotate, (rotate) => {
  if (rotate) startAutoplay();
  else stopAutoplay();
});

watch(heroNewsAutoplayMs, () => {
  if (canRotate.value) startAutoplay();
});

onMounted(async () => {
  updateCarouselSlotCount();
  window.addEventListener('resize', updateCarouselSlotCount);

  await nextTick();
  const tl = createTimeline({ defaults: { ease: 'power3.out' } });
  if (heroBadgeRef.value) tl.from(heroBadgeRef.value, { y: 18, opacity: 0, duration: 0.7 }, 0);
  if (heroTitleRef.value) splitTextRevealSafe(heroTitleRef.value, { delay: 0.18, stagger: 0.045 });
  if (heroSubtitleRef.value) tl.from(heroSubtitleRef.value, { y: 16, opacity: 0, duration: 0.65 }, 0.28);
  if (heroCtaRef.value) tl.from(heroCtaRef.value, { y: 20, opacity: 0, duration: 0.7 }, 0.38);
  if (heroNewsRef.value) {
    staggerChildren(heroNewsRef.value, ':scope .grid > a', {
      distance: 20,
      stagger: 0.08,
      delay: 0.2,
      start: 'top 95%',
    });
  }

  startAutoplay();
  startSliderAutoplay();
});

onBeforeUnmount(() => {
  stopAutoplay();
  stopSliderAutoplay();
  window.removeEventListener('resize', updateCarouselSlotCount);
  motion.killTweensOf(getNewsCards());
});
</script>

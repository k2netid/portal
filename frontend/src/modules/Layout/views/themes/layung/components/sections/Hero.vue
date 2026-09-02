<template>
  <section
    data-ja-customizer-target="hero"
    class="layung-hero px-4 sm:px-6 lg:px-8 py-16 lg:py-20 relative overflow-hidden"
  >
    <div class="layung-hero__grid" />

    <div class="max-w-7xl mx-auto w-full relative z-10 space-y-8">
      <div
        ref="heroBadgeRef"
        class="flex flex-wrap items-center gap-3"
      >
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-medium bg-sky-500/15 text-sky-300 border border-sky-500/30">
          <span class="layung-status-dot" />
          {{ heroBadgeText }}
        </span>
      </div>

      <div class="max-w-3xl space-y-4">
        <h1
          ref="heroTitleRef"
          class="text-[1.65rem] sm:text-[2.15rem] font-medium tracking-tight text-white font-heading leading-[1.15]"
        >
          <LayungSplitText :text="heroTitle" />
        </h1>
        <p
          ref="heroSubtitleRef"
          class="text-[13px] sm:text-sm text-slate-400 max-w-xl leading-relaxed font-normal"
        >
          {{ heroSubtitle }}
        </p>
      </div>

      <div
        ref="heroCtaRef"
        class="flex flex-col sm:flex-row gap-3"
      >
        <Button
          as="router-link"
          :to="contactHref"
          variant="primary"
          size="md"
          class="!py-3 !px-6 font-semibold"
        >
          <Mail class="w-4 h-4 mr-1.5" />
          {{ heroContactCtaText }}
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

const heroBadgeRef = ref<HTMLElement>();
const heroTitleRef = ref<HTMLElement>();
const heroSubtitleRef = ref<HTMLElement>();
const heroCtaRef = ref<HTMLElement>();
const heroNewsRef = ref<HTMLElement>();
const heroNewsCardsRef = ref<HTMLElement>();

const carouselEpoch = ref(0);
const isAnimating = ref(false);
let newsTimer: ReturnType<typeof setInterval> | number | null = null;

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
});

onBeforeUnmount(() => {
  stopAutoplay();
  window.removeEventListener('resize', updateCarouselSlotCount);
  motion.killTweensOf(getNewsCards());
});
</script>

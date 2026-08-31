<template>
  <div 
    ref="heroSection"
    class="relative overflow-hidden flex flex-col"
    :style="{ height: heroHeight }"
  >
    <!-- Hero Slides (Dynamic) -->
    <div 
      v-for="(slide, idx) in heroSlides" 
      :key="'slide-' + idx"
      ref="slideElements"
      class="absolute inset-0 w-full h-full transition-opacity duration-1000"
      :style="{ opacity: idx === activeSlide ? 1 : 0, zIndex: idx === activeSlide ? 2 : 1 }"
    >
      <div 
        v-if="slide"
        class="absolute inset-0 w-full h-full"
      >
        <img 
          :src="slide" 
          class="w-full h-full object-cover scale-105"
          :alt="t('theme.janari.common.slideAlt', { n: idx + 1 })"
          width="1920"
          height="1080"
          sizes="100vw"
          :decoding="idx === 0 ? 'sync' : 'async'"
          :fetchpriority="idx === 0 ? 'high' : 'low'"
          :loading="idx === 0 ? 'eager' : 'lazy'"
          referrerpolicy="no-referrer"
        >
      </div>
      <!-- Non-image fallback: sophisticated dark gradient -->
      <div 
        v-else
        class="absolute inset-0 w-full h-full bg-gradient-to-br from-black via-zinc-900 to-black"
      />
    </div>
    
    <!-- Dark overlay for text readability -->
    <div
      class="absolute inset-0 z-10"
      :style="{ background: `linear-gradient(to top, rgba(0,0,0,${heroOverlay/100 * 1.2}), rgba(0,0,0,${heroOverlay/100 * 0.4}), rgba(0,0,0,${heroOverlay/100 * 0.15}))` }"
    />
    
    <!-- Hero Content Overlay -->
    <div
      class="relative z-20 flex flex-1 flex-col justify-center min-h-0 pt-6 md:pt-8 pb-[8.5rem] md:pb-[9.5rem] px-6 lg:px-24 overflow-hidden"
      :class="heroAlignment"
    >
      <!-- Badge -->
      <span
        ref="heroBadge"
        class="inline-flex items-center px-4 py-2 rounded-full border border-primary/40 text-[9px] font-bold tracking-[0.5em] uppercase text-white mb-4 md:mb-5 bg-primary/8 backdrop-blur-sm shrink-0"
      >
        <span class="w-1 h-1 bg-primary rounded-full mr-2" />
        {{ heroBadgeText }}
      </span>

      <!-- Large Centered Title — capped size so CTAs stay visible above news strip -->
      <h1
        ref="heroTitleRef"
        class="text-2xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-[4.25rem] font-heading font-black tracking-tighter text-white mb-3 md:mb-4 leading-[1.05] uppercase text-sharp max-w-5xl line-clamp-4"
        :class="heroAlignment.includes('items-start') ? 'text-left' : 'text-center'"
      >
        <JanariSplitText :text="heroTitleText" />
      </h1>

      <!-- Subtitle -->
      <p
        class="text-sm md:text-base text-white/85 max-w-2xl mb-6 font-medium leading-relaxed line-clamp-2 shrink-0"
        :class="heroAlignment.includes('items-start') ? 'text-left' : 'text-center'"
      >
        {{ heroSubtitleText }}
      </p>

      <!-- CTA Buttons -->
      <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4 w-full sm:w-auto shrink-0 relative z-20">
        <router-link
          :to="heroCtaPrimaryUrl"
          class="w-full sm:w-auto px-8 py-3 text-xs font-bold text-center tracking-[0.5px] uppercase bg-white text-black rounded-[6px] hover:bg-gray-100 hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300"
        >
          {{ heroCtaPrimaryText }}
        </router-link>
        <router-link
          :to="heroCtaPricingUrl"
          class="w-full sm:w-auto px-8 py-3 text-xs font-bold text-center tracking-[0.5px] uppercase border border-white/40 text-white rounded-[6px] hover:border-white hover:bg-white/10 hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300"
        >
          {{ heroCtaPricingText }}
        </router-link>
        <a
          href="/auth/console-sign-in"
          class="w-full sm:w-auto px-6 py-3 text-xs font-bold text-center tracking-[0.5px] uppercase text-white/80 hover:text-white transition-colors"
        >
          {{ heroCtaConsoleText }}
        </a>
      </div>

      <!-- Slide Indicators (Dynamic) -->
      <div
        v-if="heroSlides.length > 1"
        class="flex items-center gap-3 mt-10"
      >
        <button
          v-for="(_, idx) in heroSlides"
          :key="'dot-' + idx"
          class="transition-all duration-500 rounded-full cursor-pointer min-w-10 min-h-10 flex items-center justify-center"
          :class="idx === activeSlide ? 'w-8 h-2 bg-primary' : 'w-2 h-2 bg-white/30 hover:bg-white/60'"
          :aria-label="t('theme.janari.hero.slideJumpAria', { n: idx + 1 })"
          @click="goToSlide(idx)"
        >
          <span class="sr-only">{{ t('theme.janari.common.slideSrOnly', { n: idx + 1 }) }}</span>
        </button>
      </div>
    </div>

    <!-- Animated Scroll Indicator -->
    <div
      v-if="heroShowScroll"
      class="absolute bottom-[9.5rem] left-1/2 -translate-x-1/2 z-20 hidden lg:block opacity-50 hover:opacity-100 transition-opacity"
    >
      <div class="flex flex-col items-center gap-2">
        <span class="text-[9px] font-bold tracking-[0.4em] uppercase text-white/70">{{ scrollLabel }}</span>
        <div class="w-[1px] h-12 bg-gradient-to-b from-primary to-transparent relative overflow-hidden">
          <div class="absolute top-0 left-0 w-full h-1/2 bg-white animate-scroll-line" />
        </div>
      </div>
    </div>

    <!-- ========== REFINED: HERO NEWS OVERLAY (3-Column Interactive Carousel) ========== -->
    <div
      v-if="visibleNews.length > 0"
      class="absolute bottom-0 left-0 w-full z-30 bg-zinc-950/90 backdrop-blur-md border-t border-white/10 hidden md:flex items-stretch h-[112px]"
    >
      <!-- COL 1: LATEST NEWS -->
      <router-link
        :to="visibleNews[primaryIndex]?.url || '#'"
        class="w-1/3 flex border-r border-white/10 group relative overflow-hidden bg-transparent hover:bg-white/10 transition-colors cursor-pointer shrink-0"
      >
        <div class="w-12 lg:w-16 bg-zinc-950 flex items-center justify-center shrink-0 border-r border-white/15 group-hover:bg-primary transition-colors duration-300 relative z-10 box-border">
          <span class="rotate-[-90deg] origin-center whitespace-nowrap text-[9px] font-black tracking-[0.4em] uppercase text-white group-hover:text-primary-foreground transition-colors duration-300 absolute">{{ latestNewsLabel }}</span>
        </div>
        <div class="flex-1 flex flex-col justify-center px-6 lg:px-10 relative overflow-hidden h-full">
          <div class="flex flex-col justify-center w-full gap-2">
            <div class="flex items-center gap-4">
              <span class="w-2 h-px bg-white/40 group-hover:w-4 group-hover:bg-primary transition-all duration-300" />
              <span class="text-[10px] font-black tracking-widest text-white/80 group-hover:text-white transition-colors">{{ visibleNews[primaryIndex]?.date || '' }}</span>
              <span class="text-[8px] px-2 py-1 border border-white/25 text-white/80 uppercase tracking-widest group-hover:border-primary group-hover:text-primary transition-colors">{{ visibleNews[primaryIndex]?.category || t('theme.janari.common.info') }}</span>
            </div>
            <p class="text-sm md:text-base font-medium text-white group-hover:text-white transition-colors line-clamp-2 pl-6 leading-relaxed">
              {{ visibleNews[primaryIndex]?.title || t('theme.janari.common.loading') }}
            </p>
          </div>
        </div>
      </router-link>

      <!-- COL 2 -->
      <div class="w-1/3 flex group relative overflow-hidden bg-transparent hover:bg-white/10 transition-colors shrink-0 items-center justify-center border-r border-white/10 z-0">
        <div class="absolute left-3 lg:left-5 top-1/2 -translate-y-1/2 z-20">
          <button
            class="w-10 h-10 rounded-full bg-zinc-950/90 backdrop-blur-md border border-white/20 flex items-center justify-center text-white/80 hover:text-primary hover:border-primary transition-all cursor-pointer"
            :aria-label="t('theme.janari.common.newsPrevious')"
            @click.prevent="prevNews"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="12"
              height="12"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="3"
              stroke-linecap="round"
              stroke-linejoin="round"
            ><path d="m15 18-6-6 6-6" /></svg>
          </button>
        </div>
        <router-link
          :to="visibleNews[secondaryIndex]?.url || '#'"
          class="w-full h-full cursor-pointer flex items-center"
        >
          <div class="flex items-center gap-4 lg:gap-6 w-full p-4 pl-14 lg:pl-16">
            <div class="w-24 h-14 lg:w-32 lg:h-20 overflow-hidden shrink-0 bg-white/10 relative z-10 border border-white/10">
              <img 
                v-if="visibleNews[secondaryIndex]?.image"
                :src="visibleNews[secondaryIndex].image" 
                class="w-full h-full object-cover" 
                :alt="visibleNews[secondaryIndex]?.title || t('theme.janari.common.newsImageAlt')"
                width="128"
                height="80"
                loading="lazy"
                decoding="async"
                sizes="(max-width: 1024px) 96px, 128px"
                referrerpolicy="no-referrer"
              >
              <!-- Non-image fallback for news thumb -->
              <div 
                v-else
                class="w-full h-full flex items-center justify-center bg-primary/10 text-primary"
              >
                <Newspaper class="w-6 h-6 opacity-70 text-primary" />
              </div>
            </div>
            <div class="flex-1 pr-6 flex flex-col justify-center">
              <p class="text-[8px] text-primary font-black uppercase tracking-[0.25em] mb-2">
                {{ visibleNews[secondaryIndex]?.category || t('theme.janari.common.info') }}
              </p>
              <p class="text-xs font-bold text-white group-hover:text-white transition-colors line-clamp-2">
                {{ visibleNews[secondaryIndex]?.title || '' }}
              </p>
            </div>
          </div>
        </router-link>
      </div>

      <!-- COL 3 -->
      <div class="w-1/3 flex group relative overflow-hidden bg-transparent hover:bg-white/10 transition-colors shrink-0 items-center justify-center z-0">
        <router-link
          :to="visibleNews[tertiaryIndex]?.url || '#'"
          class="w-full h-full cursor-pointer flex items-center"
        >
          <div class="flex items-center gap-4 lg:gap-6 w-full p-4 pl-6 lg:pl-10">
            <div class="w-24 h-14 lg:w-32 lg:h-20 overflow-hidden shrink-0 bg-white/10 relative z-10 border border-white/10">
              <img 
                v-if="visibleNews[tertiaryIndex]?.image"
                :src="visibleNews[tertiaryIndex].image" 
                class="w-full h-full object-cover" 
                :alt="visibleNews[tertiaryIndex]?.title || t('theme.janari.common.newsImageAlt')"
                width="128"
                height="80"
                loading="lazy"
                decoding="async"
                sizes="(max-width: 1024px) 96px, 128px"
                referrerpolicy="no-referrer"
              >
              <!-- Non-image fallback for news thumb -->
              <div 
                v-else
                class="w-full h-full flex items-center justify-center bg-primary/10 text-primary"
              >
                <Newspaper class="w-6 h-6 opacity-70 text-primary" />
              </div>
            </div>
            <div class="flex-1 pr-14 lg:pr-16 flex flex-col justify-center">
              <p class="text-[8px] text-primary font-black uppercase tracking-[0.25em] mb-2">
                {{ visibleNews[tertiaryIndex]?.category || t('theme.janari.common.info') }}
              </p>
              <p class="text-xs font-bold text-white group-hover:text-white transition-colors line-clamp-2">
                {{ visibleNews[tertiaryIndex]?.title || '' }}
              </p>
            </div>
          </div>
        </router-link>
        <div class="absolute right-3 lg:right-5 top-1/2 -translate-y-1/2 z-20">
          <button
            class="w-10 h-10 rounded-full bg-zinc-950/90 backdrop-blur-md border border-white/20 flex items-center justify-center text-white/80 hover:text-primary hover:border-primary transition-all cursor-pointer"
            :aria-label="t('theme.janari.common.newsNext')"
            @click.prevent="nextNews"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="12"
              height="12"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="3"
              stroke-linecap="round"
              stroke-linejoin="round"
            ><path d="m9 18 6-6-6-6" /></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile News -->
    <div class="md:hidden flex flex-col justify-center px-4 py-8 border-b border-white/10 bg-black/80 backdrop-blur-lg w-full z-40">
      <div class="flex items-center justify-between mb-4">
        <span class="text-[9px] font-black tracking-widest text-primary uppercase">{{ latestNewsLabel }}</span>
        <div class="flex gap-3">
          <button
            class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center text-white/70"
            :aria-label="t('theme.janari.common.newsPrevious')"
            @click.prevent="prevNews"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="10"
              height="10"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="3"
            ><path d="m15 18-6-6 6-6" /></svg>
          </button>
          <button
            class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center text-white/70"
            :aria-label="t('theme.janari.common.newsNext')"
            @click.prevent="nextNews"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="10"
              height="10"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="3"
            ><path d="m9 18 6-6-6-6" /></svg>
          </button>
        </div>
      </div>
      <router-link :to="visibleNews[primaryIndex]?.url || '#'">
        <div class="space-y-2">
          <span class="text-[8px] text-white/70 uppercase tracking-widest">{{ visibleNews[primaryIndex]?.date || '' }}</span>
          <p class="text-sm font-bold text-white line-clamp-2">
            {{ visibleNews[primaryIndex]?.title || t('theme.janari.common.loading') }}
          </p>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import JanariSplitText from '../shared/JanariSplitText.vue'
import { useTheme } from '@/modules/Layout/composables/useTheme'
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion'
import { useThemeDataBindings } from '@/modules/Layout/composables/useThemeDataBindings'
import api from '@/engine/api/client'
import { publishingPaths } from '@/engine/api/paths'
import { useJanariIdentity } from '@/modules/Layout/views/themes/janari/composables/useJanariIdentity'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'

const { getSetting } = useTheme()
const { localizedString } = useLocalizedThemeSetting()
const { displaySiteName } = useJanariIdentity()
const { t } = useI18n({ useScope: 'global' })
const { splitTextRevealSafe, createTimeline, motion } = useThemeMotion()
import { Newspaper } from 'lucide-vue-next'


const heroBadge = ref<HTMLElement>()
const heroTitleRef = ref<HTMLElement>()
const slideElements = ref<HTMLElement[]>([])

const { data: dynamicHeroNews, hasBinding: hasHeroNewsBinding } = useThemeDataBindings('hero', 'news')

const heroTitleText = computed(
    () =>
        localizedString('hero_title') ||
        displaySiteName.value ||
        t('theme.janari.hero.brandFallback'),
);
const heroBadgeText = computed(
    () => localizedString('hero_badge') || t('theme.janari.hero.badgeDefault'),
);
const heroSubtitleText = computed(
    () => localizedString('hero_subtitle') || t('theme.janari.hero.subtitleDefault'),
)
const heroCtaPricingText = computed(
    () => localizedString('cta_secondary_text') || t('theme.janari.hero.ctaPricing'),
)
const heroCtaPrimaryText = computed(
    () => localizedString('hero_cta_primary') || t('theme.janari.hero.ctaPrimary'),
)
const heroCtaConsoleText = computed(
    () => localizedString('hero_cta_console') || t('theme.janari.hero.ctaSecondary'),
);
const scrollLabel = computed(() => localizedString('hero_scroll_label') || t('theme.janari.common.scroll'))
const latestNewsLabel = computed(() => localizedString('hero_latest_news_label') || t('theme.janari.common.latestNews'))
const heroCtaPrimaryUrl = computed(() => {
    const raw = getSetting('hero_cta_primary_url', '/solusi')
    return typeof raw === 'string' && raw.trim() ? raw.trim() : '/solusi'
})
const heroCtaPricingUrl = computed(() => {
    const raw = getSetting('cta_secondary_url', '/pricing')
    return typeof raw === 'string' && raw.trim() ? raw.trim() : '/pricing'
})
const heroSlideCount = computed(() => parseInt(String(getSetting('hero_slide_count', 3)), 10))
const heroSlideInterval = computed(() => parseInt(String(getSetting('hero_slide_interval', 6)), 10) * 1000)
const heroOverlay = computed(() => parseInt(String(getSetting('hero_overlay_opacity', 50)), 10))
const heroHeight = computed(() => (getSetting('hero_height') as string) || '85vh')
const heroAlignment = computed(() => (getSetting('hero_alignment') as string) || 'items-center text-center')
const heroShowScroll = computed(() => getSetting('hero_show_scroll', true))
const heroNewsCategory = computed(() => (getSetting('hero_news_category') as string)?.trim() || '');
const initialHeroSlide = computed(() => {
    const configured = (getSetting('hero_slide_1') as string) || '';
    return typeof configured === 'string' && configured.trim().length > 0 ? configured.trim() : null;
});

useHead(computed(() => ({
    link: initialHeroSlide.value
        ? [
            {
                rel: 'preload',
                as: 'image',
                href: initialHeroSlide.value,
                fetchpriority: 'high',
            },
        ]
        : [],
})));

const heroSlides = ref<string[]>([])
const activeSlide = ref(0)
let slideTimer: any = null

const unifiedNews = ref<any[]>([])
const visibleNews = computed(() => unifiedNews.value.slice(0, 6))
const currentNewsIndex = ref(0)
let newsTimer: any = null

const nextNews = () => { if (visibleNews.value.length) currentNewsIndex.value = (currentNewsIndex.value + 1) % visibleNews.value.length }
const prevNews = () => { if (visibleNews.value.length) currentNewsIndex.value = (currentNewsIndex.value - 1 + visibleNews.value.length) % visibleNews.value.length }
const primaryIndex = computed(() => {
    if (!visibleNews.value || visibleNews.value.length === 0) return 0;
    return (currentNewsIndex.value % visibleNews.value.length);
});
const secondaryIndex = computed(() => visibleNews.value.length > 0 ? ((currentNewsIndex.value + 1) % visibleNews.value.length) : 0)
const tertiaryIndex = computed(() => visibleNews.value.length > 0 ? ((currentNewsIndex.value + 2) % visibleNews.value.length) : 0)

const sanitizeImageUrl = (url: string | null | undefined) => {
    if (!url) return undefined
    if (url.includes('unsplash.com') || url.includes('placehold.co')) return undefined
    return url
}

const buildFallbackNews = () => [] as Array<{
    id: string
    title: string
    category: string
    date: string
    url: string
    image?: string
}>

const mapPostsToNews = (posts: any[]) =>
    posts.map((item: any, idx: number) => ({
        id: item.id || idx + 1,
        image: sanitizeImageUrl(item.thumbnail || item.featured_image || item._raw?.thumbnail || item._raw?.featured_image),
        title: item.title || t('theme.janari.common.defaultNewsTitle'),
        category: item.category?.name || item._raw?.category?.name || t('theme.janari.common.defaultCategory'),
        date: (item.published_at || item._raw?.published_at)
            ? new Date(item.published_at || item._raw.published_at).toISOString().split('T')[0]
            : new Date().toISOString().split('T')[0],
        url: `/blog/${item.slug || item._raw?.slug || idx}`,
    }))

const updateStableData = async () => {
    const bound = Array.isArray(dynamicHeroNews.value) ? dynamicHeroNews.value : []
    // Only trust hero.news binding when it actually returns posts (misconfigured api_pages → blog page title "Berita")
    const boundPosts = bound.filter((item: any) => {
        const type = String(item?.type || item?._raw?.type || '').toLowerCase()
        if (type === 'page') return false
        if (type === 'post') return true
        // Mapped binding items often omit type; keep rows that look like articles
        return Boolean(item?.title || item?._raw?.title) && Boolean(item?.slug || item?._raw?.slug || item?.published_at || item?._raw?.published_at)
    })

    if (hasHeroNewsBinding.value && boundPosts.length > 0) {
        unifiedNews.value = mapPostsToNews(boundPosts.slice(0, 10))
    } else if (unifiedNews.value.length === 0 || bound.length > 0) {
        try {
            const params: any = { type: 'post', status: 'published', sort: '-published_at', per_page: 10 }
            if (heroNewsCategory.value) params.category = heroNewsCategory.value
            const res = await api.get(publishingPaths.publicContents, { params });
            const rawData = res.data || [];
            const posts = Array.isArray(rawData) ? rawData : (rawData?.data || []);
            if (posts.length > 0) {
                unifiedNews.value = mapPostsToNews(posts)
            } else {
                unifiedNews.value = buildFallbackNews()
            }
        } catch (e: any) {
            if (e.name === 'CanceledError' || e.code === 'ERR_CANCELED' || e.message?.includes('aborted')) return;
            console.error('[Hero] News Fallback Error:', e);
            if (unifiedNews.value.length === 0) {
                unifiedNews.value = buildFallbackNews()
            }
        }
    }

    if (unifiedNews.value.length === 0) {
        unifiedNews.value = buildFallbackNews()
    }

    // 2. Sync Hero Slides
    const slides: string[] = []
    const count = heroSlideCount.value > 0 ? heroSlideCount.value : 1
    for (let i = 1; i <= count; i++) {
        const img = getSetting(`hero_slide_${i}`) as string
        slides.push(img || '')
    }
    heroSlides.value = slides
}

const goToSlide = (idx: number) => {
    if (!slideElements.value?.length) return
    const prevEl = slideElements.value[activeSlide.value]
    const nextEl = slideElements.value[idx]
    if (!prevEl || !nextEl) return
    motion.to(prevEl, { opacity: 0, duration: 1.2 })
    motion.fromTo(nextEl, { opacity: 0 }, { opacity: 1, duration: 1.2 })
    activeSlide.value = idx
    resetSlideTimer()
}

const nextSlide = () => { if (heroSlides.value.length) goToSlide((activeSlide.value + 1) % heroSlides.value.length) }
const resetSlideTimer = () => { if (slideTimer) clearInterval(slideTimer); slideTimer = window.setInterval(nextSlide, heroSlideInterval.value) }

const initAnimations = () => {
    if (heroBadge.value) {
        const heroTl = createTimeline({ defaults: { ease: 'power3.out' } })
        heroTl.from(heroBadge.value, { y: 20, opacity: 0, duration: 0.8 }, 0.2)
        if (heroTitleRef.value) splitTextRevealSafe(heroTitleRef.value, { delay: 0.4, stagger: 0.06 })
    }
}

watch(dynamicHeroNews, () => { if (dynamicHeroNews.value?.length) updateStableData(); }, { deep: true });

onMounted(async () => {
    updateStableData();
    await nextTick();
    setTimeout(initAnimations, 300);
    newsTimer = window.setInterval(nextNews, 4000);
    resetSlideTimer();
})

onBeforeUnmount(() => {
    if (newsTimer) clearInterval(newsTimer)
    if (slideTimer) clearInterval(slideTimer)
})
</script>

<style scoped>
.text-sharp { -webkit-font-smoothing: antialiased; text-rendering: optimizeLegibility; }
@keyframes scroll-line { 0% { transform: translateY(-100%); } 100% { transform: translateY(200%); } }
.animate-scroll-line { animation: scroll-line 2s infinite; }
</style>

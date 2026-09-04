<template>
  <section
    data-ja-customizer-target="hero"
    class="sarangenge-hero relative py-20 sm:py-28 lg:py-32 px-4 sm:px-6 lg:px-8"
    :class="heroBgClass"
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

    <div class="relative z-10 max-w-7xl mx-auto w-full flex flex-col justify-center">
      <!-- Eyebrow Badge (Sunflower Dawn) -->
      <div
        ref="heroBadgeRef"
        class="sarangenge-rise mb-5"
      >
        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold bg-amber-400/20 text-amber-300 border border-amber-400/35 shadow-sm backdrop-blur-md">
          <SunMedium class="w-3.5 h-3.5 text-amber-300" />
          {{ heroBadge }}
        </span>
      </div>

      <!-- Main Title -->
      <h1
        ref="heroTitleRef"
        class="sarangenge-rise sarangenge-rise-delay-1 max-w-4xl text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold leading-[1.12] text-white tracking-tight font-heading"
      >
        {{ heroTitle }}
      </h1>

      <!-- Subtitle -->
      <p
        ref="heroSubtitleRef"
        class="sarangenge-rise sarangenge-rise-delay-2 mt-6 max-w-2xl text-base sm:text-lg text-slate-200 leading-relaxed"
      >
        {{ heroSubtitle }}
      </p>

      <!-- Action Buttons -->
      <div
        ref="heroCtaRef"
        class="sarangenge-rise sarangenge-rise-delay-3 mt-8 sm:mt-10 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5"
      >
        <a
          v-if="isExternalPrimary"
          :href="heroPrimaryLink"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-[var(--sarangenge-radius-sm,0.85rem)] bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold shadow-xl shadow-amber-500/25 transition-all duration-200 text-base"
        >
          <GraduationCap class="w-5 h-5 mr-1" />
          {{ heroPrimaryText }}
        </a>

        <Button
          v-else
          as="router-link"
          :to="heroPrimaryLink"
          variant="primary"
          size="lg"
          class="!bg-amber-500 hover:!bg-amber-400 !text-slate-950 font-bold shadow-xl shadow-amber-500/25 border-none"
        >
          <GraduationCap class="w-5 h-5 mr-1" />
          {{ heroPrimaryText }}
        </Button>

        <Button
          as="router-link"
          :to="heroSecondaryLink"
          variant="outline"
          size="lg"
          class="!border-white/25 !bg-white/10 !text-white hover:!bg-white/20 backdrop-blur-md font-semibold"
        >
          {{ heroSecondaryText }}
          <ArrowRight class="w-4 h-4 ml-1" />
        </Button>
      </div>

      <!-- Animated Scroll Indicator -->
      <div
        v-if="heroShowScroll"
        class="hidden lg:flex items-center justify-center gap-3 mt-10 text-xs font-semibold text-amber-200/80 uppercase tracking-widest font-mono"
      >
        <div class="w-8 h-[1px] bg-amber-400/30 relative overflow-hidden">
          <div class="absolute inset-0 bg-amber-400 animate-scroll-line" />
        </div>
        <span>{{ scrollCueText }}</span>
        <div class="w-8 h-[1px] bg-amber-400/30 relative overflow-hidden">
          <div class="absolute inset-0 bg-amber-400 animate-scroll-line" />
        </div>
      </div>

      <!-- Stats Bar (Scholastic Dawn Metrics) -->
      <div
        v-if="heroBottomMode === 'stats' || heroBottomMode === 'both'"
        ref="heroStatsRef"
        data-ja-customizer-target="hero-stats"
        class="sarangenge-rise sarangenge-rise-delay-3 mt-12 pt-8 border-t border-white/15 grid grid-cols-2 md:grid-cols-4 gap-6 text-white"
      >
        <div class="space-y-1">
          <div class="text-2xl sm:text-3xl font-extrabold font-heading text-amber-400">
            {{ stat1Val }}
          </div>
          <div class="text-xs sm:text-sm text-slate-300 font-medium">
            {{ stat1Label }}
          </div>
        </div>

        <div class="space-y-1">
          <div class="text-2xl sm:text-3xl font-extrabold font-heading text-amber-400">
            {{ stat2Val }}
          </div>
          <div class="text-xs sm:text-sm text-slate-300 font-medium">
            {{ stat2Label }}
          </div>
        </div>

        <div class="space-y-1">
          <div class="text-2xl sm:text-3xl font-extrabold font-heading text-amber-400">
            {{ stat3Val }}
          </div>
          <div class="text-xs sm:text-sm text-slate-300 font-medium">
            {{ stat3Label }}
          </div>
        </div>

        <div class="space-y-1">
          <div class="text-2xl sm:text-3xl font-extrabold font-heading text-emerald-400">
            {{ stat4Val }}
          </div>
          <div class="text-xs sm:text-sm text-slate-300 font-medium">
            {{ stat4Label }}
          </div>
        </div>
      </div>

      <!-- School News & Agenda / PPDB Updates Ticker -->
      <div
        v-if="heroBottomMode === 'news' || heroBottomMode === 'both'"
        ref="heroNewsRef"
        data-ja-customizer-target="hero-news"
        class="sarangenge-rise sarangenge-rise-delay-3 mt-10 pt-8 border-t border-white/15 space-y-4"
      >
        <div class="flex items-center justify-between gap-3">
          <span class="text-xs font-bold uppercase tracking-wider text-amber-300/90 font-mono flex items-center gap-2">
            <Sparkles class="w-3.5 h-3.5 text-amber-400" />
            {{ newsSectionLabel }}
          </span>
          <router-link
            to="/blog"
            class="text-xs font-semibold text-amber-400 hover:text-amber-300 transition-colors inline-flex items-center gap-1"
          >
            {{ t('pages.home.viewAllNews', 'Semua Warta Sekolah') }}
            <ArrowUpRight class="w-3.5 h-3.5" />
          </router-link>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <router-link
            v-for="item in visibleSchoolNews"
            :key="item.id"
            :to="item.url"
            class="group flex flex-col justify-between rounded-xl border border-white/15 bg-white/5 hover:bg-white/10 hover:border-amber-400/40 p-4 transition-all duration-300 backdrop-blur-sm"
          >
            <div class="space-y-2">
              <div class="flex items-center justify-between gap-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-300 px-2 py-0.5 rounded-full bg-amber-400/10 border border-amber-400/20">
                  {{ item.category }}
                </span>
                <ArrowUpRight class="w-3.5 h-3.5 text-white/40 group-hover:text-amber-300 transition-colors" />
              </div>
              <h3 class="text-sm font-semibold text-white group-hover:text-amber-200 transition-colors line-clamp-2 leading-snug">
                {{ item.title }}
              </h3>
            </div>
            <span v-if="item.date" class="text-[11px] text-slate-300/70 mt-3 block font-mono">
              {{ item.date }}
            </span>
          </router-link>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { SunMedium, GraduationCap, ArrowRight, ArrowUpRight, Sparkles } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeMotion } from '@/modules/Layout/composables/useThemeMotion';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import { useThemeDataBindings } from '@/modules/Layout/composables/useThemeDataBindings';
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

const heroOverlayFinalOpacity = computed(() => {
  if (heroBgType.value === 'custom_image') {
    return heroBgOverlayOpacity.value;
  }
  return 0.35;
});

const heroBadge = computed(() => {
  return (getSetting('hero_badge_text', '') as string) || t('pages.home.heroBadge', 'Penerimaan Peserta Didik Baru (PPDB) 2026/2027 Dibuka');
});

const heroTitle = computed(() => {
  return (getSetting('hero_title', '') as string) || t('pages.home.heroTitle', 'Mekar Bersama Cahaya Pagi — Membentuk Generasi Cerdas & Berkarakter.');
});

const heroSubtitle = computed(() => {
  return (getSetting('hero_subtitle', '') as string) || t('pages.home.heroSubtitle', 'Situs resmi sekolah: pendidikan vokasi unggulan berstandar industri dengan bengkel modern dan penyiapan karir masa depan.');
});

const heroPrimaryText = computed(() => {
  return (getSetting('hero_primary_cta_text', '') as string) || t('pages.home.heroCta', 'Daftar PPDB Jabar (Resmi)');
});

const heroPrimaryLink = computed(() => {
  return (getSetting('hero_primary_cta_link', '') as string) || ppdbPortalUrl.value;
});

const isExternalPrimary = computed(() => {
  return typeof heroPrimaryLink.value === 'string' && (heroPrimaryLink.value.startsWith('http://') || heroPrimaryLink.value.startsWith('https://'));
});

const heroSecondaryText = computed(() => {
  return (getSetting('hero_secondary_cta_text', '') as string) || t('pages.home.heroSecondary', 'Program Keahlian');
});

const heroSecondaryLink = computed(() => {
  return (getSetting('hero_secondary_cta_link', '') as string) || '/programs';
});

// Bottom Mode & Scroll Indicators
const heroBottomMode = computed(() => String(getSetting('hero_bottom_mode', 'stats') || 'stats'));
const heroShowScroll = computed(() => getSetting('hero_show_scroll', true) !== false);
const scrollCueText = computed(() => t('pages.home.scrollCue', 'JELAJAHI SMKN 6 BANDUNG'));
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
const { data: dynamicHeroNews, hasBinding: hasHeroNewsBinding } = useThemeDataBindings('hero', 'news');

const defaultSchoolNews = computed(() => [
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

const fetchedPosts = ref<Array<{ id: string | number; title: string; category: string; date: string; url: string }>>([]);

const schoolNewsList = computed(() => {
  if (hasHeroNewsBinding.value && Array.isArray(dynamicHeroNews.value) && dynamicHeroNews.value.length > 0) {
    return (dynamicHeroNews.value as Record<string, unknown>[]).map((item, idx) => ({
      id: String(item.id || idx),
      title: String(item.title || item.name || ''),
      category: String(item.category || item.badge || 'Warta'),
      date: String(item.date || item.created_at || ''),
      url: String(item.url || (item.slug ? `/blog/${item.slug}` : '/blog')),
    }));
  }
  if (fetchedPosts.value.length > 0) {
    return fetchedPosts.value;
  }
  return defaultSchoolNews.value;
});

const visibleSchoolNews = computed(() => schoolNewsList.value.slice(0, 4));

onMounted(async () => {
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
      params: { type: 'post', per_page: 4, status: 'published', sort: '-published_at' },
    });
    const posts = res?.data?.data || res?.data;
    if (Array.isArray(posts) && posts.length > 0) {
      fetchedPosts.value = posts.slice(0, 4).map((p: any) => ({
        id: p.id,
        title: p.title,
        category: p.category?.name || p.category || 'Warta',
        date: p.published_at || p.created_at ? new Date(p.published_at || p.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '',
        url: `/blog/${p.slug || p.id}`,
      }));
    }
  } catch {
    // Keep fallback
  }
});
</script>

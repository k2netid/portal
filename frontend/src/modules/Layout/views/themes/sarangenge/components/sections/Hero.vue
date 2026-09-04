<template>
  <section
    data-ja-customizer-target="hero"
    class="sarangenge-hero relative py-20 sm:py-28 lg:py-32 px-4 sm:px-6 lg:px-8"
  >
    <div
      class="sarangenge-hero__media"
      aria-hidden="true"
    />

    <div class="relative z-10 max-w-7xl mx-auto w-full flex flex-col justify-center">
      <!-- Eyebrow Badge (Sunflower Dawn) -->
      <div class="sarangenge-rise mb-5">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold bg-amber-400/20 text-amber-300 border border-amber-400/35 shadow-sm backdrop-blur-md">
          <SunMedium class="w-3.5 h-3.5 text-amber-300" />
          {{ heroBadge }}
        </span>
      </div>

      <!-- Main Title -->
      <h1 class="sarangenge-rise sarangenge-rise-delay-1 max-w-4xl text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold leading-[1.12] text-white tracking-tight font-heading">
        {{ heroTitle }}
      </h1>

      <!-- Subtitle -->
      <p class="sarangenge-rise sarangenge-rise-delay-2 mt-6 max-w-2xl text-base sm:text-lg text-slate-200 leading-relaxed">
        {{ heroSubtitle }}
      </p>

      <!-- Action Buttons -->
      <div class="sarangenge-rise sarangenge-rise-delay-3 mt-8 sm:mt-10 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
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

      <!-- Stats Bar (Scholastic Dawn Metrics) -->
      <div class="sarangenge-rise sarangenge-rise-delay-3 mt-14 pt-8 border-t border-white/15 grid grid-cols-2 md:grid-cols-4 gap-6 text-white">
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
            {{ displayAccreditation }}
          </div>
          <div class="text-xs sm:text-sm text-slate-300 font-medium">
            Akreditasi BAN-S/M
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { SunMedium, GraduationCap, ArrowRight } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';

const { t } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();
const { displayAccreditation, ppdbPortalUrl } = useSarangengeIdentity();

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

const stat1Val = computed(() => (getSetting('hero_stat_1_val', '100%') as string) || '100%');
const stat1Label = computed(() => (getSetting('hero_stat_1_label', 'Keterserapan DUDI & Kuliah') as string) || 'Keterserapan DUDI & Kuliah');
const stat2Val = computed(() => (getSetting('hero_stat_2_val', '6') as string) || '6');
const stat2Label = computed(() => (getSetting('hero_stat_2_label', 'Program Keahlian Vokasi') as string) || 'Program Keahlian Vokasi');
const stat3Val = computed(() => (getSetting('hero_stat_3_val', '1:12') as string) || '1:12');
const stat3Label = computed(() => (getSetting('hero_stat_3_label', 'Rasio Guru & Siswa') as string) || 'Rasio Guru & Siswa');
</script>

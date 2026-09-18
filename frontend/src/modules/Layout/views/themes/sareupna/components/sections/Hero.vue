<template>
  <section
    ref="heroSectionRef"
    class="relative min-h-[90vh] md:min-h-[95vh] flex flex-col justify-center items-center pt-28 md:pt-36 pb-20 px-4 md:px-8 overflow-hidden z-10"
  >
    <!-- Cyber Cosmic Ambient Glows -->
    <div class="sareupna-ambient-mesh sareupna-pulse-glow w-[550px] h-[550px] bg-primary/30 top-1/4 -left-32 -translate-y-1/2" />
    <div class="sareupna-ambient-mesh sareupna-pulse-glow w-[600px] h-[600px] bg-secondary/25 top-1/3 -right-36 -translate-y-1/2" style="animation-delay: -4s;" />
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(139,92,246,0.15),rgba(255,255,255,0))] pointer-events-none" />

    <!-- Grid lines texture -->
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#8882_1px,transparent_1px),linear-gradient(to_bottom,#8882_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_40%,#000_70%,transparent_100%)] opacity-30 pointer-events-none" />

    <div class="container mx-auto max-w-5xl relative z-20 flex flex-col items-center text-center">
      <!-- Interactive CLI Command Badge -->
      <div
        class="group/badge inline-flex items-center gap-2.5 px-4 py-2 rounded-full border border-primary/40 bg-primary/10 hover:bg-primary/20 hover:border-primary/70 text-foreground transition-all duration-300 backdrop-blur-xl mb-6 shadow-sm shadow-primary/15 cursor-pointer select-none"
        :title="badgeCopied ? t('common.copied') : t('common.clickToCopy')"
        @click="copyBadgeCommand"
      >
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-80" />
          <span class="relative inline-flex rounded-full h-2 w-2 bg-primary" />
        </span>
        <span class="text-xs font-mono tracking-wider uppercase text-foreground/90 font-semibold">
          {{ heroBadgeText }}
        </span>
        <span class="sareupna-typed-cursor" />
        <span
          v-if="badgeCopied"
          class="text-[10px] font-mono text-primary font-bold tracking-wider uppercase ml-1 animate-pulse"
        >
          {{ t('common.copied') }}
        </span>
      </div>

      <!-- Hero Heading with High Impact Fluid Clamp -->
      <h1
        class="text-4xl sm:text-6xl md:text-7xl font-heading font-black tracking-tight text-foreground uppercase max-w-4xl mb-6 leading-[1.05]"
      >
        <span class="bg-clip-text text-transparent bg-gradient-to-b from-foreground via-foreground to-foreground/60">
          {{ heroTitle }}
        </span>
      </h1>

      <!-- Hero Subtitle -->
      <p
        class="text-base sm:text-lg md:text-xl text-muted-foreground font-normal max-w-2xl mb-10 leading-relaxed"
      >
        {{ heroSubtitle }}
      </p>

      <!-- Action Buttons (CTA) -->
      <div
        class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto mb-16"
      >
        <router-link
          :to="heroCtaPrimaryUrl"
          class="w-full sm:w-auto px-8 py-3.5 text-xs font-bold uppercase tracking-wider rounded-xl bg-gradient-to-r from-primary to-primary/90 text-primary-foreground shadow-lg shadow-primary/30 hover:shadow-primary/50 hover:scale-[1.02] active:scale-95 transition-all duration-200 inline-flex items-center justify-center gap-2"
        >
          <span>{{ heroCtaPrimaryText }}</span>
          <ArrowRight class="w-4 h-4" />
        </router-link>

        <router-link
          :to="heroCtaSecondaryUrl"
          class="w-full sm:w-auto px-8 py-3.5 text-xs font-bold uppercase tracking-wider rounded-xl border border-border/80 bg-card/60 hover:bg-card hover:border-primary/50 text-foreground shadow-sm hover:scale-[1.02] active:scale-95 transition-all duration-200 inline-flex items-center justify-center gap-2"
        >
          <Terminal class="w-4 h-4 text-primary" />
          <span>{{ heroCtaSecondaryText }}</span>
        </router-link>
      </div>

      <!-- Live Telemetry / Metrics Bar -->
      <div
        class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8 w-full max-w-4xl p-6 rounded-2xl bg-card/40 border border-border/50 backdrop-blur-xl"
      >
        <div class="flex flex-col items-center">
          <span class="text-2xl md:text-3xl font-heading font-black tracking-tight text-primary">
            {{ stat1Val }}
          </span>
          <span class="text-[11px] font-mono uppercase tracking-wider text-muted-foreground mt-1">
            {{ stat1Label }}
          </span>
        </div>

        <div class="flex flex-col items-center">
          <span class="text-2xl md:text-3xl font-heading font-black tracking-tight text-secondary">
            {{ stat2Val }}
          </span>
          <span class="text-[11px] font-mono uppercase tracking-wider text-muted-foreground mt-1">
            {{ stat2Label }}
          </span>
        </div>

        <div class="flex flex-col items-center">
          <span class="text-2xl md:text-3xl font-heading font-black tracking-tight text-foreground">
            {{ stat3Val }}
          </span>
          <span class="text-[11px] font-mono uppercase tracking-wider text-muted-foreground mt-1">
            {{ stat3Label }}
          </span>
        </div>

        <div class="flex flex-col items-center">
          <span class="text-2xl md:text-3xl font-heading font-black tracking-tight text-emerald-400">
            {{ stat4Val }}
          </span>
          <span class="text-[11px] font-mono uppercase tracking-wider text-muted-foreground mt-1">
            {{ stat4Label }}
          </span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { ArrowRight, Terminal } from 'lucide-vue-next';

const { getSetting } = useTheme();
const { localizedString } = useLocalizedThemeSetting();
const { t } = useThemeI18n('sareupna');

const heroBadgeText = computed(() => localizedString('hero_badge_text') || localizedString('hero_badge') || t('hero.badgeDefault'));
const heroTitle = computed(() => localizedString('hero_title') || t('hero.brandFallback'));
const heroSubtitle = computed(() => localizedString('hero_subtitle') || t('hero.subtitleDefault'));

const heroCtaPrimaryText = computed(() => localizedString('hero_primary_cta_text') || localizedString('hero_cta_primary') || t('hero.ctaPrimary'));
const heroCtaPrimaryUrl = computed(() => String(getSetting('hero_primary_cta_link') || getSetting('hero_cta_primary_url', '/solusi') || '/solusi'));

const heroCtaSecondaryText = computed(() => localizedString('hero_secondary_cta_text') || localizedString('cta_secondary_text') || t('hero.ctaPricing'));
const heroCtaSecondaryUrl = computed(() => String(getSetting('hero_secondary_cta_link') || getSetting('cta_secondary_url', '/pricing') || '/pricing'));

const stat1Val = computed(() => String(getSetting('hero_stat_1_val') || t('hero.stat1Val')));
const stat1Label = computed(() => String(getSetting('hero_stat_1_label') || t('hero.stat1Label')));
const stat2Val = computed(() => String(getSetting('hero_stat_2_val') || t('hero.stat2Val')));
const stat2Label = computed(() => String(getSetting('hero_stat_2_label') || t('hero.stat2Label')));
const stat3Val = computed(() => String(getSetting('hero_stat_3_val') || t('hero.stat3Val')));
const stat3Label = computed(() => String(getSetting('hero_stat_3_label') || t('hero.stat3Label')));
const stat4Val = computed(() => String(getSetting('hero_stat_4_val') || t('hero.stat4Val')));
const stat4Label = computed(() => String(getSetting('hero_stat_4_label') || t('hero.stat4Label')));

const badgeCopied = ref(false);

const copyBadgeCommand = async () => {
  try {
    let text = heroBadgeText.value.trim();
    if (text.startsWith('$')) {
      text = text.slice(1).trim();
    }
    if (typeof navigator !== 'undefined' && navigator.clipboard?.writeText) {
      await navigator.clipboard.writeText(text);
      badgeCopied.value = true;
      setTimeout(() => {
        badgeCopied.value = false;
      }, 2000);
    }
  } catch {
    // Fail silently
  }
};
</script>

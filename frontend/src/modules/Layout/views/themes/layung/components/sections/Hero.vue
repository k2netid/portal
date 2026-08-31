<template>
  <section
    data-ja-customizer-target="hero"
    class="layung-hero px-4 sm:px-6 lg:px-8 py-20 lg:py-28 relative overflow-hidden"
  >
    <div class="layung-hero__grid" />
    
    <div class="max-w-7xl mx-auto w-full relative z-10 space-y-12">
      <!-- Top Badge -->
      <div class="flex flex-wrap items-center gap-3">
        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-orange-500/20 text-orange-400 border border-orange-500/40 shadow-inner">
          <span class="layung-status-dot" />
          {{ heroBadgeText }}
        </span>
        <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">
          <Zap class="w-3.5 h-3.5 text-cyan-400" />
          {{ displayAsn }}
        </span>
      </div>

      <!-- Main Headline & Subheadline -->
      <div class="max-w-4xl space-y-6">
        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-white font-heading leading-[1.08]">
          {{ heroTitle }}
        </h1>
        <p class="text-base sm:text-xl text-slate-300 max-w-3xl leading-relaxed">
          {{ heroSubtitle }}
        </p>
      </div>

      <!-- Quick Coverage Search & CTA -->
      <div class="max-w-2xl bg-slate-900/90 backdrop-blur-xl border border-slate-700/80 p-2 sm:p-2.5 rounded-2xl shadow-2xl shadow-black/60 flex flex-col sm:flex-row gap-2.5">
        <div class="relative flex-1 flex items-center">
          <MapPin class="w-5 h-5 text-orange-400 absolute left-3.5 pointer-events-none" />
          <input
            v-model="searchCity"
            type="text"
            placeholder="Ketik nama gedung / kota Anda (misal: Jakarta Selatan)..."
            class="w-full bg-slate-950 border border-slate-800 text-white placeholder:text-slate-500 text-sm pl-11 pr-4 py-3 rounded-xl focus:outline-none focus:border-orange-500 transition-colors"
            @keyup.enter="handleCoverageCheck"
          >
        </div>
        <Button
          variant="primary"
          size="md"
          class="!py-3 !px-6 font-bold shrink-0"
          @click="handleCoverageCheck"
        >
          <Search class="w-4 h-4 mr-1.5" />
          {{ heroPrimaryCtaText }}
        </Button>
      </div>

      <!-- Coverage Result Notification -->
      <div
        v-if="coverageResult"
        class="max-w-2xl p-4 rounded-xl text-sm font-medium border flex items-center justify-between animate-in fade-in slide-in-from-top-2 duration-300"
        :class="coverageResult.available ? 'bg-emerald-950/80 text-emerald-300 border-emerald-500/40' : 'bg-amber-950/80 text-amber-300 border-amber-500/40'"
      >
        <div class="flex items-center gap-2.5">
          <CheckCircle2
            v-if="coverageResult.available"
            class="w-5 h-5 text-emerald-400 shrink-0"
          />
          <AlertCircle
            v-else
            class="w-5 h-5 text-amber-400 shrink-0"
          />
          <span>{{ coverageResult.message }}</span>
        </div>
        <button
          type="button"
          class="text-xs underline hover:opacity-80"
          @click="coverageResult = null"
        >
          Tutup
        </button>
      </div>

      <!-- Live SLA & Performance Bar -->
      <div class="pt-6 border-t border-slate-800/80 grid grid-cols-2 md:grid-cols-4 gap-6 text-slate-300 font-mono">
        <div class="space-y-1">
          <span class="text-xs text-slate-500 font-sans block">{{ t('hero.uptime', 'Jaminan Uptime SLA') }}</span>
          <strong class="text-2xl font-black text-emerald-400 font-heading">99.999%</strong>
        </div>
        <div class="space-y-1">
          <span class="text-xs text-slate-500 font-sans block">{{ t('hero.latency', 'Latensi Inti') }}</span>
          <strong class="text-2xl font-black text-cyan-400 font-heading">{{ displayNocLatency }}</strong>
        </div>
        <div class="space-y-1">
          <span class="text-xs text-slate-500 font-sans block">{{ t('hero.backbone', 'Kapasitas Backbone') }}</span>
          <strong class="text-2xl font-black text-orange-400 font-heading">{{ displayBackboneCapacity }}</strong>
        </div>
        <div class="space-y-1">
          <span class="text-xs text-slate-500 font-sans block">{{ t('hero.support', 'Dukungan NOC 24/7') }}</span>
          <strong class="text-2xl font-black text-white font-heading">15 Mnt MTTR</strong>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Zap, MapPin, Search, CheckCircle2, AlertCircle } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/layung/ui';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useLayungIdentity } from '@/modules/Layout/views/themes/layung/composables/useLayungIdentity';

const { t } = useThemeI18n('layung');
const { getSetting } = useTheme();
const {
  displayAsn,
  displayNocLatency,
  displayBackboneCapacity,
  coverageCities,
} = useLayungIdentity();

const searchCity = ref('');
const coverageResult = ref<{ available: boolean; message: string } | null>(null);

const heroBadgeText = computed(() => {
  const custom = getSetting('hero_badge_text', '');
  if (custom && typeof custom === 'string' && custom.trim() !== '') return custom;
  return t('hero.badge', 'Infrastruktur Serat Optik & Managed Services 2026');
});

const heroTitle = computed(() => {
  const custom = getSetting('hero_title', '');
  if (custom && typeof custom === 'string' && custom.trim() !== '') return custom;
  return t('hero.headline', 'Konektivitas Fiber Ultra Cepat & Solusi IT Terkelola untuk Bisnis Skala Global');
});

const heroSubtitle = computed(() => {
  const custom = getSetting('hero_subtitle', '');
  if (custom && typeof custom === 'string' && custom.trim() !== '') return custom;
  return t('hero.subheadline', 'Hadirkan backbone internet berkecepatan cahaya, latensi super rendah di bawah 3ms, multi-cloud interconnect, dan perlindungan Cyber SOC 24/7.');
});

const heroPrimaryCtaText = computed(() => {
  const custom = getSetting('hero_primary_cta_text', '');
  if (custom && typeof custom === 'string' && custom.trim() !== '') return custom;
  return t('hero.ctaPrimary', 'Cek Area Jangkauan');
});

const handleCoverageCheck = () => {
  const query = searchCity.value.trim().toLowerCase();
  if (!query) return;

  const isCovered = coverageCities.value.some((c) => query.includes(c.toLowerCase()));
  if (isCovered || query.length > 2) {
    coverageResult.value = {
      available: true,
      message: `Jaringan Fiber Optik Layung TERSEDIA di area "${searchCity.value}". Tim sales siap melakukan survei lokasi & uji sinyal!`,
    };
  } else {
    coverageResult.value = {
      available: false,
      message: `Area "${searchCity.value}" sedang dalam proses ekspansi jalur backbone. Hubungi sales untuk pemetaan fiber khusus.`,
    };
  }
};
</script>

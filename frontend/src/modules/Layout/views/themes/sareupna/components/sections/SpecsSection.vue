<template>
  <section
    id="sareupna-specs"
    class="sareupna-section relative py-20 md:py-28 overflow-hidden bg-background border-t border-border/40"
    data-ja-customizer-target="specs"
  >
    <!-- Background Ambient Glow -->
    <div class="sareupna-ambient-mesh w-96 h-96 bg-primary/10 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2" />

    <div class="container mx-auto px-4 md:px-8 relative z-10">
      <!-- Section Header -->
      <div class="text-center max-w-3xl mx-auto mb-16">
        <span class="text-xs font-mono uppercase tracking-widest text-primary font-bold mb-3 block">
          {{ t('specs.badge', 'SPESIFIKASI INFRASTRUKTUR') }}
        </span>
        <h2 class="text-3xl md:text-5xl font-heading font-black tracking-tight text-foreground mb-6 uppercase">
          {{ specsTitle }}
        </h2>
        <p class="text-sm md:text-base text-muted-foreground leading-relaxed">
          {{ specsSubtitle }}
        </p>
      </div>

      <!-- Specs Telemetry Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: SLA -->
        <div class="p-6 rounded-2xl bg-card/60 border border-border/60 hover:border-primary/50 transition-all group backdrop-blur-sm">
          <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <ShieldCheck class="w-5 h-5" />
          </div>
          <div class="text-3xl font-black font-heading text-foreground mb-1 tracking-tight">
            {{ getSetting('platform_sla_guarantee', '99.99%') }}
          </div>
          <h3 class="text-sm font-bold text-foreground mb-2">
            {{ t('specs.card1Title', 'High Availability SLA') }}
          </h3>
          <p class="text-xs text-muted-foreground leading-relaxed">
            {{ t('specs.card1Desc', 'Jaminan uptime tanpa kompromi didukung arsitektur multi-region failover.') }}
          </p>
        </div>

        <!-- Card 2: Latency -->
        <div class="p-6 rounded-2xl bg-card/60 border border-border/60 hover:border-secondary/50 transition-all group backdrop-blur-sm">
          <div class="w-10 h-10 rounded-xl bg-secondary/10 text-secondary flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <Activity class="w-5 h-5" />
          </div>
          <div class="text-3xl font-black font-heading text-foreground mb-1 tracking-tight">
            {{ getSetting('platform_core_latency', '< 15ms') }}
          </div>
          <h3 class="text-sm font-bold text-foreground mb-2">
            {{ t('specs.card2Title', 'Edge Computing Latency') }}
          </h3>
          <p class="text-xs text-muted-foreground leading-relaxed">
            {{ t('specs.card2Desc', 'Titik kehadiran server edge di pusat pertukaran data utama Indonesia (Equinix & APJII).') }}
          </p>
        </div>

        <!-- Card 3: Storage Fabric -->
        <div class="p-6 rounded-2xl bg-card/60 border border-border/60 hover:border-primary/50 transition-all group backdrop-blur-sm">
          <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <Server class="w-5 h-5" />
          </div>
          <div class="text-3xl font-black font-heading text-foreground mb-1 tracking-tight">
            100% NVMe
          </div>
          <h3 class="text-sm font-bold text-foreground mb-2">
            {{ t('specs.card3Title', 'Distributed Storage Fabric') }}
          </h3>
          <p class="text-xs text-muted-foreground leading-relaxed">
            {{ t('specs.card3Desc', 'Storage tier ultra-cepat dengan replikasi tiga lapis (triplicate redundancy).') }}
          </p>
        </div>

        <!-- Card 4: SRE Support -->
        <div class="p-6 rounded-2xl bg-card/60 border border-border/60 hover:border-secondary/50 transition-all group backdrop-blur-sm">
          <div class="w-10 h-10 rounded-xl bg-secondary/10 text-secondary flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <Cpu class="w-5 h-5" />
          </div>
          <div class="text-3xl font-black font-heading text-foreground mb-1 tracking-tight">
            {{ getSetting('platform_support_tier', '24/7/365') }}
          </div>
          <h3 class="text-sm font-bold text-foreground mb-2">
            {{ t('specs.card4Title', 'Dedicated DevOps & SRE') }}
          </h3>
          <p class="text-xs text-muted-foreground leading-relaxed">
            {{ t('specs.card4Desc', 'Monitoring proaktif dan tanggap darurat teknis kapan pun dibutuhkan.') }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { ShieldCheck, Activity, Server, Cpu } from 'lucide-vue-next';

const { getSetting } = useTheme();
const { t } = useThemeI18n('sareupna');

const specsTitle = computed(() => {
  return String(getSetting('specs_title', '')).trim() || t('specs.title', 'Keandalan Infrastruktur Tingkat Enterprise');
});

const specsSubtitle = computed(() => {
  return String(getSetting('specs_subtitle', '')).trim() || t('specs.subtitle', 'Arsitektur cloud dengan jaminan ketersediaan tinggi, latensi ultra rendah, dan perlindungan multi-region.');
});
</script>

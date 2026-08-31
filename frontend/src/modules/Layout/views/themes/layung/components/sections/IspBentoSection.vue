<template>
  <section
    data-ja-customizer-target="bento"
    class="py-12 sm:py-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10"
  >
    <!-- Section Header -->
    <div class="text-center max-w-3xl mx-auto space-y-4">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 uppercase tracking-wider font-mono">
        {{ t('bento.badge', 'Pilar Keunggulan Infrastruktur') }}
      </span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
        {{ t('bento.title', 'Solusi Lengkap Ekosistem Telekomunikasi & Keamanan Digital') }}
      </h2>
      <p class="text-muted-foreground text-sm sm:text-base leading-relaxed">
        {{ t('bento.subtitle', 'Dirancang dari hulu ke hilir untuk memastikan operasional bisnis Anda tetap menyala tanpa henti.') }}
      </p>
    </div>

    <!-- Bento Grid -->
    <div class="layung-bento">
      <!-- Bento 1: Dedicated Internet Access (Wide) -->
      <div class="layung-bento__cell layung-bento__wide layung-bento__cell--orange space-y-6">
        <div class="space-y-3">
          <div class="w-12 h-12 rounded-xl bg-orange-500/20 text-orange-400 flex items-center justify-center">
            <Network class="w-6 h-6" />
          </div>
          <h3 class="text-2xl font-bold font-heading text-white">
            {{ t('bento.diaTitle', 'Dedicated Internet 1:1 Symmetrical') }}
          </h3>
          <p class="text-slate-300 text-sm leading-relaxed max-w-xl">
            {{ t('bento.diaDesc', 'Bandwidth simetris murni upload dan download dengan IP Publik Statis dan SLA 99.999%.') }}
          </p>
        </div>

        <div class="grid grid-cols-3 gap-4 pt-4 border-t border-white/10 font-mono text-xs text-slate-300">
          <div>
            <span class="text-slate-400 block text-[10px]">Rasio Upload/DL</span>
            <strong class="text-orange-400 text-sm">1:1 Murni</strong>
          </div>
          <div>
            <span class="text-slate-400 block text-[10px]">Alokasi IP</span>
            <strong class="text-white text-sm">/29 Statis</strong>
          </div>
          <div>
            <span class="text-slate-400 block text-[10px]">Jaminan Garansi</span>
            <strong class="text-emerald-400 text-sm">SLA 99.999%</strong>
          </div>
        </div>

        <router-link
          to="/services"
          class="inline-flex items-center gap-2 text-sm font-bold text-orange-400 hover:text-orange-300 transition-colors self-start"
        >
          <span>Pelajari Skema DIA</span>
          <ArrowRight class="w-4 h-4" />
        </router-link>
      </div>

      <!-- Bento 2: 24/7 Cyber Security SOC -->
      <div class="layung-bento__cell layung-bento__cell--cyber space-y-6">
        <div class="space-y-3">
          <div class="w-12 h-12 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center">
            <Lock class="w-6 h-6" />
          </div>
          <h3 class="text-xl font-bold font-heading text-white">
            {{ t('bento.socTitle', '24/7 Cyber Security SOC') }}
          </h3>
          <p class="text-slate-300 text-xs leading-relaxed">
            {{ t('bento.socDesc', 'Mitigasi serangan DDoS kapasitas tinggi, Next-Gen Firewall, dan pemantauan ancaman siber proaktif.') }}
          </p>
        </div>

        <div class="pt-3 border-t border-white/10 text-xs text-cyan-400 font-mono flex items-center gap-2">
          <ShieldAlert class="w-4 h-4" />
          <span>DDoS Filter 1.2 Tbps</span>
        </div>
      </div>

      <!-- Bento 3: Managed SD-WAN & Multi-Cloud -->
      <div class="layung-bento__cell layung-bento__cell--cyber space-y-6">
        <div class="space-y-3">
          <div class="w-12 h-12 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center">
            <Cloud class="w-6 h-6" />
          </div>
          <h3 class="text-xl font-bold font-heading text-white">
            {{ t('bento.sdwanTitle', 'Managed SD-WAN & Cloud') }}
          </h3>
          <p class="text-slate-300 text-xs leading-relaxed">
            {{ t('bento.sdwanDesc', 'Integrasi langsung ke AWS, Google Cloud, dan Azure dengan routing cerdas anti-gangguan.') }}
          </p>
        </div>

        <div class="pt-3 border-t border-white/10 text-xs text-slate-400 font-mono">
          <span>Multi-Link Zero Failover</span>
        </div>
      </div>

      <!-- Bento 4: Tier-3 Data Center Interconnect (Wide) -->
      <div class="layung-bento__cell layung-bento__wide space-y-6 bg-card border border-border/80">
        <div class="space-y-3">
          <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
            <Server class="w-6 h-6" />
          </div>
          <h3 class="text-2xl font-bold font-heading text-foreground">
            {{ t('bento.colocationTitle', 'Tier-3 Data Center Interconnect') }}
          </h3>
          <p class="text-muted-foreground text-sm leading-relaxed max-w-xl">
            {{ t('bento.colocationDesc', 'Fasilitas colocation server bersertifikasi ISO 27001 dengan multi-upstream redundan.') }}
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-2 text-xs font-mono">
          <span class="px-3 py-1 rounded-md bg-muted text-foreground">OpenIXP Peering</span>
          <span class="px-3 py-1 rounded-md bg-muted text-foreground">IIX APJII 100G</span>
          <span class="px-3 py-1 rounded-md bg-muted text-foreground">Equinix SG1 Direct</span>
        </div>

        <router-link
          to="/about"
          class="inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline self-start"
        >
          <span>Lihat Data Center PoP</span>
          <ArrowRight class="w-4 h-4" />
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Network, Lock, Cloud, Server, ArrowRight, ShieldAlert } from 'lucide-vue-next';

const { t } = useThemeI18n('layung');
</script>

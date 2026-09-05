<template>
  <section
    data-ja-customizer-target="packages"
    class="py-12 sm:py-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10"
  >
    <div class="text-center max-w-3xl mx-auto space-y-4">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20 uppercase tracking-wider font-mono">
        {{ t('packages.badge', 'Skema Paket & Kapasitas') }}
      </span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
        {{ t('packages.hubTitle', 'Dua Lini Produk Kami') }}
      </h2>
      <p class="text-muted-foreground text-sm sm:text-base leading-relaxed">
        {{ t('packages.hubSubtitle', 'Pilih paket konektivitas internet (ISP) atau layanan IT terkelola (MSP) sesuai kebutuhan institusi Anda.') }}
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto items-stretch">
      <div class="layung-panel p-8 space-y-4 flex flex-col border border-border hover:border-sky-500/40">
        <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400">
          <Network class="w-6 h-6" />
        </div>
        <h3 class="text-xl font-bold text-foreground font-heading">
          {{ t('packages.ispCardTitle', 'Paket Internet (ISP)') }}
        </h3>
        <p class="text-sm text-muted-foreground leading-relaxed flex-1">
          {{ t('packages.ispCardDesc', 'Dedicated Internet, Broadband Bisnis SOHO, dan Retail Broadband.') }}
        </p>
        <Button
          :as="RouterLink"
          to="/pricing/isp"
          variant="primary"
          size="md"
          class="w-full font-bold mt-auto"
        >
          <Package class="w-4 h-4" />
          {{ t('packages.viewPlans', 'Lihat paket ISP') }}
        </Button>
      </div>

      <div class="layung-panel p-8 space-y-4 flex flex-col border border-border hover:border-sky-500/40">
        <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
          <Settings2 class="w-6 h-6" />
        </div>
        <h3 class="text-xl font-bold text-foreground font-heading">
          {{ t('packages.mspCardTitle', 'Managed Services (MSP)') }}
        </h3>
        <p class="text-sm text-muted-foreground leading-relaxed flex-1">
          {{ t('packages.mspCardDesc', 'Paket Basic, Standard, dan Custom untuk sekolah dan institusi.') }}
        </p>
        <Button
          :as="RouterLink"
          to="/pricing/msp"
          variant="primary"
          size="md"
          class="w-full font-bold mt-auto"
        >
          <Package class="w-4 h-4" />
          {{ t('packages.viewPlans', 'Lihat paket MSP') }}
        </Button>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { Network, Settings2, Package } from 'lucide-vue-next';
import { RouterLink } from 'vue-router';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Button } from '@/modules/Layout/views/themes/layung/ui';

const { t } = useThemeI18n('layung');
</script>

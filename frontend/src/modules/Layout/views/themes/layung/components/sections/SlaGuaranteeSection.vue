<template>
  <section
    id="sla"
    class="py-12 sm:py-14 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-24"
  >
    <span id="guarantee" class="sr-only">Guarantee</span>
    <div class="layung-panel p-8 sm:p-12 border border-emerald-500/30 bg-gradient-to-br from-card via-card to-emerald-500/5 relative overflow-hidden">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
        <div class="lg:col-span-8 space-y-4 flex flex-col">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 uppercase tracking-wider font-mono">
            {{ t('sla.badge', 'SLA berbasis kontrak') }}
          </span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
            {{ t('sla.title', 'Komitmen layanan yang tertulis di kontrak') }}
          </h2>
          <p class="text-muted-foreground text-sm sm:text-base leading-relaxed max-w-2xl">
            {{ t('sla.body', 'Target ketersediaan, jam dukungan, dan eskalasi insiden disepakati per layanan — Dedicated Internet, broadband, maupun managed services. Kompensasi, jika ada, mengikuti klausul kontrak; bukan klaim uptime universal.') }}
          </p>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 font-mono text-xs text-foreground">
            <div class="p-3 rounded-xl bg-muted/60 border border-border">
              <span class="text-muted-foreground block text-[10px]">{{ t('sla.metric1Label', 'Dukungan operasional') }}</span>
              <strong class="text-emerald-600 dark:text-emerald-400 text-sm font-bold">{{ t('sla.metric1Value', 'NOC & Service Desk') }}</strong>
            </div>
            <div class="p-3 rounded-xl bg-muted/60 border border-border">
              <span class="text-muted-foreground block text-[10px]">{{ t('sla.metric2Label', 'Eskalasi insiden') }}</span>
              <strong class="text-sky-600 dark:text-sky-400 text-sm font-bold">{{ t('sla.metric2Value', 'Sesuai kontrak') }}</strong>
            </div>
            <div class="p-3 rounded-xl bg-muted/60 border border-border">
              <span class="text-muted-foreground block text-[10px]">{{ t('sla.metric3Label', 'Wilayah layanan') }}</span>
              <strong class="text-cyan-600 dark:text-cyan-400 text-sm font-bold">{{ t('sla.metric3Value', 'Bandung & Jawa Barat') }}</strong>
            </div>
          </div>

          <div class="pt-2 mt-auto">
            <Button
              :as="RouterLink"
              to="/achievement"
              variant="outline"
              size="md"
              class="font-bold"
            >
              {{ t('sla.cta', 'Lihat identitas jaringan & komitmen') }}
              <ArrowRight class="w-4 h-4" />
            </Button>
          </div>
        </div>

        <div class="lg:col-span-4 flex flex-col items-center justify-center p-6 rounded-2xl bg-slate-900 border border-slate-800 text-center space-y-3 text-white">
          <ShieldCheck class="w-12 h-12 text-emerald-400" />
          <div class="text-2xl sm:text-3xl font-black font-heading text-emerald-400">
            {{ t('sla.panelTitle', 'SLA kontrak') }}
          </div>
          <p class="text-xs text-slate-400 leading-relaxed">
            {{ t('sla.panelBody', 'ASN AS153992 · anggota IDNIC. Target layanan mengikuti dokumen yang ditandatangani, bukan angka pemasaran.') }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router';
import { ShieldCheck, ArrowRight } from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Button } from '@/modules/Layout/views/themes/layung/ui';

const { t } = useThemeI18n('layung');
</script>

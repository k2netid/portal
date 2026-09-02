<template>
  <div class="space-y-16">
    <!-- DIA — compact callout, bukan kartu besar -->
    <section class="space-y-4">
      <div class="max-w-3xl space-y-2">
        <h2 class="text-2xl font-extrabold text-foreground font-heading">
          {{ t('pricingIsp.diaTitle', 'Dedicated Internet (DIA)') }}
        </h2>
        <p class="text-sm text-muted-foreground leading-relaxed">
          {{ t('pricingIsp.diaSubtitle', 'Koneksi fiber dedicated dengan alokasi bandwidth terjamin. Harga disesuaikan kapasitas, jarak last-mile, dan SLA kontrak.') }}
        </p>
      </div>
      <div class="layung-panel p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 border border-sky-500/20 bg-gradient-to-r from-card to-sky-500/5">
        <div class="space-y-2 flex-1">
          <span class="text-xs font-bold uppercase tracking-wider text-sky-500 font-mono">DIA</span>
          <h3 class="text-xl font-bold text-foreground font-heading">
            {{ diaPlan.name }}
          </h3>
          <p class="text-sm text-muted-foreground leading-relaxed max-w-2xl">
            {{ diaPlan.description }}
          </p>
          <ul class="grid sm:grid-cols-2 gap-x-4 gap-y-1.5 pt-2 text-xs text-muted-foreground">
            <li
              v-for="(feat, idx) in diaPlan.features"
              :key="idx"
              class="flex items-start gap-2"
            >
              <Check class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" />
              <span>{{ feat }}</span>
            </li>
          </ul>
        </div>
        <div class="shrink-0 text-center sm:text-right space-y-3">
          <div>
            <strong class="text-2xl font-black text-foreground font-heading block">{{ diaPlan.price }}</strong>
            <span class="text-xs text-muted-foreground">{{ diaPlan.priceNote }}</span>
          </div>
          <Button
            as="router-link"
            to="/contact"
            variant="primary"
            size="md"
            class="font-bold w-full sm:w-auto"
          >
            {{ t('packages.contactSales', 'Hubungi Sales') }}
          </Button>
        </div>
      </div>
    </section>

    <!-- SOHO -->
    <section class="space-y-6">
      <div class="max-w-3xl space-y-2">
        <h2 class="text-2xl font-extrabold text-foreground font-heading">
          {{ t('pricingIsp.sohoTitle', 'Broadband Bisnis (SOHO)') }}
        </h2>
        <p class="text-sm text-muted-foreground leading-relaxed">
          {{ t('pricingIsp.sohoSubtitle', 'Broadband bisnis 50–100 Mbps untuk SOHO, ruko, dan kantor kecil. Harga final dikonfirmasi setelah survei lokasi.') }}
        </p>
      </div>
      <PricingPlanGrid
        :plans="sohoPlans"
        grid-class="md:grid-cols-2"
        :popular-badge="t('packages.popularBadge', 'Pilihan Populer')"
        :price-from-label="t('packages.priceFrom', 'Mulai dari')"
        :select-plan-label="t('packages.selectPlan', 'Pilih Layanan Ini')"
        :contact-sales-label="t('packages.contactSales', 'Hubungi Sales')"
      />
    </section>

    <!-- Retail -->
    <section class="space-y-6">
      <div class="max-w-3xl space-y-2">
        <h2 class="text-2xl font-extrabold text-foreground font-heading">
          {{ t('pricingIsp.retailTitle', 'Retail Broadband') }}
        </h2>
        <p class="text-sm text-muted-foreground leading-relaxed">
          {{ t('pricingIsp.retailSubtitle', 'Paket internet rumah tangga dan ritel di area jangkauan jaringan K2NET. Harga belum termasuk PPN.') }}
        </p>
      </div>
      <PricingPlanGrid
        :plans="retailPlans"
        grid-class="md:grid-cols-3"
        :popular-badge="t('packages.popularBadge', 'Pilihan Populer')"
        :price-from-label="t('packages.priceFrom', 'Mulai dari')"
        :select-plan-label="t('packages.selectPlan', 'Pilih Layanan Ini')"
        :contact-sales-label="t('packages.contactSales', 'Hubungi Sales')"
      />
    </section>
  </div>
</template>

<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Button } from '@/modules/Layout/views/themes/layung/ui';
import PricingPlanGrid from './PricingPlanGrid.vue';
import {
  LAYUNG_ISP_DEDICATED,
  LAYUNG_ISP_RETAIL_PLANS,
  LAYUNG_ISP_SOHO_PLANS,
} from '@/modules/Layout/views/themes/layung/composables/layungPricingPlans';

const { t } = useThemeI18n('layung');

const diaPlan = LAYUNG_ISP_DEDICATED;
const sohoPlans = LAYUNG_ISP_SOHO_PLANS;
const retailPlans = LAYUNG_ISP_RETAIL_PLANS;
</script>

<template>
  <div class="space-y-10">
    <div class="max-w-3xl mx-auto text-center space-y-4">
      <p class="text-sm text-muted-foreground leading-relaxed">
        {{ t('pricingMsp.audience', 'Paket managed services K2NET dirancang untuk sekolah dan lembaga pendidikan yang membutuhkan dukungan IT andal tanpa harus membangun tim internal. Kami menangani jaringan, server & aplikasi, CCTV, serta kebutuhan operasional IT lainnya — sesuai lingkup yang disepakati dalam kontrak.') }}
      </p>
    </div>

    <PricingPlanGrid
      :plans="mspPlans"
      grid-class="md:grid-cols-3 pt-2"
      :popular-badge="t('packages.popularBadge', 'Pilihan Populer')"
      :price-from-label="t('packages.priceFrom', 'Mulai dari')"
      :select-plan-label="t('packages.selectPlan', 'Pilih Layanan Ini')"
      :contact-sales-label="t('packages.contactSales', 'Hubungi Sales')"
    />

    <p class="text-center text-xs text-muted-foreground max-w-2xl mx-auto">
      {{ t('pricingMsp.footnote', 'Harga dan cakupan layanan disesuaikan kebutuhan institusi Anda. Hubungi tim sales K2NET untuk konsultasi dan penawaran.') }}
    </p>
  </div>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import PricingPlanGrid from './PricingPlanGrid.vue';
import { LAYUNG_MSP_PLANS } from '@/modules/Layout/views/themes/layung/composables/layungPricingPlans';

const { t } = useThemeI18n('layung');
const mspPlans = LAYUNG_MSP_PLANS;
</script>

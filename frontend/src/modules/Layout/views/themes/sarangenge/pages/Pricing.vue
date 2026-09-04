<template>
  <SarangengePageGate
    setting-key="enable_pricing"
    :title="t('pages.pricing.title', 'Bebas Biaya Pendidikan & PPDB')"
  >
    <div class="sarangenge-theme flex-1 flex flex-col py-10 md:py-12">
      <BlockRenderer
        v-if="hasBuilderBlocks"
        :blocks="builderBlocks"
        :context="{ site: { name: displaySchoolName } }"
      />

      <template v-else>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 w-full text-center">
          <div class="space-y-4">
            <Breadcrumb :items="[{ name: t('pages.pricing.title', 'Bebas Biaya Pendidikan & PPDB') }]" class="justify-center" />
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
              {{ t('pages.pricing.heading', 'Bebas Biaya Pendidikan') }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed max-w-2xl mx-auto">
              {{ t('pages.pricing.subtitle', 'Sebagai sekolah negeri, SMK Negeri 6 Bandung didukung sepenuhnya oleh dana BOS (APBN) dan BOPD (APBD Jawa Barat).') }}
            </p>
          </div>

          <div class="sarangenge-panel p-8 md:p-12 bg-[var(--sarangenge-teal,#0f766e)]/5 border-[var(--sarangenge-teal,#0f766e)]/20 shadow-lg">
            <div class="w-16 h-16 mx-auto rounded-full bg-[var(--sarangenge-teal,#0f766e)]/10 flex items-center justify-center text-[var(--sarangenge-teal-deep,#115e59)] mb-6">
              <CheckCircle2 class="w-8 h-8" />
            </div>
            <h2 class="text-2xl font-bold font-heading text-foreground mb-4">
              {{ t('pages.pricing.freeQualityTitle', 'Sekolah Gratis Berkualitas') }}
            </h2>
            <p class="text-muted-foreground mb-8">
              {{ t('pages.pricing.freeQualityDesc', 'Tidak ada pungutan Sumbangan Pembinaan Pendidikan (SPP) bulanan maupun Uang Bangunan. Semua proses penerimaan peserta didik baru dan kegiatan akademik reguler difasilitasi oleh negara.') }}
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left mb-8">
              <div class="space-y-3">
                <div class="flex items-start gap-3">
                  <Check class="w-5 h-5 text-green-600 mt-0.5 shrink-0" />
                  <span class="text-sm font-medium">{{ t('pages.pricing.benefit1', 'Bebas SPP Bulanan (Ditanggung BOPD Jabar)') }}</span>
                </div>
                <div class="flex items-start gap-3">
                  <Check class="w-5 h-5 text-green-600 mt-0.5 shrink-0" />
                  <span class="text-sm font-medium">{{ t('pages.pricing.benefit2', 'Bebas Uang Pangkal / Uang Gedung') }}</span>
                </div>
              </div>
              <div class="space-y-3">
                <div class="flex items-start gap-3">
                  <Check class="w-5 h-5 text-green-600 mt-0.5 shrink-0" />
                  <span class="text-sm font-medium">{{ t('pages.pricing.benefit3', 'Bantuan Operasional Sekolah (BOS) Pusat') }}</span>
                </div>
                <div class="flex items-start gap-3">
                  <Check class="w-5 h-5 text-green-600 mt-0.5 shrink-0" />
                  <span class="text-sm font-medium">{{ t('pages.pricing.benefit4', 'Fasilitas Praktik Standar Industri SMK PK') }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="sarangenge-panel p-8 text-center space-y-6">
            <h2 class="text-2xl font-bold font-heading text-foreground">
              {{ t('pages.pricing.ppdbTitle', 'Penerimaan Peserta Didik Baru (PPDB)') }}
            </h2>
            <p class="text-muted-foreground text-sm max-w-xl mx-auto">
              {{ t('pages.pricing.ppdbDesc', 'Proses seleksi dan pendaftaran PPDB SMK Negeri 6 Bandung dilaksanakan secara terpusat melalui portal resmi Dinas Pendidikan Provinsi Jawa Barat.') }}
            </p>
            <a
              :href="ppdbPortalUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center justify-center gap-2 px-8 py-3 rounded-full bg-[var(--sarangenge-teal,#0f766e)] text-primary-foreground font-bold hover:bg-[var(--sarangenge-teal-deep,#115e59)] transition-colors"
            >
              {{ t('pages.pricing.ppdbButton', 'Akses Portal PPDB Jabar') }}
              <ExternalLink class="w-5 h-5" />
            </a>
          </div>
        </div>
      </template>
    </div>
  </SarangengePageGate>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import Breadcrumb from '@/modules/Layout/views/themes/sarangenge/components/shared/Breadcrumb.vue';
import SarangengePageGate from '@/modules/Layout/views/themes/sarangenge/components/shared/SarangengePageGate.vue';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import { CheckCircle2, Check, ExternalLink } from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { displaySchoolName, ppdbPortalUrl } = useSarangengeIdentity();
const { builderBlocks, hasBuilderBlocks } = useThemePageOverride('pricing');
</script>

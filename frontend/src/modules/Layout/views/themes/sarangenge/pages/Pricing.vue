<template>
  <div
    data-ja-customizer-target="pricing"
    class="sarangenge-theme flex-1 flex flex-col py-10 md:py-12"
  >
    <BlockRenderer
      v-if="hasBuilderBlocks"
      :blocks="builderBlocks"
      :context="{ post: pageData, site: { name: displaySchoolName } }"
    />

    <ThemeSafeHtml
      v-else-if="cmsBody"
      class="container mx-auto px-4 py-16"
      :html="cmsBody"
      mode="publishing"
    />

    <template v-else>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 w-full">
        <!-- Breadcrumb & Header -->
        <div class="space-y-4">
          <Breadcrumb :items="[{ name: t('pages.pricing.title', 'Biaya Pendidikan & Beasiswa') }]" />
          <div class="max-w-3xl space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
              <CreditCard class="w-3.5 h-3.5" />
              Transparansi Biaya 2026/2027
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
              {{ t('pages.pricing.title', 'Investasi Pendidikan & Skema Beasiswa') }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
              {{ t('pages.pricing.subtitle', 'Rincian biaya pendidikan yang transparan, lengkap dengan program beasiswa prestasi akademik, tahfidz, dan bantuan afirmasi.') }}
            </p>
          </div>
        </div>

        <!-- Pricing Schemes Grid -->
        <div
          id="beasiswa"
          class="scroll-mt-28 grid grid-cols-1 md:grid-cols-3 gap-8"
        >
          <!-- Card 1: Jalur Reguler -->
          <div class="sarangenge-panel p-8 space-y-6 flex flex-col justify-between">
            <div class="space-y-4">
              <span class="px-3 py-1 rounded-full text-xs font-bold bg-muted text-foreground inline-block">
                Jalur Reguler
              </span>
              <h3 class="text-2xl font-bold text-foreground font-heading">
                Program Nasional
              </h3>
              <p class="text-xs text-muted-foreground leading-relaxed">
                Kurikulum Merdeka Riset, lab sains lengkap, ekstrakurikuler pilihan, dan bimbingan PTN.
              </p>
              <div class="pt-4 border-t border-border/60">
                <div class="text-xs text-muted-foreground">
                  SPP Bulanan
                </div>
                <div class="text-3xl font-extrabold font-heading text-foreground">
                  Rp 1.250.000<span class="text-xs font-normal text-muted-foreground">/bulan</span>
                </div>
              </div>
              <ul class="space-y-2 text-xs text-muted-foreground pt-2">
                <li class="flex items-center gap-2">
                  <Check class="w-4 h-4 text-[var(--sarangenge-teal,#0f766e)] shrink-0" />
                  <span>Akses seluruh laboratorium & perpustakaan</span>
                </li>
                <li class="flex items-center gap-2">
                  <Check class="w-4 h-4 text-[var(--sarangenge-teal,#0f766e)] shrink-0" />
                  <span>2 Pilihan klub ekstrakurikuler</span>
                </li>
                <li class="flex items-center gap-2">
                  <Check class="w-4 h-4 text-[var(--sarangenge-teal,#0f766e)] shrink-0" />
                  <span>Ujian berbasis komputer (CBT) & rapor digital</span>
                </li>
              </ul>
            </div>
            <Button
              as="router-link"
              to="/contact"
              variant="outline"
              size="md"
              class="w-full"
            >
              Info PPDB Reguler
            </Button>
          </div>

          <!-- Card 2: Jalur Bilingual / Cambridge (Featured) -->
          <div class="sarangenge-panel p-8 space-y-6 flex flex-col justify-between border-2 border-primary shadow-xl relative bg-gradient-to-b from-card to-primary/5">
            <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full text-[10px] font-black bg-primary text-primary-foreground uppercase tracking-widest shadow-md">
              Paling Diminati
            </div>
            <div class="space-y-4 pt-2">
              <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-900 dark:text-amber-200 inline-block">
                Bilingual Track
              </span>
              <h3 class="text-2xl font-bold text-foreground font-heading">
                Cambridge International
              </h3>
              <p class="text-xs text-muted-foreground leading-relaxed">
                Pengantar bahasa Inggris aktif, sertifikasi Cambridge IGCSE, bimbingan beasiswa luar negeri, dan AI coding lab.
              </p>
              <div class="pt-4 border-t border-border/60">
                <div class="text-xs text-muted-foreground">
                  SPP Bulanan
                </div>
                <div class="text-3xl font-extrabold font-heading text-[var(--sarangenge-teal,#0f766e)]">
                  Rp 1.950.000<span class="text-xs font-normal text-muted-foreground">/bulan</span>
                </div>
              </div>
              <ul class="space-y-2 text-xs text-muted-foreground pt-2">
                <li class="flex items-center gap-2">
                  <Check class="w-4 h-4 text-[var(--sarangenge-teal,#0f766e)] shrink-0" />
                  <span>Semua fasilitas program nasional</span>
                </li>
                <li class="flex items-center gap-2">
                  <Check class="w-4 h-4 text-[var(--sarangenge-teal,#0f766e)] shrink-0" />
                  <span>Native speaker & Cambridge teacher</span>
                </li>
                <li class="flex items-center gap-2">
                  <Check class="w-4 h-4 text-[var(--sarangenge-teal,#0f766e)] shrink-0" />
                  <span>Simulasi tes TOEFL / IELTS berkala</span>
                </li>
              </ul>
            </div>
            <Button
              as="router-link"
              to="/contact"
              variant="primary"
              size="md"
              class="w-full font-bold shadow-md shadow-[var(--sarangenge-teal)]/20"
            >
              Daftar Kelas Cambridge
            </Button>
          </div>

          <!-- Card 3: Beasiswa Prestasi & Tahfidz -->
          <div class="sarangenge-panel p-8 space-y-6 flex flex-col justify-between">
            <div class="space-y-4">
              <span class="px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-sun,#e8a317)]/15 text-amber-800 dark:text-amber-200 inline-block">
                Jalur Beasiswa
              </span>
              <h3 class="text-2xl font-bold text-foreground font-heading">
                Beasiswa Bintang Sarangenge
              </h3>
              <p class="text-xs text-muted-foreground leading-relaxed">
                Potongan biaya SPP dan uang gedung 50% hingga 100% (Bebas Biaya) bagi peraih medali olimpiade dan penghafal Quran.
              </p>
              <div class="pt-4 border-t border-border/60">
                <div class="text-xs text-muted-foreground">
                  Skema Beasiswa
                </div>
                <div class="text-3xl font-extrabold font-heading text-[var(--sarangenge-sun,#e8a317)]">
                  Free 100%<span class="text-xs font-normal text-muted-foreground"> / SPP</span>
                </div>
              </div>
              <ul class="space-y-2 text-xs text-muted-foreground pt-2">
                <li class="flex items-center gap-2">
                  <Check class="w-4 h-4 text-[var(--sarangenge-sun,#e8a317)] shrink-0" />
                  <span>Juara OSN / Kejuaraan Internasional</span>
                </li>
                <li class="flex items-center gap-2">
                  <Check class="w-4 h-4 text-[var(--sarangenge-sun,#e8a317)] shrink-0" />
                  <span>Tahfidz minimal 3 Juz Al-Quran</span>
                </li>
                <li class="flex items-center gap-2">
                  <Check class="w-4 h-4 text-[var(--sarangenge-sun,#e8a317)] shrink-0" />
                  <span>Beasiswa afirmasi keluarga berpenghasilan rendah</span>
                </li>
              </ul>
            </div>
            <Button
              as="router-link"
              to="/contact"
              variant="outline"
              size="md"
              class="w-full"
            >
              Ajukan Beasiswa
            </Button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '@/modules/Layout/views/themes/sarangenge/components/shared/Breadcrumb.vue';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';
import { CreditCard, Check } from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { displaySchoolName } = useSarangengeIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('pricing');

useThemeHashScroll(128);
</script>

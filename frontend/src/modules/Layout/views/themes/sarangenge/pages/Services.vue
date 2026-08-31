<template>
  <div
    data-ja-customizer-target="facilities"
    class="sarangenge-theme flex-1 flex flex-col py-10 sm:py-16"
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
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 w-full">
        <!-- Breadcrumb & Header -->
        <div class="space-y-4">
          <Breadcrumb :items="[{ name: t('pages.services.title', 'Fasilitas Kampus') }]" />
          <div class="max-w-3xl space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
              <Building2 class="w-3.5 h-3.5" />
              Infrastruktur & Sarana 2026
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
              {{ t('pages.services.title', 'Fasilitas & Sarana Kampus Modern') }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
              {{ t('pages.services.subtitle', 'Menyediakan lingkungan belajar yang aman, nyaman, higienis, dan terintegrasi dengan teknologi digital terkini.') }}
            </p>
          </div>
        </div>

        <!-- Detailed Facilities Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="(fac, idx) in allFacilities"
            :key="idx"
            :id="fac.anchor"
            class="sarangenge-panel p-8 space-y-4 hover:border-[var(--sarangenge-teal,#0f766e)]/40 hover:shadow-xl transition-all duration-300 flex flex-col justify-between scroll-mt-28"
          >
            <div class="space-y-4">
              <div class="w-14 h-14 rounded-2xl bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal,#0f766e)] flex items-center justify-center font-bold shadow-inner">
                <component
                  :is="fac.icon"
                  class="w-7 h-7"
                />
              </div>

              <h3 class="text-xl font-bold text-foreground font-heading">
                {{ fac.title }}
              </h3>

              <p class="text-sm text-muted-foreground leading-relaxed">
                {{ fac.description }}
              </p>
            </div>

            <div class="pt-4 border-t border-border/60 text-xs font-bold text-[var(--sarangenge-teal,#0f766e)]">
              <span>{{ fac.features }}</span>
            </div>
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
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import { useThemeHashScroll } from '@/modules/Layout/composables/useThemeHashScroll';
import { Building2, Monitor, Microscope, Library, Dumbbell, Utensils, HeartPulse } from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { displaySchoolName } = useSarangengeIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('services');

useThemeHashScroll(128);

const allFacilities = [
  {
    anchor: 'smart-class',
    icon: Monitor,
    title: 'Smart Classroom & AI Learning Hub',
    description: 'Ruang kelas ber-AC dengan Interactive Smart Board, pencahayaan ergonomis, audio jernih, dan koneksi internet serat optik.',
    features: 'Kapasitas 24-28 Siswa · Ergonomic Seats',
  },
  {
    anchor: 'lab-sains',
    icon: Microscope,
    title: 'Laboratorium Sains & Riset Terpadu',
    description: 'Fasilitas praktikum Fisika, Kimia, dan Biologi lengkap dengan mikroskop digital, lemari asam, dan kit sensor modern.',
    features: 'Standar Keselamatan Lab · Sertifikasi K3',
  },
  {
    icon: Library,
    title: 'Perpustakaan Digital & Reading Pods',
    description: 'Pusat sumber belajar dengan akses e-book ribuan judul, ruang baca hening, ruang podcast, dan area diskusi kelompok.',
    features: 'E-Library Catalog · Silent Study Room',
  },
  {
    icon: Dumbbell,
    title: 'Sport Arena & Lapangan Multifungsi',
    description: 'Gelanggang olahraga indoor untuk basket, futsal, bulutangkis, serta lintasan atletik dan panjat dinding.',
    features: 'Indoor Hall · Lantai Standar FIBA',
  },
  {
    icon: Utensils,
    title: 'Kantin Higienis & Eco-Cafe',
    description: 'Penyedia makanan sehat bebas MSG berlebih dan pewarna buatan dengan pengawasan ketat oleh ahli gizi sekolah.',
    features: 'Menu Sehat Terpantau · Cashless Payment',
  },
  {
    icon: HeartPulse,
    title: 'Pusat Layanan Kesehatan & Konseling (UKS)',
    description: 'Ruang medis dengan tenaga perawat terlatih, fasilitas pertolongan pertama, dan ruang konsultasi psikolog.',
    features: 'Tenaga Medis Siaga · Bimbingan Konseling',
  },
];
</script>

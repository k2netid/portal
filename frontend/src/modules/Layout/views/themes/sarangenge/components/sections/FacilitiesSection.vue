<template>
  <section
    data-ja-customizer-target="facilities"
    class="py-12 sm:py-14"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col md:flex-row md:items-end justify-between mb-14 gap-6">
        <div class="max-w-2xl space-y-3">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
            <Building2 class="w-3.5 h-3.5" />
            Lingkungan Belajar
          </span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
            {{ facilitiesTitle }}
          </h2>
          <p class="text-muted-foreground text-base sm:text-lg leading-relaxed">
            {{ facilitiesSubtitle }}
          </p>
        </div>

        <Button
          as="router-link"
          to="/services"
          variant="outline"
          size="md"
          class="self-start md:self-auto shrink-0"
        >
          {{ t('common.viewAll', 'Lihat Semua Fasilitas') }}
          <ArrowRight class="w-4 h-4 ml-1" />
        </Button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="(fac, idx) in facilitiesList"
          :key="idx"
          class="sarangenge-panel p-6 space-y-3 group hover:border-[var(--sarangenge-teal,#0f766e)]/40 hover:-translate-y-1 transition-all duration-300"
        >
          <div class="w-12 h-12 rounded-xl bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal,#0f766e)] flex items-center justify-center font-bold shadow-inner group-hover:scale-110 transition-transform">
            <component
              :is="fac.icon"
              class="w-6 h-6"
            />
          </div>
          <h3 class="text-lg font-bold text-foreground font-heading">
            {{ fac.title }}
          </h3>
          <p class="text-xs text-muted-foreground leading-relaxed">
            {{ fac.description }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Building2, ArrowRight, Monitor, Microscope, Library, Dumbbell } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useTheme } from '@/modules/Layout/composables/useTheme';

const { t } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();

const facilitiesTitle = computed(() => {
  return (getSetting('facilities_title', '') as string) || t('pages.services.title', 'Fasilitas & Sarana Kampus Modern');
});

const facilitiesSubtitle = computed(() => {
  return (getSetting('facilities_subtitle', '') as string) || t('pages.services.subtitle', 'Mendukung pembelajaran interaktif berstandar global dengan infrastruktur ramah lingkungan.');
});

const facilitiesList = [
  {
    icon: Monitor,
    title: 'Smart Classroom & AI Lab',
    description: 'Ruang kelas interaktif dengan papan digital, high-speed WiFi 6, dan perangkat komputasi modern.',
  },
  {
    icon: Microscope,
    title: 'Laboratorium Sains Terpadu',
    description: 'Peralatan fisika, kimia, biologi modern untuk eksperimen langsung dan riset ilmiah.',
  },
  {
    icon: Library,
    title: 'Perpustakaan & Media Center',
    description: 'Ribuan koleksi buku fisik, e-book reader, ruang diskusi kedap suara, dan akses jurnal ilmiah.',
  },
  {
    icon: Dumbbell,
    title: 'Sport Arena & Lapangan',
    description: 'Gelanggang olahraga indoor multifungsi (basket, futsal, bulutangkis) dan lintasan atletik.',
  },
];
</script>

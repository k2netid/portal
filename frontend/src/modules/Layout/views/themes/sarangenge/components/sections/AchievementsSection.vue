<template>
  <section
    data-ja-customizer-target="achievements"
    class="py-12 sm:py-14"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-end justify-between mb-14 gap-6">
        <div class="max-w-2xl space-y-3">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-sun,#e8a317)]/15 text-amber-800 dark:text-amber-200 border border-[var(--sarangenge-sun)]/30">
            <Trophy class="w-3.5 h-3.5" />
            Galeri Keunggulan
          </span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
            {{ achievementsTitle }}
          </h2>
          <p class="text-muted-foreground text-base sm:text-lg leading-relaxed">
            {{ achievementsSubtitle }}
          </p>
        </div>

        <Button
          as="router-link"
          to="/achievement"
          variant="outline"
          size="md"
          class="self-start md:self-auto shrink-0"
        >
          {{ t('common.viewAll', 'Lihat Semua Prestasi') }}
          <ArrowRight class="w-4 h-4 ml-1" />
        </Button>
      </div>

      <!-- Achievement Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div
          v-for="(item, idx) in achievementsList"
          :key="idx"
          class="sarangenge-panel p-6 space-y-4 hover:border-[var(--sarangenge-sun,#e8a317)]/40 hover:shadow-xl transition-all duration-300 flex flex-col justify-between"
        >
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-[var(--sarangenge-teal,#0f766e)] text-white shadow-sm">
                {{ item.year }}
              </span>
              <span class="text-xs font-bold text-amber-600 dark:text-amber-300 flex items-center gap-1">
                <Medal class="w-4 h-4" />
                {{ item.level }}
              </span>
            </div>

            <h3 class="text-xl font-bold text-foreground font-heading">
              {{ item.title }}
            </h3>

            <p class="text-sm text-muted-foreground leading-relaxed">
              {{ item.description }}
            </p>
          </div>

          <div class="pt-4 border-t border-border/60 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">
              {{ item.student.charAt(0) }}
            </div>
            <div>
              <div class="text-xs font-bold text-foreground">
                {{ item.student }}
              </div>
              <div class="text-[11px] text-muted-foreground">
                {{ item.category }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Trophy, ArrowRight, Medal } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useTheme } from '@/modules/Layout/composables/useTheme';

const { t } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();

const achievementsTitle = computed(() => {
  return (getSetting('achievements_title', '') as string) || t('pages.achievement.title', 'Prestasi & Penghargaan Siswa');
});

const achievementsSubtitle = computed(() => {
  return (getSetting('achievements_subtitle', '') as string) || t('pages.achievement.subtitle', 'Bukti dedikasi dan keunggulan akademik, sains, seni, dan olahraga di tingkat regional hingga internasional.');
});

const achievementsList = [
  {
    year: '2026',
    level: 'Tingkat Internasional',
    title: 'Medali Emas International Mathematical Olympiad (IMO)',
    description: 'Meraih skor sempurna dalam pemecahan geometri & kombinatorika melawan 110 negara delegasi.',
    student: 'Ahmad Fadhil Prasetya',
    category: 'Kelas XII MIPA Unggulan',
  },
  {
    year: '2026',
    level: 'Tingkat Nasional',
    title: 'Juara 1 National Autonomous Robotics Championship',
    description: 'Inovasi robot pemilah sampah berbasis Computer Vision dan AI Machine Learning.',
    student: 'Tim Robotika Sarangenge',
    category: 'Klub STEM & Robotika',
  },
  {
    year: '2025',
    level: 'Tingkat Provinsi',
    title: 'Juara Umum Festival Seni & Debat Bahasa Inggris (NSDC)',
    description: 'Menorehkan gelar Best Speaker dan juara debat tingkat provinsi Jawa Barat.',
    student: 'Nadia Putri Anindita',
    category: 'Klub English & Debat',
  },
];
</script>

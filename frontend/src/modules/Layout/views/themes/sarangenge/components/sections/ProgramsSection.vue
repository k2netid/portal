<template>
  <section
    data-ja-customizer-target="programs"
    class="py-12 sm:py-14 bg-muted/30 border-y border-border/60"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <div class="flex flex-col md:flex-row md:items-end justify-between mb-14 gap-6">
        <div class="max-w-2xl space-y-3">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
            <BookOpen class="w-3.5 h-3.5" />
            Keunggulan Pembelajaran
          </span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
            {{ programsTitle }}
          </h2>
          <p class="text-muted-foreground text-base sm:text-lg leading-relaxed">
            {{ programsSubtitle }}
          </p>
        </div>

        <Button
          as="router-link"
          to="/solusi"
          variant="outline"
          size="md"
          class="self-start md:self-auto shrink-0"
        >
          {{ t('common.viewAll', 'Lihat Seluruh Program') }}
          <ArrowRight class="w-4 h-4 ml-1" />
        </Button>
      </div>

      <!-- Program Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div
          v-for="(prog, idx) in programsList"
          :key="idx"
          class="sarangenge-panel p-8 flex flex-col justify-between group hover:border-[var(--sarangenge-teal,#0f766e)]/40 hover:shadow-xl transition-all duration-300"
        >
          <div class="space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal,#0f766e)] flex items-center justify-center font-bold shadow-inner group-hover:scale-110 transition-transform duration-300">
              <component
                :is="prog.icon"
                class="w-6 h-6"
              />
            </div>

            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-[var(--sarangenge-sun,#e8a317)]/15 text-amber-800 dark:text-amber-300">
              {{ prog.category }}
            </span>

            <h3 class="text-xl font-bold text-foreground font-heading">
              {{ prog.title }}
            </h3>

            <p class="text-sm text-muted-foreground leading-relaxed">
              {{ prog.description }}
            </p>
          </div>

          <div class="pt-6 mt-6 border-t border-border/60 flex items-center justify-between text-xs font-bold text-[var(--sarangenge-teal,#0f766e)]">
            <span>{{ t('common.learnMore', 'Pelajari Kurikulum') }}</span>
            <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { BookOpen, ArrowRight, Cpu, Compass, Globe, HeartHandshake, Trophy, Sparkles } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useTheme } from '@/modules/Layout/composables/useTheme';

const { t } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();

const programsTitle = computed(() => {
  return (getSetting('programs_title', '') as string) || t('pages.solusi.title', 'Program Unggulan & Kurikulum');
});

const programsSubtitle = computed(() => {
  return (getSetting('programs_subtitle', '') as string) || t('pages.solusi.subtitle', 'Pilihan jalur akademik, sains terapan, teknologi, bahasa internasional, dan penguatan karakter.');
});

const programsList = [
  {
    icon: Compass,
    category: 'Akademik Nasional',
    title: 'Kurikulum Merdeka Riset & Inovasi',
    description: 'Pembelajaran berbasis proyek (P5) yang mendorong kemampuan analisis mendalam, pemecahan masalah nyata, dan portofolio karya ilmiah siswa.',
  },
  {
    icon: Globe,
    category: 'Internasional',
    title: 'Bilingual & Cambridge Immersion',
    description: 'Pengantar bahasa Inggris dalam sains & matematika dengan persiapan sertifikasi ujian Cambridge IGCSE / A-Level dan TOEFL/IELTS.',
  },
  {
    icon: Cpu,
    category: 'Teknologi Masa Depan',
    title: 'STEM, AI & Robotika Terapan',
    description: 'Laboratorium kecerdasan buatan, dasar-dasar coding, perakitan robotika, dan komputasi kreatif untuk mempersiapkan era digital 2026+.',
  },
  {
    icon: HeartHandshake,
    category: 'Karakter & Nilai',
    title: 'Tahfidz & Kepemimpinan Beradab',
    description: 'Pembinaan akhlakul karimah, halaqah Quran bersanad, dan kepemimpinan sosial yang menumbuhkan empati dan integritas luhur.',
  },
  {
    icon: Trophy,
    category: 'Minat & Bakat',
    title: 'Akademi Prestasi Olahraga & Seni',
    description: 'Pelatihan terstruktur bidang atletik, basket, futsal, seni musik, orkestra, dan teater untuk meraih prestasi tingkat daerah hingga nasional.',
  },
  {
    icon: Sparkles,
    category: 'Lanjut Studi',
    title: 'Klinik PTN & Beasiswa Luar Negeri',
    description: 'Pendampingan intensif SNBP, UTBK SNBT, kedinasan, hingga beasiswa kuliah universitas top dunia dengan rekam jejak kelulusan teruji.',
  },
];
</script>

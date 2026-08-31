<template>
  <div
    data-ja-customizer-target="programs"
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
          <Breadcrumb :items="[{ name: t('pages.solusi.title', 'Program & Kurikulum') }]" />
          <div class="max-w-3xl space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
              <BookOpen class="w-3.5 h-3.5" />
              Struktur Akademik 2026
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
              {{ t('pages.solusi.title', 'Program Pendidikan & Kurikulum Unggulan') }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
              {{ t('pages.solusi.subtitle', 'Menyelenggarakan kurikulum adaptif yang memadukan kedalaman ilmu pengetahuan, kompetensi digital, dan pembinaan karakter kepemimpinan.') }}
            </p>
          </div>
        </div>

        <!-- Flagship Programs Detailed Grid -->
        <div
          id="programs"
          class="scroll-mt-28 grid grid-cols-1 md:grid-cols-2 gap-8"
        >
          <div
            v-for="(track, idx) in academicTracks"
            :key="idx"
            class="sarangenge-panel p-8 space-y-5 hover:border-[var(--sarangenge-teal,#0f766e)]/40 hover:shadow-xl transition-all duration-300 flex flex-col justify-between"
          >
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div class="w-12 h-12 rounded-2xl bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal,#0f766e)] flex items-center justify-center font-bold shadow-inner">
                  <component
                    :is="track.icon"
                    class="w-6 h-6"
                  />
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-sun,#e8a317)]/15 text-amber-800 dark:text-amber-200">
                  {{ track.badge }}
                </span>
              </div>

              <h3 class="text-2xl font-bold text-foreground font-heading">
                {{ track.title }}
              </h3>

              <p class="text-sm text-muted-foreground leading-relaxed">
                {{ track.description }}
              </p>

              <div class="space-y-2 pt-2">
                <h4 class="text-xs font-bold uppercase tracking-wider text-foreground">
                  Fokus Pembelajaran:
                </h4>
                <ul class="space-y-1.5 text-xs text-muted-foreground">
                  <li
                    v-for="(fokus, fIdx) in track.points"
                    :key="fIdx"
                    class="flex items-center gap-2"
                  >
                    <Check class="w-3.5 h-3.5 text-[var(--sarangenge-teal,#0f766e)] shrink-0" />
                    <span>{{ fokus }}</span>
                  </li>
                </ul>
              </div>
            </div>

            <div class="pt-4 border-t border-border/60">
              <Button
                as="router-link"
                to="/contact"
                variant="outline"
                size="sm"
                class="w-full justify-between"
              >
                <span>Konsultasi Jalur Ini</span>
                <ArrowRight class="w-4 h-4" />
              </Button>
            </div>
          </div>
        </div>

        <!-- Curriculum Framework Summary -->
        <div class="sarangenge-bento__cell sarangenge-bento__cell--navy !p-10 sm:!p-12 text-center rounded-[var(--sarangenge-radius-lg,1.5rem)] space-y-4">
          <h3 class="text-2xl sm:text-3xl font-extrabold text-white font-heading">
            Pendampingan Sukses Menembus PTN Top & Kampus Luar Negeri
          </h3>
          <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto leading-relaxed">
            Setiap siswa mendapatkan pemetaan minat karir (*Talent Mapping*), simulasi tryout berkala, serta klinik konsultasi pemilihan jurusan bersama guru BK dan psikolog pendidikan.
          </p>
          <div class="pt-4">
            <Button
              as="router-link"
              to="/contact"
              variant="primary"
              size="md"
              class="!bg-amber-500 hover:!bg-amber-400 !text-slate-950 font-bold shadow-lg border-none"
            >
              Daftar PPDB / Tanya Konselor
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
import { BookOpen, Compass, Globe, Cpu, HeartHandshake, Check, ArrowRight } from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { displaySchoolName } = useSarangengeIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('solusi');

useThemeHashScroll(128);

const academicTracks = [
  {
    icon: Compass,
    badge: 'MIPA & Riset',
    title: 'Kelas Unggulan Sains & Riset Ilmiah (STEM)',
    description: 'Dirancang bagi siswa dengan minat kuat di bidang matematika, kedokteran, bioteknologi, dan teknik.',
    points: [
      'Praktikum laboratorium intensif dan pembuatan karya tulis ilmiah',
      'Pelatihan terstruktur olimpiade sains (OSN & internasional)',
      'Kerjasama penelitian bersama perguruan tinggi ternama',
    ],
  },
  {
    icon: Globe,
    badge: 'International Track',
    title: 'Kelas Bilingual & Kurikulum Cambridge',
    description: 'Pengantar bahasa Inggris dalam mata pelajaran sains dan matematika dengan standar sertifikasi internasional.',
    points: [
      'Persiapan ujian Cambridge IGCSE & AS/A Level',
      'Pembiasaan bahasa Inggris aktif di lingkungan kelas',
      'Bimbingan portofolio dan esai beasiswa kuliah luar negeri',
    ],
  },
  {
    icon: Cpu,
    badge: 'Teknologi & Digital',
    title: 'Peminatan AI, Coding & Robotika Terapan',
    description: 'Membekali siswa keterampilan rekayasa komputasi modern, kecerdasan buatan, dan literasi data.',
    points: [
      'Pemrograman Python, web development, dan dasar Machine Learning',
      'Perakitan mikrokontroler, IoT, dan robotika kompetisi',
      'Pengembangan proyek digital berbasis pemecahan masalah sosial',
    ],
  },
  {
    icon: HeartHandshake,
    badge: 'Karakter & Kepemimpinan',
    title: 'Kelas Tahfidz Quran & Islamic Leadership',
    description: 'Menumbuhkan kecintaan pada Al-Quran, penguatan adab islami, serta kepemimpinan yang berakhlak mulia.',
    points: [
      'Target hafalan Quran mutqin 3 hingga 5 juz bersanad',
      'Kajian adab, tarbiyah, dan pembiasaan ibadah harian',
      'Latihan kepemimpinan organisasi kesiswaan dan pengabdian masyarakat',
    ],
  },
];
</script>

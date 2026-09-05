<template>
  <section
    data-ja-customizer-target="bento"
    class="py-12 sm:py-14"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <div class="max-w-2xl mb-12 space-y-3">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary">
          <Layers class="w-3.5 h-3.5" />
          Akses Cepat Kampus
        </span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
          {{ bentoTitle }}
        </h2>
        <p class="text-muted-foreground text-base sm:text-lg leading-relaxed">
          {{ bentoSubtitle }}
        </p>
      </div>

      <!-- Bento Grid -->
      <div class="sarangenge-bento">
        <!-- 1. PPDB Cell (Wide Navy) -->
        <router-link
          to="/contact"
          class="sarangenge-bento__cell sarangenge-bento__cell--navy sarangenge-bento__wide group"
        >
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="px-3 py-1 rounded-full text-[11px] font-black bg-amber-500 text-slate-950 uppercase tracking-wider shadow-sm">
                PPDB 2026/2027
              </span>
              <ArrowUpRight class="w-5 h-5 opacity-70 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" />
            </div>
            <h3 class="text-xl sm:text-2xl font-bold font-heading text-white">
              {{ t('pages.home.tilePpdb', 'Penerimaan Peserta Didik Baru') }}
            </h3>
            <p class="text-sm text-slate-300 max-w-md leading-relaxed">
              {{ t('pages.home.tilePpdbDesc', 'Jalur Prestasi, Reguler, dan Afirmasi. Ketahui persyaratan, jadwal tes, dan formulir pendaftaran.') }}
            </p>
          </div>
          <div class="pt-4 flex items-center gap-2 text-xs font-bold text-amber-300">
            <span>Daftar Sekarang</span>
            <ArrowRight class="w-4 h-4" />
          </div>
        </router-link>

        <!-- 2. Programs & Curriculum (Tall Gold) -->
        <router-link
          to="/programs"
          class="sarangenge-bento__cell sarangenge-bento__cell--gold sarangenge-bento__tall group"
        >
          <div class="space-y-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-900 dark:text-amber-200 flex items-center justify-center">
              <BookOpen class="w-5 h-5" />
            </div>
            <h3 class="text-xl font-bold font-heading">
              {{ t('pages.home.tileProgram', 'Program Unggulan & Kurikulum') }}
            </h3>
            <p class="text-sm leading-relaxed opacity-90">
              {{ t('pages.home.tileProgramDesc', 'Kurikulum Merdeka berbasis riset, bilingual track, STEM & coding lab, serta program tahfidz & karakter mulia.') }}
            </p>
          </div>
          <div class="flex items-center justify-between text-xs font-bold pt-4">
            <span>Lihat Kurikulum</span>
            <ArrowUpRight class="w-4 h-4 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" />
          </div>
        </router-link>

        <!-- 3. Prestasi & Penghargaan -->
        <router-link
          to="/achievement"
          class="sarangenge-bento__cell group"
        >
          <div class="space-y-2">
            <div class="w-9 h-9 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
              <Award class="w-5 h-5" />
            </div>
            <h3 class="text-lg font-bold text-foreground font-heading">
              {{ t('pages.home.tilePrestasi', 'Prestasi Siswa') }}
            </h3>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {{ t('pages.home.tilePrestasiDesc', 'Juara Olimpiade Sains, Robotika Nasional, Olahraga & Seni.') }}
            </p>
          </div>
          <ArrowUpRight class="w-4 h-4 self-end text-muted-foreground group-hover:text-primary transition-colors" />
        </router-link>

        <!-- 4. Fasilitas Kampus -->
        <router-link
          to="/facilities"
          class="sarangenge-bento__cell group"
        >
          <div class="space-y-2">
            <div class="w-9 h-9 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
              <Building2 class="w-5 h-5" />
            </div>
            <h3 class="text-lg font-bold text-foreground font-heading">
              {{ t('header.services', 'Fasilitas Kampus') }}
            </h3>
            <p class="text-xs text-muted-foreground leading-relaxed">
              Smart Classroom, Lab Komputer AI, Perpustakaan Digital & Sport Arena.
            </p>
          </div>
          <ArrowUpRight class="w-4 h-4 self-end text-muted-foreground group-hover:text-primary transition-colors" />
        </router-link>

        <!-- 5. Guru & Tenaga Pendidik -->
        <router-link
          to="/tim"
          class="sarangenge-bento__cell group"
        >
          <div class="space-y-2">
            <div class="w-9 h-9 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
              <Users class="w-5 h-5" />
            </div>
            <h3 class="text-lg font-bold text-foreground font-heading">
              {{ t('header.tim', 'Dewan Guru & Staf') }}
            </h3>
            <p class="text-xs text-muted-foreground leading-relaxed">
              Pendidik berdedikasi tinggi dengan kualifikasi magister & sertifikasi.
            </p>
          </div>
          <ArrowUpRight class="w-4 h-4 self-end text-muted-foreground group-hover:text-primary transition-colors" />
        </router-link>

        <!-- 6. Berita & Agenda (Wide Cell) -->
        <router-link
          to="/blog"
          class="sarangenge-bento__cell sarangenge-bento__wide group"
        >
          <div class="space-y-2">
            <div class="flex items-center gap-2 text-xs font-bold text-primary">
              <Newspaper class="w-4 h-4" />
              <span>Kabar Sekolah</span>
            </div>
            <h3 class="text-lg sm:text-xl font-bold text-foreground font-heading">
              {{ t('pages.home.tileBerita', 'Berita, Pengumuman & Agenda Akademik') }}
            </h3>
            <p class="text-xs sm:text-sm text-muted-foreground leading-relaxed">
              {{ t('pages.home.tileBeritaDesc', 'Pantau aktivitas terkini, kalender akademik, edaran resmi, dan liputan kegiatan kesiswaan.') }}
            </p>
          </div>
          <div class="pt-3 flex items-center justify-between text-xs font-bold text-primary">
            <span>Buka Berita Sekolah</span>
            <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
          </div>
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Layers, ArrowUpRight, ArrowRight, BookOpen, Award, Building2, Users, Newspaper } from 'lucide-vue-next';
import { useTheme } from '@/modules/Layout/composables/useTheme';

const { t } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();

const bentoTitle = computed(() => {
  return (getSetting('bento_title', '') as string) || t('pages.home.bentoTitle', 'Akses Cepat Kampus');
});

const bentoSubtitle = computed(() => {
  return (getSetting('bento_subtitle', '') as string) || t('pages.home.bentoSubtitle', 'Navigasi penting untuk orang tua, calon siswa, dan peserta didik dalam satu kanvas modern.');
});
</script>

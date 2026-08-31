<template>
  <section
    data-ja-customizer-target="news"
    class="py-16 sm:py-24 bg-muted/20 border-t border-border/60"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col md:flex-row md:items-end justify-between mb-14 gap-6">
        <div class="max-w-2xl space-y-3">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
            <Calendar class="w-3.5 h-3.5" />
            Agenda & Kabar Terkini
          </span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-foreground font-heading tracking-tight">
            {{ announcementsTitle }}
          </h2>
          <p class="text-muted-foreground text-base sm:text-lg leading-relaxed">
            {{ announcementsSubtitle }}
          </p>
        </div>

        <Button
          as="router-link"
          to="/blog"
          variant="outline"
          size="md"
          class="self-start md:self-auto shrink-0"
        >
          {{ t('common.viewAll', 'Lihat Semua Agenda') }}
          <ArrowRight class="w-4 h-4 ml-1" />
        </Button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Event 1 -->
        <div class="sarangenge-panel p-6 flex flex-col justify-between hover:shadow-lg transition-all duration-300">
          <div class="space-y-3">
            <div class="flex items-center gap-3">
              <div class="px-3 py-1.5 rounded-xl bg-[var(--sarangenge-sun,#e8a317)]/20 text-amber-900 dark:text-amber-200 font-extrabold text-center text-xs">
                <div class="text-base font-black leading-none">
                  15
                </div>
                <div class="text-[9px] uppercase tracking-wider">
                  Okt
                </div>
              </div>
              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--sarangenge-teal,#0f766e)] block">
                  PPDB 2026
                </span>
                <span class="text-xs text-muted-foreground">Gelombang 1 Dibuka</span>
              </div>
            </div>
            <h3 class="text-lg font-bold text-foreground font-heading">
              Pembukaan Pendaftaran Siswa Baru Jalur Prestasi & Tes Akademik
            </h3>
            <p class="text-xs text-muted-foreground leading-relaxed">
              Pendaftaran daring melalui portal resmi sekolah dengan diskon biaya pendidikan bagi peraih medali olimpiade.
            </p>
          </div>
          <div class="pt-4 mt-4 border-t border-border/60 flex items-center justify-between text-xs text-muted-foreground">
            <span>Aula Utama & Online</span>
            <router-link
              to="/contact"
              class="font-bold text-[var(--sarangenge-teal,#0f766e)] hover:underline"
            >
              Info Syarat →
            </router-link>
          </div>
        </div>

        <!-- Event 2 -->
        <div class="sarangenge-panel p-6 flex flex-col justify-between hover:shadow-lg transition-all duration-300">
          <div class="space-y-3">
            <div class="flex items-center gap-3">
              <div class="px-3 py-1.5 rounded-xl bg-[var(--sarangenge-teal,#0f766e)]/15 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200 font-extrabold text-center text-xs">
                <div class="text-base font-black leading-none">
                  28
                </div>
                <div class="text-[9px] uppercase tracking-wider">
                  Okt
                </div>
              </div>
              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--sarangenge-teal,#0f766e)] block">
                  Pameran Karya
                </span>
                <span class="text-xs text-muted-foreground">P5 Expo 2026</span>
              </div>
            </div>
            <h3 class="text-lg font-bold text-foreground font-heading">
              Sarangenge Science, Tech & Cultural Exhibition
            </h3>
            <p class="text-xs text-muted-foreground leading-relaxed">
              Unjuk gelar proyek penelitian sains, demo robotika AI, dan pameran seni budaya nusantara karya seluruh siswa.
            </p>
          </div>
          <div class="pt-4 mt-4 border-t border-border/60 flex items-center justify-between text-xs text-muted-foreground">
            <span>Sport Hall & Galeri</span>
            <router-link
              to="/blog"
              class="font-bold text-[var(--sarangenge-teal,#0f766e)] hover:underline"
            >
              Jadwal Acara →
            </router-link>
          </div>
        </div>

        <!-- Event 3 -->
        <div class="sarangenge-panel p-6 flex flex-col justify-between hover:shadow-lg transition-all duration-300">
          <div class="space-y-3">
            <div class="flex items-center gap-3">
              <div class="px-3 py-1.5 rounded-xl bg-[var(--sarangenge-sun,#e8a317)]/20 text-amber-900 dark:text-amber-200 font-extrabold text-center text-xs">
                <div class="text-base font-black leading-none">
                  10
                </div>
                <div class="text-[9px] uppercase tracking-wider">
                  Nov
                </div>
              </div>
              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--sarangenge-teal,#0f766e)] block">
                  Parenting
                </span>
                <span class="text-xs text-muted-foreground">Seminar Orang Tua</span>
              </div>
            </div>
            <h3 class="text-lg font-bold text-foreground font-heading">
              Parent-Teacher Synergy: Mendampingi Remaja Era Digital
            </h3>
            <p class="text-xs text-muted-foreground leading-relaxed">
              Diskusi interaktif bersama pakar psikologi pendidikan tentang kesehatan mental dan fokus belajar generasi Alpha.
            </p>
          </div>
          <div class="pt-4 mt-4 border-t border-border/60 flex items-center justify-between text-xs text-muted-foreground">
            <span>Auditorium & Zoom</span>
            <router-link
              to="/blog"
              class="font-bold text-[var(--sarangenge-teal,#0f766e)] hover:underline"
            >
              Registrasi →
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Calendar, ArrowRight } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useTheme } from '@/modules/Layout/composables/useTheme';

const { t } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();

const announcementsTitle = computed(() => {
  return (getSetting('announcements_title', '') as string) || t('pages.home.tileBerita', 'Agenda & Kabar Sekolah Terkini');
});

const announcementsSubtitle = computed(() => {
  return (getSetting('announcements_subtitle', '') as string) || t('pages.home.tileBeritaDesc', 'Pantau aktivitas resmi, kalender akademik, jadwal asesmen, dan siaran pers sekolah.');
});
</script>

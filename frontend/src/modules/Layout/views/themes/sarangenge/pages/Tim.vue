<template>
  <div
    data-ja-customizer-target="tim"
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
          <Breadcrumb :items="[{ name: t('pages.tim.title', 'Guru & Staf') }]" />
          <div class="max-w-3xl space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
              <Users class="w-3.5 h-3.5" />
              Pendidik Berdedikasi
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
              {{ t('pages.tim.title', 'Dewan Guru & Tenaga Kependidikan') }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
              {{ t('pages.tim.subtitle', 'Didukung oleh pendidik profesional berkualifikasi sarjana & magister dengan sertifikasi pendidik nasional serta pengajar bersertifikat Cambridge.') }}
            </p>
          </div>
        </div>

        <!-- Teachers Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          <div
            v-for="(staff, idx) in staffDirectory"
            :key="idx"
            class="sarangenge-panel p-6 text-center space-y-4 hover:border-[var(--sarangenge-teal,#0f766e)]/40 hover:-translate-y-1 transition-all duration-300 group flex flex-col justify-between"
          >
            <div class="space-y-3">
              <div class="w-20 h-20 rounded-2xl bg-slate-900 border border-slate-700/80 p-1 mx-auto shadow-md group-hover:scale-105 transition-transform flex items-center justify-center text-amber-400 font-extrabold text-xl">
                {{ staffInitials(staff.name) }}
              </div>

              <div>
                <h3 class="text-base font-bold text-foreground font-heading">
                  {{ staff.name }}
                </h3>
                <span class="text-xs font-bold text-[var(--sarangenge-teal,#0f766e)] block">
                  {{ staff.role }}
                </span>
                <span class="text-[11px] text-muted-foreground block mt-0.5">
                  {{ staff.subject }}
                </span>
              </div>
            </div>

            <div class="pt-3 border-t border-border/60 text-[11px] text-muted-foreground">
              {{ staff.education }}
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
import { Users } from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { displaySchoolName } = useSarangengeIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('tim');

const staffInitials = (name: string) => {
  const parts = name.trim().split(/\s+/).filter(Boolean);
  const first = parts[0]?.charAt(0) ?? '';
  const second = parts[1]?.charAt(0) ?? '';
  return `${first}${second}`;
};

const staffDirectory = [
  {
    name: 'Drs. H. Rahmat Sudrajat, M.Pd.',
    role: 'Kepala Sekolah',
    subject: 'Manajemen Pendidikan',
    education: 'S2 Manajemen Pendidikan UPI',
  },
  {
    name: 'Dra. Hj. Nurul Hidayati, M.Si.',
    role: 'Wakil Kepala Sekolah',
    subject: 'Bidang Kurikulum & Akademik',
    education: 'S2 Ilmu Kimia ITB',
  },
  {
    name: 'Bambang Irawan, S.Kom., M.T.',
    role: 'Koordinator STEM & Lab AI',
    subject: 'Informatika & Robotika',
    education: 'S2 Teknik Elektro ITB',
  },
  {
    name: 'Sarah Jenkins, B.Ed., M.A.',
    role: 'Cambridge Coordinator',
    subject: 'English & Literature',
    education: 'University of Cambridge (UK)',
  },
  {
    name: 'Ustadz Ahmad Fauzi, Lc., M.Ag.',
    role: 'Koordinator Keagamaan',
    subject: 'Pendidikan Agama Islam & Tahfidz',
    education: 'Universitas Al-Azhar Kairo',
  },
  {
    name: 'Dewi Sartika, S.Pd., Gr.',
    role: 'Guru Pembina Olimpiade',
    subject: 'Matematika Terapan',
    education: 'S1 Pendidikan Matematika UPI',
  },
  {
    name: 'Rudi Hartono, S.Or.',
    role: 'Kepala Bagian Kesiswaan',
    subject: 'Pendidikan Jasmani & Olahraga',
    education: 'S1 Ilmu Keolahragaan UNJ',
  },
  {
    name: 'Laksmi Paramita, S.Psi., M.Psi.',
    role: 'Koordinator BK & Konseling',
    subject: 'Bimbingan Karir & Psikologi',
    education: 'S2 Psikologi Profesi UNPAD',
  },
];
</script>

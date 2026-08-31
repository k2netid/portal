<template>
  <div
    data-ja-customizer-target="alumni"
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
          <Breadcrumb :items="[{ name: t('pages.career_center.title', 'Alumni & Karir') }]" />
          <div class="max-w-3xl space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-teal,#0f766e)]/10 text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-200">
              <GraduationCap class="w-3.5 h-3.5" />
              Jejaring Alumni & Masa Depan
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
              {{ t('pages.career_center.title', 'Jejaring Alumni & Bimbingan Studi') }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
              {{ t('pages.career_center.subtitle', 'Menjaga ikatan kekeluargaan alumni Sarangenge di seluruh dunia serta memfasilitasi bimbingan karir dan beasiswa bagi siswa.') }}
            </p>
          </div>
        </div>

        <!-- Alumni Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
          <div class="sarangenge-panel p-6 space-y-1">
            <div class="text-3xl sm:text-4xl font-black font-heading text-[var(--sarangenge-teal,#0f766e)]">
              98.4%
            </div>
            <div class="text-xs sm:text-sm text-muted-foreground font-semibold">
              Tembus PTN Top & Luar Negeri
            </div>
          </div>

          <div class="sarangenge-panel p-6 space-y-1">
            <div class="text-3xl sm:text-4xl font-black font-heading text-[var(--sarangenge-sun,#e8a317)]">
              2.500+
            </div>
            <div class="text-xs sm:text-sm text-muted-foreground font-semibold">
              Alumni Tersebar Global
            </div>
          </div>

          <div class="sarangenge-panel p-6 space-y-1">
            <div class="text-3xl sm:text-4xl font-black font-heading text-[var(--sarangenge-teal,#0f766e)]">
              45+
            </div>
            <div class="text-xs sm:text-sm text-muted-foreground font-semibold">
              Mitra Universitas & Industri
            </div>
          </div>

          <div class="sarangenge-panel p-6 space-y-1">
            <div class="text-3xl sm:text-4xl font-black font-heading text-[var(--sarangenge-sun,#e8a317)]">
              100+
            </div>
            <div class="text-xs sm:text-sm text-muted-foreground font-semibold">
              Penerima Beasiswa Kuliah
            </div>
          </div>
        </div>

        <!-- Alumni Spotlight -->
        <div class="space-y-6">
          <h2 class="text-2xl font-bold text-foreground font-heading">
            Kisah Sukses & Sorotan Alumni
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div
              v-for="(alumni, idx) in alumniStories"
              :key="idx"
              class="sarangenge-panel p-8 space-y-4 flex flex-col justify-between"
            >
              <div class="space-y-3">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-base shadow-inner">
                    {{ alumni.name.charAt(0) }}
                  </div>
                  <div>
                    <h3 class="text-base font-bold text-foreground font-heading">
                      {{ alumni.name }}
                    </h3>
                    <p class="text-xs text-[var(--sarangenge-teal,#0f766e)] font-semibold">
                      {{ alumni.grad }} · {{ alumni.campus }}
                    </p>
                  </div>
                </div>
                <p class="text-xs text-muted-foreground leading-relaxed italic">
                  "{{ alumni.story }}"
                </p>
              </div>

              <div class="pt-4 border-t border-border/60 text-xs font-bold text-foreground/80">
                <span>{{ alumni.role }}</span>
              </div>
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
import { GraduationCap } from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { displaySchoolName } = useSarangengeIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('career_center');

const alumniStories = [
  {
    name: 'dr. Farhan Maulana',
    grad: 'Alumni 2018',
    campus: 'Fakultas Kedokteran UI',
    role: 'Dokter Residen & Peneliti Medis',
    story: 'Pendidikan disiplin riset dan laboratorium biologi di Sarangenge meletakkan pondasi kuat bagi karir kedokteran saya.',
  },
  {
    name: 'Annisa Larasati, S.T., M.Sc.',
    grad: 'Alumni 2019',
    campus: 'TU Delft (Belanda)',
    role: 'AI Engineer di Perusahaan Teknologi',
    story: 'Dukungan klub coding sekolah dan bimbingan guru bahasa Inggris mempermudah langkah saya meraih beasiswa master di Eropa.',
  },
  {
    name: 'Dimas Wicaksono, S.E.',
    grad: 'Alumni 2020',
    campus: 'Fakultas Ekonomika dan Bisnis UGM',
    role: 'Co-Founder Startup Agritech',
    story: 'Jiwa kepemimpinan dan empati sosial yang ditanamkan selama bersekolah menjadi kompas utama dalam mendirikan usaha.',
  },
];
</script>

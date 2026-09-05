<template>
  <SarangengePageGate
    setting-key="enable_career"
    :title="t('pages.career_center.title', 'Alumni & Karir')"
  >
    <div
      data-ja-customizer-target="alumni"
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
                {{ t('pages.career_center.subtitle', { school: displaySchoolName }, `Menjaga ikatan kekeluargaan alumni ${displaySchoolName} di seluruh dunia serta memfasilitasi bimbingan karir dan beasiswa bagi siswa.`) }}
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
                Mitra Perguruan Tinggi
              </div>
            </div>

            <div class="sarangenge-panel p-6 space-y-1">
              <div class="text-3xl sm:text-4xl font-black font-heading text-[var(--sarangenge-sun,#e8a317)]">
                100%
              </div>
              <div class="text-xs sm:text-sm text-muted-foreground font-semibold">
                Konseling Karir Terpadu
              </div>
            </div>
          </div>

          <!-- Alumni Highlights -->
          <div class="space-y-6">
            <h2 class="text-2xl font-bold text-foreground font-heading">
              Kisah Sukses Alumni
            </h2>

            <!-- Loading Spinner -->
            <div v-if="loading" class="min-h-[200px] flex items-center justify-center">
              <div class="w-8 h-8 rounded-full border-2 border-[var(--sarangenge-teal,#0f766e)] border-t-transparent animate-spin" />
            </div>

            <div v-else-if="resolvedStories.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              <router-link
                v-for="(alumni, idx) in resolvedStories"
                :key="alumni.id || idx"
                :to="alumni.slug ? `/blog/${alumni.slug}` : '#'"
                class="sarangenge-panel p-8 space-y-4 flex flex-col justify-between hover:border-[var(--sarangenge-teal,#0f766e)]/40 transition-all duration-300 group cursor-pointer block text-left"
              >
                <div class="space-y-3">
                  <div class="w-14 h-14 rounded-2xl bg-slate-900 border border-slate-700/80 p-1 flex items-center justify-center text-amber-400 font-extrabold text-lg">
                    {{ alumni.name.charAt(0) }}
                  </div>
                  <div>
                    <h3 class="text-base font-bold text-foreground font-heading group-hover:text-[var(--sarangenge-teal,#0f766e)] transition-colors">
                      {{ alumni.name }}
                    </h3>
                    <span class="text-xs font-bold text-[var(--sarangenge-teal,#0f766e)] block">
                      {{ alumni.grad }} • {{ alumni.campus }}
                    </span>
                    <span class="text-xs text-muted-foreground block mt-0.5">
                      {{ alumni.role }}
                    </span>
                  </div>
                  <p class="text-sm text-muted-foreground italic leading-relaxed pt-2 line-clamp-4">
                    "{{ alumni.story }}"
                  </p>
                </div>

                <div class="pt-4 border-t border-border/60 flex items-center justify-between text-xs">
                  <span class="text-muted-foreground font-medium">SMK Pusat Keunggulan</span>
                  <span v-if="alumni.slug" class="font-bold text-[var(--sarangenge-teal,#0f766e)] group-hover:underline flex items-center gap-0.5">
                    Kisah Lengkap →
                  </span>
                </div>
              </router-link>
            </div>

            <div v-else class="sarangenge-panel p-10 text-center text-muted-foreground space-y-3">
              <p class="text-base font-semibold text-foreground">
                {{ t('pages.career_center.noData', 'Data kisah sukses alumni belum tersedia.') }}
              </p>
            </div>
          </div>
        </div>
      </template>
    </div>
  </SarangengePageGate>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '@/modules/Layout/views/themes/sarangenge/components/shared/Breadcrumb.vue';
import SarangengePageGate from '@/modules/Layout/views/themes/sarangenge/components/shared/SarangengePageGate.vue';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import type { Content } from '@/modules/Publishing/types/content';
import { GraduationCap } from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { displaySchoolName } = useSarangengeIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('career_center');

const storiesList = ref<Content[]>([]);
const loading = ref(true);

const defaultAlumniStories = computed(() => [
  {
    id: 'story-1',
    slug: 'farhan-maulana-karir-alumni',
    name: 'dr. Farhan Maulana',
    grad: 'Alumni 2018',
    campus: 'Fakultas Kedokteran UI',
    role: 'Dokter Residen & Peneliti Medis',
    story: `Pendidikan disiplin riset dan laboratorium di ${displaySchoolName.value} meletakkan pondasi kuat bagi karir profesional saya.`,
  },
  {
    id: 'story-2',
    slug: 'annisa-larasati-ai-engineer-alumni',
    name: 'Annisa Larasati, S.T., M.Sc.',
    grad: 'Alumni 2019',
    campus: 'TU Delft (Belanda)',
    role: 'AI Engineer di Perusahaan Teknologi Global',
    story: 'Dukungan klub coding sekolah dan bimbingan guru bahasa mempermudah langkah saya meraih beasiswa master di Eropa.',
  },
  {
    id: 'story-3',
    slug: 'dimas-wicaksono-founder-agritech',
    name: 'Dimas Wicaksono, S.E.',
    grad: 'Alumni 2020',
    campus: 'Fakultas Ekonomika dan Bisnis UGM',
    role: 'Co-Founder Startup Agritech',
    story: 'Jiwa kepemimpinan dan empati sosial yang ditanamkan selama bersekolah menjadi kompas utama dalam mendirikan usaha mandiri.',
  },
]);

const resolvedStories = computed(() => {
  if (storiesList.value.length > 0) {
    return storiesList.value.map((item: any) => {
      const raw = item._raw || item;
      const meta = raw.meta || {};
      return {
        id: item.id,
        slug: item.slug || '',
        name: meta.name || item.title.split('—')[0]?.trim() || item.title,
        grad: meta.grad || 'Alumni Vokasi',
        campus: meta.campus || 'Mitra Industri / Kampus',
        role: meta.role || item.excerpt || 'Profesional',
        story: meta.story || item.excerpt || raw.intro || '',
      };
    });
  }

  return defaultAlumniStories.value;
});

onMounted(async () => {
  try {
    const res = await api.get(publishingPaths.publicContents, {
      params: { category: 'karir-alumni', status: 'published', sort: '-created_at' },
    });
    const data = res.data;
    const items = Array.isArray(data)
      ? data
      : Array.isArray(data?.data)
        ? data.data
        : Array.isArray(data?.data?.data)
          ? data.data.data
          : [];
    storiesList.value = items;
  } catch {
    storiesList.value = [];
  } finally {
    loading.value = false;
  }
});
</script>

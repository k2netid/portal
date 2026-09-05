<template>
  <SarangengePageGate
    setting-key="enable_achievement"
    :title="achievementsTitle"
  >
    <div
      data-ja-customizer-target="achievements"
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
            <Breadcrumb :items="[{ name: t('pages.achievement.title', 'Prestasi Sekolah') }]" />
            <div class="max-w-3xl space-y-3">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[var(--sarangenge-sun,#e8a317)]/15 text-amber-800 dark:text-amber-200 border border-[var(--sarangenge-sun)]/30">
                <Trophy class="w-3.5 h-3.5" />
                {{ t('pages.achievement.badge', 'Jejak Keunggulan & Penghargaan') }}
              </span>
              <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
                {{ achievementsTitle }}
              </h1>
              <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
                {{ achievementsSubtitle }}
              </p>
            </div>
          </div>

          <!-- Filter category tab bar -->
          <div class="flex flex-wrap gap-2 pb-2">
            <button
              v-for="tab in filterTabs"
              :key="tab.id"
              type="button"
              class="px-4 py-2 rounded-[var(--sarangenge-radius-sm)] text-xs font-bold transition-colors"
              :class="activeTab === tab.id
                ? 'bg-[var(--sarangenge-teal,#0f766e)] text-white shadow-md'
                : 'border border-border/80 text-muted-foreground hover:text-foreground hover:bg-muted/60'"
              @click="activeTab = tab.id"
            >
              {{ tab.name }}
            </button>
          </div>

          <!-- Loading Spinner -->
          <div v-if="loading && !hasBinding" class="min-h-[250px] flex items-center justify-center">
            <div class="w-8 h-8 rounded-full border-2 border-[var(--sarangenge-teal,#0f766e)] border-t-transparent animate-spin" />
          </div>

          <!-- Achievements List Grid -->
          <div v-else-if="filteredAchievements.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <router-link
              v-for="(ach, idx) in filteredAchievements"
              :key="ach.id || idx"
              :to="ach.slug ? `/blog/${ach.slug}` : '#'"
              class="sarangenge-panel p-8 space-y-4 flex flex-col justify-between hover:border-[var(--sarangenge-teal,#0f766e)]/40 hover:shadow-xl transition-all duration-300 group cursor-pointer block text-left"
            >
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-[var(--sarangenge-teal,#0f766e)] text-white shadow-sm">
                    {{ ach.year }}
                  </span>
                  <span class="text-xs font-bold text-amber-600 dark:text-amber-300 flex items-center gap-1">
                    <Medal class="w-4 h-4" />
                    {{ ach.level }}
                  </span>
                </div>

                <h3 class="text-xl font-bold text-foreground font-heading group-hover:text-[var(--sarangenge-teal,#0f766e)] transition-colors">
                  {{ ach.title }}
                </h3>

                <p class="text-sm text-muted-foreground leading-relaxed line-clamp-3">
                  {{ ach.description }}
                </p>
              </div>

              <div class="pt-4 mt-4 border-t border-border/60 flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs shadow-inner">
                    {{ (ach.student || 'S').charAt(0) }}
                  </div>
                  <div>
                    <div class="text-xs font-bold text-foreground">
                      {{ ach.student }}
                    </div>
                    <div class="text-[11px] text-muted-foreground">
                      {{ ach.categoryName }}
                    </div>
                  </div>
                </div>
                <span v-if="ach.slug" class="text-xs font-medium text-[var(--sarangenge-teal,#0f766e)] group-hover:underline flex items-center gap-0.5">
                  Detail →
                </span>
              </div>
            </router-link>
          </div>

          <div v-else class="sarangenge-panel p-10 text-center text-muted-foreground space-y-3">
            <p class="text-base font-semibold text-foreground">
              {{ t('pages.achievement.noData', 'Belum ada data prestasi untuk kategori ini.') }}
            </p>
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
import { useThemeDataBindings } from '@/modules/Layout/composables/useThemeDataBindings';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '@/modules/Layout/views/themes/sarangenge/components/shared/Breadcrumb.vue';
import SarangengePageGate from '@/modules/Layout/views/themes/sarangenge/components/shared/SarangengePageGate.vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import type { Content } from '@/modules/Publishing/types/content';
import { Trophy, Medal } from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();
const { displaySchoolName } = useSarangengeIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('achievement');

const { data: dynamicAchievements, hasBinding } = useThemeDataBindings('achievements', 'list');

const achievements = ref<Content[]>([]);
const loading = ref(true);
const activeTab = ref('all');

const filterTabs = [
  { id: 'all', name: 'Semua Prestasi' },
  { id: 'sains', name: 'Sains & Kejuruan' },
  { id: 'robotika', name: 'Robotika & IT' },
  { id: 'bahasa', name: 'Bahasa & Seni' },
  { id: 'olahraga', name: 'Olahraga' },
];

const achievementsTitle = computed(() => {
  return (getSetting('achievements_title', '') as string) || t('pages.achievement.title', { school: displaySchoolName.value }, `Prestasi & Penghargaan Sivitas ${displaySchoolName.value}`);
});

const achievementsSubtitle = computed(() => {
  return (getSetting('achievements_subtitle', '') as string) || t('pages.achievement.subtitle', 'Rangkuman prestasi kejuaraan akademik, sains, teknologi, seni budaya, dan olahraga di kancah nasional maupun internasional.');
});

const defaultAchievements = computed(() => [
  {
    id: 'default-1',
    slug: 'medali-emas-lks-nasional-cad-building',
    year: '2026',
    level: 'Tingkat Nasional',
    category: 'sains',
    categoryName: 'LKS SMK Nasional',
    title: 'Medali Emas LKS Tingkat Nasional Bidang CAD Building',
    description: 'Menyabet medali emas dengan skor tertinggi dalam desain permodelan konstruksi 3D dan Building Information Modeling.',
    student: 'Ahmad Fadhil Prasetya',
  },
  {
    id: 'default-2',
    slug: 'juara-1-national-robotics-ai-championship',
    year: '2026',
    level: 'Tingkat Nasional',
    category: 'robotika',
    categoryName: 'Kontes Robotika Nasional',
    title: 'Juara 1 National Autonomous Robotics & AI Championship',
    description: 'Inovasi robot penyortir otomatis dengan integrasi Computer Vision dan algoritma deep learning.',
    student: `Tim Robotika Alpha ${displaySchoolName.value}`,
  },
  {
    id: 'default-3',
    slug: 'overall-best-speaker-nsdc-debating',
    year: '2026',
    level: 'Tingkat Nasional',
    category: 'bahasa',
    categoryName: 'Debat Bahasa Inggris (NSDC)',
    title: 'Overall Best Speaker National Schools Debating Championship',
    description: 'Menjuarai kompetisi debat parlemen bahasa Inggris tingkat nasional mewakili kontingen provinsi.',
    student: 'Nadia Putri Anindita',
  },
  {
    id: 'default-4',
    slug: 'juara-1-dbl-basketball-league-jabar',
    year: '2025',
    level: 'Tingkat Provinsi',
    category: 'olahraga',
    categoryName: 'Kejuaraan Basket Antarpelajar',
    title: 'Juara 1 DBL Basketball League Jawa Barat',
    description: `Tim basket putra ${displaySchoolName.value} berhasil mempertahankan gelar juara dengan rekor tak terkalahkan.`,
    student: 'Tim Basket Putra',
  },
  {
    id: 'default-5',
    slug: 'medali-perak-osn-fisika-terapan',
    year: '2025',
    level: 'Tingkat Nasional',
    category: 'sains',
    categoryName: 'Olimpiade Sains Nasional (OSN)',
    title: 'Medali Perak OSN Bidang Astronomi & Fisika Terapan',
    description: 'Kompetisi sains bergengsi yang diselenggarakan oleh Pusat Prestasi Nasional Kemendikbudristek.',
    student: 'Muhammad Rizky Ramadhan',
  },
  {
    id: 'default-6',
    slug: 'gold-diploma-international-choir-singapura',
    year: '2025',
    level: 'Tingkat Internasional',
    category: 'bahasa',
    categoryName: 'International Choir Festival',
    title: 'Gold Diploma Choir Championship di Singapura',
    description: 'Paduan suara sekolah mempersembahkan medley lagu daerah Sunda dan musik klasik kontemporer.',
    student: `Paduan Suara Gita ${displaySchoolName.value}`,
  },
]);

const baseAchievements = computed(() => {
  if (hasBinding.value && dynamicAchievements.value && dynamicAchievements.value.length > 0) {
    return dynamicAchievements.value.map((item: any) => {
      const raw = item._raw || item;
      return {
        id: item.id,
        slug: item.slug || '',
        year: raw.meta?.year || (raw.published_at ? new Date(raw.published_at).getFullYear().toString() : '2026'),
        level: raw.meta?.level || 'Tingkat Nasional',
        category: raw.meta?.category || 'sains',
        categoryName: raw.meta?.category_name || item.excerpt || 'Kejuaraan',
        title: item.title,
        description: item.description || item.excerpt || raw.description || '',
        student: raw.meta?.winner || raw.meta?.student || displaySchoolName.value,
      };
    });
  }

  if (achievements.value.length > 0) {
    return achievements.value.map((item: any) => {
      const raw = item._raw || item;
      const meta = raw.meta || {};
      return {
        id: item.id,
        slug: item.slug || '',
        year: meta.year || (raw.published_at ? new Date(raw.published_at).getFullYear().toString() : '2026'),
        level: meta.level || 'Tingkat Nasional',
        category: meta.category || 'sains',
        categoryName: meta.category_name || item.excerpt || 'Kejuaraan',
        title: item.title,
        description: item.excerpt || item.description || raw.intro || '',
        student: meta.winner || meta.student || displaySchoolName.value,
      };
    });
  }

  return defaultAchievements.value;
});

const filteredAchievements = computed(() => {
  if (activeTab.value === 'all') return baseAchievements.value;
  return baseAchievements.value.filter((a) => a.category === activeTab.value);
});

onMounted(async () => {
  if (hasBinding.value) {
    loading.value = false;
    return;
  }
  try {
    const res = await api.get(publishingPaths.publicContents, {
      params: { category: 'prestasi', status: 'published', sort: '-created_at' },
    });
    const data = res.data;
    const items = Array.isArray(data)
      ? data
      : Array.isArray(data?.data)
        ? data.data
        : Array.isArray(data?.data?.data)
          ? data.data.data
          : [];
    achievements.value = items;
  } catch {
    achievements.value = [];
  } finally {
    loading.value = false;
  }
});
</script>

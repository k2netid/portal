<template>
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
              Jejak Keunggulan & Penghargaan
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

        <!-- Achievements List Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="(ach, idx) in filteredAchievements"
            :key="idx"
            class="sarangenge-panel p-8 space-y-4 flex flex-col justify-between hover:border-[var(--sarangenge-teal,#0f766e)]/40 hover:shadow-xl transition-all duration-300 group"
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

              <p class="text-sm text-muted-foreground leading-relaxed">
                {{ ach.description }}
              </p>
            </div>

            <div class="pt-4 mt-4 border-t border-border/60 flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs shadow-inner">
                {{ ach.student.charAt(0) }}
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
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import ThemeSafeHtml from '@/modules/Layout/components/themes/ThemeSafeHtml.vue';
import Breadcrumb from '@/modules/Layout/views/themes/sarangenge/components/shared/Breadcrumb.vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import { Trophy, Medal } from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();
const { displaySchoolName } = useSarangengeIdentity();
const { pageData, cmsBody, builderBlocks, hasBuilderBlocks } = useThemePageOverride('achievement');

const activeTab = ref('all');

const filterTabs = [
  { id: 'all', name: 'Semua Prestasi' },
  { id: 'sains', name: 'Sains & Matematika' },
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

const allAchievements = computed(() => [
  {
    year: '2026',
    level: 'Tingkat Internasional',
    category: 'sains',
    categoryName: 'Olimpiade Sains (IMO)',
    title: 'Medali Emas International Mathematical Olympiad',
    description: 'Menyabet medali emas dengan nilai sempurna di babak final olimpiade matematika dunia.',
    student: 'Ahmad Fadhil Prasetya',
  },
  {
    year: '2026',
    level: 'Tingkat Nasional',
    category: 'robotika',
    categoryName: 'Kontes Robotika Nasional',
    title: 'Juara 1 National Autonomous Robotics & AI Championship',
    description: 'Inovasi robot penyortir otomatis dengan integrasi Computer Vision dan algoritma deep learning.',
    student: `Tim Robotika Alpha ${displaySchoolName.value}`,
  },
  {
    year: '2026',
    level: 'Tingkat Nasional',
    category: 'bahasa',
    categoryName: 'Debat Bahasa Inggris (NSDC)',
    title: 'Overall Best Speaker National Schools Debating Championship',
    description: 'Menjuarai kompetisi debat parlemen bahasa Inggris tingkat nasional mewakili kontingen provinsi.',
    student: 'Nadia Putri Anindita',
  },
  {
    year: '2025',
    level: 'Tingkat Provinsi',
    category: 'olahraga',
    categoryName: 'Kejuaraan Basket Antarpelajar',
    title: 'Juara 1 DBL Basketball League Jawa Barat',
    description: `Tim basket putra ${displaySchoolName.value} berhasil mempertahankan gelar juara dengan rekor tak terkalahkan.`,
    student: 'Tim Basket Putra',
  },
  {
    year: '2025',
    level: 'Tingkat Nasional',
    category: 'sains',
    categoryName: 'Olimpiade Sains Nasional (OSN)',
    title: 'Medali Perak OSN Bidang Astronomi & Fisika Terapan',
    description: 'Kompetisi sains bergengsi yang diselenggarakan oleh Pusat Prestasi Nasional Kemendikbudristek.',
    student: 'Muhammad Rizky Ramadhan',
  },
  {
    year: '2025',
    level: 'Tingkat Internasional',
    category: 'bahasa',
    categoryName: 'International Choir Festival',
    title: 'Gold Diploma Choir Championship di Singapura',
    description: 'Paduan suara sekolah mempersembahkan medley lagu daerah Sunda dan musik klasik kontemporer.',
    student: `Paduan Suara Gita ${displaySchoolName.value}`,
  },
]);

const filteredAchievements = computed(() => {
  if (activeTab.value === 'all') return allAchievements.value;
  return allAchievements.value.filter((a) => a.category === activeTab.value);
});
</script>

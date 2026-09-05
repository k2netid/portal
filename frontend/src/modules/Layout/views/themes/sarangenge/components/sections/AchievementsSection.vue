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
        <router-link
          v-for="(item, idx) in resolvedAchievements"
          :key="item.id || idx"
          :to="item.slug ? `/blog/${item.slug}` : '/achievement'"
          class="sarangenge-panel p-6 space-y-4 hover:border-[var(--sarangenge-sun,#e8a317)]/40 hover:shadow-xl transition-all duration-300 flex flex-col justify-between group cursor-pointer block text-left"
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

            <h3 class="text-xl font-bold text-foreground font-heading group-hover:text-[var(--sarangenge-teal,#0f766e)] transition-colors">
              {{ item.title }}
            </h3>

            <p class="text-sm text-muted-foreground leading-relaxed line-clamp-3">
              {{ item.description }}
            </p>
          </div>

          <div class="pt-4 border-t border-border/60 flex items-center justify-between">
            <div class="flex items-center gap-3">
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
            <span class="text-xs font-medium text-[var(--sarangenge-teal,#0f766e)] group-hover:underline flex items-center gap-0.5">
              Detail →
            </span>
          </div>
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';
import { Trophy, ArrowRight, Medal } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';

const { t } = useThemeI18n('sarangenge');
const { getSetting } = useTheme();
const { displaySchoolName } = useSarangengeIdentity();

const dynamicAchievements = ref<any[]>([]);
const loading = ref(true);

const achievementsTitle = computed(() => {
  return (getSetting('achievements_title', '') as string) || t('pages.achievement.title', { school: displaySchoolName.value }, `Prestasi & Penghargaan Siswa ${displaySchoolName.value}`);
});

const achievementsSubtitle = computed(() => {
  return (getSetting('achievements_subtitle', '') as string) || t('pages.achievement.subtitle', 'Bukti dedikasi dan keunggulan akademik, sains, seni, dan olahraga di tingkat regional hingga internasional.');
});

const defaultAchievementsList = computed(() => [
  {
    id: 'default-1',
    slug: 'medali-emas-lks-nasional-cad-building',
    year: '2026',
    level: 'Tingkat Nasional',
    title: 'Medali Emas LKS Tingkat Nasional Bidang CAD Building',
    description: 'Menyabet medali emas dengan skor tertinggi dalam desain permodelan konstruksi 3D dan Building Information Modeling.',
    student: 'Ahmad Fadhil Prasetya',
    category: 'LKS SMK Nasional',
  },
  {
    id: 'default-2',
    slug: 'juara-1-national-robotics-ai-championship',
    year: '2026',
    level: 'Tingkat Nasional',
    title: 'Juara 1 National Autonomous Robotics Championship',
    description: 'Inovasi robot penyortir otomatis dengan integrasi Computer Vision dan algoritma deep learning.',
    student: `Tim Robotika Alpha ${displaySchoolName.value}`,
    category: 'Kontes Robotika Nasional',
  },
  {
    id: 'default-3',
    slug: 'overall-best-speaker-nsdc-debating',
    year: '2026',
    level: 'Tingkat Nasional',
    title: 'Overall Best Speaker National Schools Debating Championship',
    description: 'Menjuarai kompetisi debat parlemen bahasa Inggris tingkat nasional mewakili kontingen provinsi.',
    student: 'Nadia Putri Anindita',
    category: 'Debat Bahasa Inggris (NSDC)',
  },
]);

const resolvedAchievements = computed(() => {
  if (dynamicAchievements.value.length > 0) {
    return dynamicAchievements.value.slice(0, 3).map((item: any) => {
      const raw = item._raw || item;
      const meta = raw.meta || {};
      return {
        id: item.id,
        slug: item.slug || '',
        year: meta.year || (raw.published_at ? new Date(raw.published_at).getFullYear().toString() : '2026'),
        level: meta.level || 'Tingkat Nasional',
        title: item.title,
        description: item.excerpt || item.description || raw.intro || '',
        student: meta.winner || meta.student || displaySchoolName.value,
        category: meta.category_name || item.excerpt || 'Kejuaraan',
      };
    });
  }
  return defaultAchievementsList.value;
});

onMounted(async () => {
  try {
    const res = await api.get(publishingPaths.publicContents, {
      params: { category: 'prestasi', status: 'published', limit: 3, sort: '-created_at' },
    });
    const data = res.data;
    const items = Array.isArray(data)
      ? data
      : Array.isArray(data?.data)
        ? data.data
        : Array.isArray(data?.data?.data)
          ? data.data.data
          : [];
    dynamicAchievements.value = items;
  } catch {
    dynamicAchievements.value = [];
  } finally {
    loading.value = false;
  }
});
</script>

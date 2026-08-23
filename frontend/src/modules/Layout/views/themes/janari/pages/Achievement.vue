<template>
  <div class="min-h-screen bg-background">
    <!-- If Enabled -->
    <template v-if="isEnabled">
      <!-- Hero / Title -->
      <section class="py-24 text-center">
        <div class="container mx-auto px-4">
          <div class="inline-flex items-center justify-center p-4 bg-amber-100 rounded-3xl mb-8 group overflow-hidden relative">
            <Trophy class="w-8 h-8 text-amber-600 relative z-10 group-hover:scale-125 transition-transform duration-500" />
            <div class="absolute inset-0 bg-amber-200 opacity-0 group-hover:opacity-40 transition-opacity" />
          </div>
          <h1 class="text-4xl md:text-6xl font-black mb-6">
            {{ pageTitle || t('theme.janari.pages.achievement.title') }}
          </h1>
          <p class="text-lg text-muted-foreground max-w-2xl mx-auto font-medium">
            {{ pageSubtitle || t('theme.janari.pages.achievement.subtitle') }}
          </p>
        </div>
      </section>

      <PluginSlot name="after_hero" class="w-full" />

      <!-- Achievement Grid -->
      <section class="py-12">
        <div class="container mx-auto px-4">
          <!-- Categories Filter -->
          <div class="flex flex-wrap items-center justify-center gap-4 mb-16">
            <button 
              v-for="cat in categoryOptions" 
              :key="cat.slug"
              :class="[
                'px-6 py-2.5 rounded-full text-sm font-bold transition-all duration-300',
                activeCategory === cat.slug 
                  ? 'bg-foreground text-background shadow-lg scale-105' 
                  : 'bg-muted text-muted-foreground hover:bg-muted/80'
              ]"
              @click="activeCategory = cat.slug"
            >
              {{ cat.label }}
            </button>
          </div>

          <!-- Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <div 
              v-for="item in filteredAchievements" 
              :key="item.id"
              class="group bg-card rounded-[3rem] border border-border overflow-hidden hover:shadow-2xl transition-all duration-500 hover:-translate-y-2"
            >
              <!-- Image Area -->
              <div class="aspect-[4/5] bg-muted relative overflow-hidden">
                <img
                  v-if="item.image"
                  :src="item.image"
                  :alt="item.title"
                  class="absolute inset-0 h-full w-full object-cover"
                  loading="lazy"
                  decoding="async"
                >
                <div
                  v-else
                  class="absolute inset-0 flex items-center justify-center text-muted-foreground font-bold italic px-4 text-center"
                >
                  {{ item.imagePlaceholder }}
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-60 group-hover:opacity-100 transition-opacity duration-500" />
               
                <div class="absolute bottom-6 left-6 right-6 text-white translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                  <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-0.5 rounded-lg bg-amber-500 text-[10px] font-black uppercase tracking-widest">{{ item.level }}</span>
                    <span class="text-[10px] font-bold uppercase opacity-80">{{ item.year }}</span>
                  </div>
                  <h3 class="text-xl font-bold leading-snug">
                    {{ item.title }}
                  </h3>
                </div>
              </div>

              <div class="p-8">
                <p class="text-muted-foreground text-sm leading-relaxed mb-6">
                  {{ item.description }}
                </p>
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                    {{ item.winner.substring(0, 1) }}
                  </div>
                  <div>
                    <div class="text-sm font-bold">
                      {{ item.winner }}
                    </div>
                    <div class="text-[10px] font-bold text-muted-foreground uppercase">
                      {{ item.role }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="py-20 border-t border-border bg-muted/20">
        <div class="container mx-auto px-4 text-center max-w-2xl space-y-6">
          <h2 class="text-2xl font-bold text-foreground">{{ t('theme.janari.pages.achievement.ctaTitle') }}</h2>
          <p class="text-muted-foreground">{{ t('theme.janari.pages.achievement.ctaBody') }}</p>
          <div class="flex flex-wrap justify-center gap-4">
            <router-link to="/pricing" class="px-8 py-3 text-xs font-bold uppercase tracking-widest bg-primary text-primary-foreground rounded-lg">
              {{ t('theme.janari.pages.achievement.ctaPricing') }}
            </router-link>
            <router-link to="/contact" class="px-8 py-3 text-xs font-bold uppercase tracking-widest border border-border rounded-lg hover:bg-muted/50">
              {{ t('theme.janari.pages.achievement.ctaContact') }}
            </router-link>
          </div>
        </div>
      </section>
    </template>

    <!-- If Disabled -->
    <PageDisabled 
      v-else 
      :title="(pageTitle as string) || 'pencapaian'" 
      :message="(getSetting('disabled_page_message') as string)" 
    />
  </div>
</template>

<script setup lang="ts">
import { PluginSlot } from '@/shared/components'
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import PageDisabled from '../components/shared/PageDisabled.vue';
import { useThemeDataBindings } from '@/modules/Layout/composables/useThemeDataBindings';
import { Trophy } from 'lucide-vue-next';

const { t } = useI18n()
const { getSetting } = useTheme();
const router = useRouter();

const isEnabled = computed(() => getSetting('enable_achievement', true));
const behavior = computed(() => getSetting('disabled_page_behavior', 'message'));
const pageTitle = computed(() => getSetting('page_achievement_title') as string);
const pageSubtitle = computed(() => getSetting('page_achievement_subtitle') as string);

onMounted(() => {
    if (!isEnabled.value && behavior.value === 'redirect') {
        router.push('/');
    }
});

const activeCategory = ref('all');

const categoryOptions = computed(() => [
  { slug: 'all', label: t('theme.janari.pages.achievement.categoryAll') },
  { slug: 'platform', label: t('theme.janari.pages.achievement.categoryPlatform') },
  { slug: 'publishing', label: t('theme.janari.pages.achievement.categoryPublishing') },
  { slug: 'intelligence', label: t('theme.janari.pages.achievement.categoryIntelligence') },
  { slug: 'partnership', label: t('theme.janari.pages.achievement.categoryPartnership') },
]);

// Theme data bindings for achievements list
const { data: dynamicAchievements, hasBinding } = useThemeDataBindings('achievements', 'list');

const normalizeCatSlug = (raw: string | undefined): string => {
  const s = (raw || 'platform').toString().toLowerCase();
  if (s === 'semua' || s === 'all') return 'all';
  if (['platform', 'publishing', 'intelligence', 'partnership'].includes(s)) return s;
  return 'platform';
};

const demoAchievements = computed(() =>
  [0, 1, 2, 3].map((i) => {
    const prefix = `theme.janari.demo.achievement${i}`;
    return {
      id: String(i + 1),
      catSlug: ['platform', 'publishing', 'platform', 'publishing'][i],
      level: t('theme.janari.pages.achievement.levelDefault'),
      year: i === 0 ? '2025' : '2024',
      title: t(`${prefix}.title`),
      description: t(`${prefix}.description`),
      winner: t(`${prefix}.winner`),
      role: t(`${prefix}.role`),
      imagePlaceholder: t(`${prefix}.imagePlaceholder`),
      image: '' as string,
    };
  }),
);

const achievements = computed(() => {
  if (hasBinding.value && dynamicAchievements.value.length > 0) {
    return dynamicAchievements.value.map((item: any, idx: number) => {
      const raw = item._raw || item;
      return {
        id: raw.id || idx + 1,
        catSlug: normalizeCatSlug(raw.meta?.category_slug || raw.meta?.category_label),
        level: raw.meta?.level || t('theme.janari.pages.achievement.levelDefault'),
        year: raw.published_at ? new Date(raw.published_at).getFullYear() : '2026',
        title: item.title,
        description: item.excerpt || item.description || raw.description,
        winner: raw.meta?.winner || t('theme.janari.pages.achievement.winnerDefault'),
        role: raw.meta?.role || t('theme.janari.pages.achievement.roleDefault'),
        image: raw.featured_image || raw.thumbnail,
        imagePlaceholder: t('theme.janari.pages.achievement.photoPlaceholder'),
      };
    });
  }
  return demoAchievements.value;
});

const filteredAchievements = computed(() => {
  if (activeCategory.value === 'all') return achievements.value;
  return achievements.value.filter((a) => a.catSlug === activeCategory.value);
});
</script>

<template>
  <div class="min-h-screen bg-background" data-ja-customizer-target="achievements">
    <BlockRenderer
      v-if="hasBuilderBlocks"
      :blocks="builderBlocks"
      :context="{ post: pageData, site: { name: 'Jejakawan' } }"
    />

    <!-- If Enabled -->
    <template v-else-if="isEnabled">
      <!-- Hero / Title -->
      <section class="py-10 md:py-12 text-center">
        <div class="container mx-auto px-4">
          <div class="inline-flex items-center justify-center p-4 bg-amber-100 dark:bg-amber-500/20 rounded-3xl mb-6 group overflow-hidden relative">
            <Trophy class="w-8 h-8 text-amber-600 dark:text-amber-400 relative z-10 group-hover:scale-125 transition-transform duration-500" />
            <div class="absolute inset-0 bg-amber-200 dark:bg-amber-400/20 opacity-0 group-hover:opacity-40 transition-opacity" />
          </div>
          <h1 class="text-3xl md:text-5xl font-black mb-4 text-foreground">
            {{ pageTitle }}
          </h1>
          <p class="text-base text-muted-foreground max-w-2xl mx-auto font-medium">
            {{ pageSubtitle }}
          </p>
        </div>
      </section>

      <PluginSlot name="after_hero" class="w-full" />

      <!-- Achievement Grid -->
      <section class="py-8 md:py-10">
        <div class="container mx-auto px-4">
          <!-- Categories Filter -->
          <div class="flex flex-wrap items-center justify-center gap-3 mb-8 md:mb-10">
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
          <div
            v-if="filteredAchievements.length === 0"
            class="text-center py-12 text-sm text-muted-foreground border border-dashed border-border rounded-2xl"
          >
            {{ pageSubtitle }}
          </div>
          <div
            v-else
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
          >
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

      <section class="py-12 md:py-14 border-t border-border bg-muted/20">
        <div class="container mx-auto px-4 text-center max-w-2xl space-y-6">
          <h2 class="text-2xl font-bold text-foreground">{{ ctaTitle }}</h2>
          <p class="text-muted-foreground">{{ ctaBody }}</p>
          <div class="flex flex-wrap justify-center gap-4">
            <router-link :to="pricingUrl" class="px-8 py-3 text-xs font-bold uppercase tracking-widest bg-primary text-primary-foreground rounded-lg">
              {{ ctaPricing }}
            </router-link>
            <router-link :to="contactUrl" class="px-8 py-3 text-xs font-bold uppercase tracking-widest border border-border rounded-lg hover:bg-muted/50">
              {{ ctaContact }}
            </router-link>
          </div>
        </div>
      </section>
    </template>

    <!-- If Disabled -->
    <PageDisabled 
      v-else 
      :title="(pageTitle as string) || t('theme.janari.pages.achievement.title')" 
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
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import PageDisabled from '../components/shared/PageDisabled.vue';
import { useThemeDataBindings } from '@/modules/Layout/composables/useThemeDataBindings';
import { Trophy } from 'lucide-vue-next';

const { t } = useI18n()
const { getSetting } = useTheme();
const { localizedString } = useLocalizedThemeSetting()
const { pageData, builderBlocks, hasBuilderBlocks } = useThemePageOverride('achievement')
const router = useRouter();

const isEnabled = computed(() => getSetting('enable_achievement', true));
const behavior = computed(() => getSetting('disabled_page_behavior', 'message'));
const pageTitle = computed(() => localizedString('page_achievement_title') || t('theme.janari.pages.achievement.title'));
const pageSubtitle = computed(() => localizedString('page_achievement_subtitle') || t('theme.janari.pages.achievement.subtitle'));
const ctaTitle = computed(() => localizedString('page_achievement_cta_title') || t('theme.janari.pages.achievement.ctaTitle'));
const ctaBody = computed(() => localizedString('page_achievement_cta_body') || t('theme.janari.pages.achievement.ctaBody'));
const ctaPricing = computed(() => localizedString('page_achievement_cta_pricing') || t('theme.janari.pages.achievement.ctaPricing'));
const ctaContact = computed(() => localizedString('page_achievement_cta_contact') || t('theme.janari.pages.achievement.ctaContact'));
const pricingUrl = computed(() => {
  const raw = getSetting('page_achievement_pricing_url', '/pricing')
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/pricing'
})
const contactUrl = computed(() => {
  const raw = getSetting('page_achievement_contact_url', '/contact')
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/contact'
})

onMounted(() => {
    if (!isEnabled.value && behavior.value === 'redirect') {
        router.push('/');
    }
});

const activeCategory = ref('all');

const categoryOptions = computed(() => [
  { slug: 'all', label: localizedString('page_achievement_cat_all') || t('theme.janari.pages.achievement.categoryAll') },
  { slug: 'platform', label: localizedString('page_achievement_cat_platform') || t('theme.janari.pages.achievement.categoryPlatform') },
  { slug: 'publishing', label: localizedString('page_achievement_cat_publishing') || t('theme.janari.pages.achievement.categoryPublishing') },
  { slug: 'intelligence', label: localizedString('page_achievement_cat_intelligence') || t('theme.janari.pages.achievement.categoryIntelligence') },
  { slug: 'partnership', label: localizedString('page_achievement_cat_partnership') || t('theme.janari.pages.achievement.categoryPartnership') },
]);

// Theme data bindings for achievements list
const { data: dynamicAchievements, hasBinding } = useThemeDataBindings('achievements', 'list');

const normalizeCatSlug = (raw: string | undefined): string => {
  const s = (raw || 'platform').toString().toLowerCase();
  if (s === 'semua' || s === 'all') return 'all';
  if (['platform', 'publishing', 'intelligence', 'partnership'].includes(s)) return s;
  return 'platform';
};

const demoAchievements = computed(() => [] as Array<{
  id: string;
  catSlug: string;
  level: string;
  year: string | number;
  title: string;
  description: string;
  winner: string;
  role: string;
  imagePlaceholder: string;
  image: string;
}>);

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
  return [];
});

const filteredAchievements = computed(() => {
  if (activeCategory.value === 'all') return achievements.value;
  return achievements.value.filter((a) => a.catSlug === activeCategory.value);
});
</script>

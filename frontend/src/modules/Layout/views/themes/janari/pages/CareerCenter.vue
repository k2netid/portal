<template>
  <div class="min-h-screen bg-background" data-ja-customizer-target="careers">
    <BlockRenderer
      v-if="hasBuilderBlocks"
      :blocks="builderBlocks"
      :context="{ post: pageData, site: { name: 'Jejakawan' } }"
    />

    <!-- If Enabled -->
    <template v-else-if="isEnabled">
      <!-- Header -->
      <header class="py-10 md:py-12 bg-primary text-primary-foreground">
        <div class="container mx-auto px-4">
          <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-3 text-center md:text-left">
              <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                {{ pageTitle }}
              </h1>
              <p class="text-primary-foreground/90 text-base max-w-xl">
                {{ pageSubtitle }}
              </p>
            </div>
            <div class="flex gap-4">
              <div class="p-5 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 text-center min-w-[5rem]">
                <div class="text-2xl font-black">
                  {{ jobsCount }}
                </div>
                <div class="text-[10px] font-bold uppercase tracking-wider opacity-90 mt-1">
                  {{ jobsStatLabel }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </header>

      <div class="container mx-auto px-4 py-10 md:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Main Content (Job List) -->
          <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-bold flex items-center gap-3 text-foreground">
                <Briefcase class="w-5 h-5 text-primary" />
                {{ latestJobsText }}
              </h2>
              <button class="text-sm font-bold text-primary hover:underline">
                {{ viewAllJobsText }}
              </button>
            </div>

            <!-- Job Cards -->
            <div
              v-if="jobs.length === 0"
              class="text-center py-12 text-sm text-muted-foreground border border-dashed border-border rounded-2xl"
            >
              {{ pageSubtitle }}
            </div>
            <div
              v-else
              class="space-y-4"
            >
              <div 
                v-for="job in jobs" 
                :key="job.id"
                class="p-6 rounded-[2rem] bg-card border border-border hover:border-primary/50 transition-all duration-300 hover:shadow-xl group"
              >
                <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                  <div class="w-16 h-16 rounded-2xl bg-muted flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <Building2 class="w-8 h-8 text-muted-foreground" />
                  </div>
                  <div class="flex-1 space-y-1">
                    <div class="flex items-center gap-2">
                      <h3 class="font-bold text-lg">
                        {{ job.title }}
                      </h3>
                      <span
                        v-if="job.isNew"
                        class="px-2 py-0.5 rounded-full bg-primary/3 text-primary text-[10px] font-bold uppercase"
                      >{{ newBadgeText }}</span>
                    </div>
                    <p class="text-primary font-semibold text-sm">
                      {{ job.company }}
                    </p>
                    <div class="flex flex-wrap gap-4 pt-2">
                      <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <MapPin class="w-3.5 h-3.5" /> {{ job.location }}
                      </div>
                      <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <Clock class="w-3.5 h-3.5" /> {{ job.type }}
                      </div>
                    </div>
                  </div>
                  <router-link :to="job.url || '/contact'" class="px-6 py-2.5 rounded-xl bg-primary inline-flex items-center justify-center text-primary-foreground text-sm font-bold hover:bg-primary/90 transition-colors">
                    {{ detailButtonText }}
                  </router-link>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-8">
            <!-- Hubin Section -->
            <div class="p-8 rounded-[2.5rem] bg-muted/40 dark:bg-muted/20 border border-border">
              <div class="w-12 h-12 rounded-2xl bg-primary text-primary-foreground flex items-center justify-center mb-6 shadow-lg shadow-primary/25">
                <Users2 class="w-6 h-6" />
              </div>
              <h3 class="text-xl font-bold mb-3 text-foreground">
                {{ hubinTitle }}
              </h3>
              <p class="text-muted-foreground text-sm leading-relaxed mb-6">
                {{ hubinDesc }}
              </p>
              <router-link :to="pricingUrl" class="w-full py-3 mb-3 rounded-xl border border-border text-foreground font-bold text-sm hover:bg-muted/60 transition-colors block text-center">
                {{ viewPricingText }}
              </router-link>
              <router-link :to="contactUrl" class="w-full py-3 rounded-xl bg-primary text-primary-foreground font-bold text-sm hover:bg-primary/90 transition-colors block text-center">
                {{ partnershipContactText }}
              </router-link>
            </div>

            <!-- Resources -->
            <div class="p-8 rounded-[2.5rem] bg-card border border-border">
              <h3 class="font-bold text-lg mb-6">
                {{ guideTitle }}
              </h3>
              <ul class="space-y-4">
                <li
                  v-for="(link, idx) in careerGuideLinks"
                  :key="idx"
                  class="flex items-center gap-3 text-sm text-muted-foreground hover:text-primary cursor-pointer transition-colors group"
                >
                  <ArrowRight class="w-4 h-4 text-primary group-hover:translate-x-1 transition-transform" />
                  {{ link }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- If Disabled -->
    <PageDisabled 
      v-else 
      :title="(pageTitle as string) || t('theme.janari.pages.career.jobBoardTitle')" 
      :message="(getSetting('disabled_page_message') as string)" 
    />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting';
import { useThemeDataBindings } from '@/modules/Layout/composables/useThemeDataBindings';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import PageDisabled from '../components/shared/PageDisabled.vue';
import {
  Briefcase,
  Building2,
  MapPin,
  Clock,
  Users as Users2,
  ArrowRight,
} from 'lucide-vue-next';

const { t } = useI18n()
const { getSetting } = useTheme();
const { localizedString } = useLocalizedThemeSetting()
const { pageData, builderBlocks, hasBuilderBlocks } = useThemePageOverride('career')
const router = useRouter();

const isEnabled = computed(() => getSetting('enable_career', true));
const behavior = computed(() => getSetting('disabled_page_behavior', 'message'));
const pageTitle = computed(() => localizedString('page_career_title') || t('theme.janari.pages.career.title'));
const pageSubtitle = computed(() => localizedString('page_career_subtitle') || t('theme.janari.pages.career.subtitle'));
const latestJobsText = computed(() => localizedString('page_career_latest_jobs') || t('theme.janari.pages.career.latestJobs'));
const hubinTitle = computed(() => localizedString('page_career_hubin_title') || t('theme.janari.pages.career.hubinTitle'));
const hubinDesc = computed(() => localizedString('page_career_hubin_desc') || t('theme.janari.pages.career.hubinDesc'));
const viewPricingText = computed(() => localizedString('page_career_view_pricing') || t('theme.janari.pages.career.viewPricing'));
const partnershipContactText = computed(() => localizedString('page_career_partnership_contact') || t('theme.janari.pages.career.partnershipContact'));
const jobsStatLabel = computed(() => localizedString('page_career_stat_jobs') || t('theme.janari.pages.career.newJobsStat'));
const viewAllJobsText = computed(() => localizedString('page_career_view_all') || t('theme.janari.pages.career.viewAllShort'));
const newBadgeText = computed(() => localizedString('page_career_new_badge') || t('theme.janari.pages.career.newBadge'));
const detailButtonText = computed(() => localizedString('page_career_detail') || t('theme.janari.pages.career.detail'));
const guideTitle = computed(() => localizedString('page_career_guide_title') || t('theme.janari.pages.career.careerGuideTitle'));
const pricingUrl = computed(() => {
  const raw = getSetting('page_career_pricing_url', '/pricing')
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/pricing'
})
const contactUrl = computed(() => {
  const raw = getSetting('page_career_contact_url', '/contact')
  return typeof raw === 'string' && raw.trim() ? raw.trim() : '/contact'
})

onMounted(() => {
    if (!isEnabled.value && behavior.value === 'redirect') {
        router.push('/');
    }
});

const careerGuideLinks = computed(() => {
  const raw = getSetting('page_career_guide_links')
  if (Array.isArray(raw) && raw.length > 0) {
    return raw.map((row) => {
      const item = (row && typeof row === 'object' ? row : {}) as Record<string, unknown>
      return String(item.label || '').trim()
    }).filter(Boolean)
  }
  return [0, 1, 2, 3].map((i) => t(`theme.janari.pages.career.guideLink${i}`))
})

const { data: dynamicJobs, hasBinding } = useThemeDataBindings('careers', 'jobs');

const jobsCount = computed(() => String(jobs.value.length))

const jobs = computed(() => {
  if (hasBinding.value && dynamicJobs.value.length > 0) {
    return (dynamicJobs.value as Record<string, unknown>[]).map((item, idx) => {
      const raw = (item._raw as Record<string, unknown>) || item;
      const slug = String(item.url || raw.slug || '').trim();
      return {
        id: String(raw.id || idx + 1),
        title: String(item.title || raw.title || ''),
        company: String(item.company || (raw.meta as Record<string, unknown>)?.company || 'Jejakawan'),
        location: String(item.location || (raw.meta as Record<string, unknown>)?.location || ''),
        type: String(item.type || (raw.meta as Record<string, unknown>)?.job_type || ''),
        isNew: idx < 2,
        url: slug ? `/blog/${slug}` : '/contact',
      };
    });
  }
  return [];
});
</script>

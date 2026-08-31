<template>
  <section class="py-12 sm:py-14 border-t border-border bg-gradient-to-b from-background to-muted/20">
    <div class="container mx-auto px-4">
      <div class="text-center max-w-2xl mx-auto mb-8 md:mb-10 space-y-3">
        <span class="text-xs font-bold tracking-widest text-primary uppercase bg-primary/10 px-3 py-1 rounded-full inline-block">
          {{ surveyBadge }}
        </span>
        <h2 class="text-3xl md:text-4xl font-extrabold text-foreground tracking-tight">
          {{ surveyTitle }}
        </h2>
        <p class="text-muted-foreground text-sm md:text-base leading-relaxed">
          {{ surveySubtitle }}
        </p>
      </div>

      <div
        v-if="loading"
        class="flex items-center justify-center py-12"
      >
        <div class="flex flex-col items-center gap-3 text-muted-foreground">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary" />
          <span class="text-xs font-medium">{{ surveyLoading }}</span>
        </div>
      </div>

      <div
        v-else-if="surveyRecords.length > 0"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto"
      >
        <div
          v-for="item in surveyRecords"
          :key="item.id"
          class="flex flex-col justify-between p-6 rounded-2xl border border-border/80 bg-card/70 backdrop-blur-xs shadow-xs hover:shadow-md hover:border-primary/40 transition-all duration-300 group"
        >
          <div class="space-y-4">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-primary/20 bg-muted shrink-0 shadow-xs">
                <img
                  v-if="item.avatar"
                  :src="item.avatar"
                  :alt="item.respondent_name"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  loading="lazy"
                >
                <div
                  v-else
                  class="w-full h-full flex items-center justify-center text-primary font-bold text-sm"
                >
                  {{ item.respondent_name.charAt(0) }}
                </div>
              </div>
              <div class="min-w-0 flex-1">
                <h4 class="font-bold text-sm text-foreground truncate">
                  {{ item.respondent_name }}
                </h4>
                <p class="text-xs text-muted-foreground truncate">
                  {{ item.organization || item.user_role }}
                </p>
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
              <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-md">
                ⭐ {{ item.satisfaction_score }}/10
              </span>
              <span
                v-if="item.favorite_feature"
                class="inline-block text-[10px] font-medium text-primary bg-primary/10 px-2 py-0.5 rounded-md truncate max-w-[170px]"
              >
                {{ item.favorite_feature }}
              </span>
            </div>

            <p class="text-xs text-muted-foreground leading-relaxed italic line-clamp-4">
              "{{ item.feedback_notes }}"
            </p>
          </div>

          <div class="pt-4 mt-4 border-t border-border/50 flex items-center justify-between text-[11px] text-muted-foreground">
            <span>{{ item.user_role }}</span>
            <span class="font-mono text-[10px]">{{ item.survey_date }}</span>
          </div>
        </div>
      </div>

      <div
        v-else
        class="py-12 text-center text-muted-foreground max-w-lg mx-auto space-y-2"
      >
        <p class="text-sm font-semibold text-foreground">
          {{ surveyEmpty }}
        </p>
        <p class="text-xs">
          {{ surveyEmptyHint }}
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useLocalizedThemeSetting } from '@/modules/Layout/composables/useLocalizedThemeSetting'
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n'

const { localizedString } = useLocalizedThemeSetting()
const { t } = useThemeI18n('janari')

const surveyBadge = computed(() => localizedString('page_about_survey_badge') || t('pages.about.surveyBadge'))
const surveyTitle = computed(() => localizedString('page_about_survey_title') || t('pages.about.surveyTitle'))
const surveySubtitle = computed(() =>
  localizedString('page_about_survey_subtitle') || t('pages.about.surveySubtitle'),
)
const surveyLoading = computed(() =>
  localizedString('page_about_survey_loading') || t('pages.about.surveyLoading'),
)
const surveyEmpty = computed(() => t('pages.about.surveyEmpty'))
const surveyEmptyHint = computed(() => t('pages.about.surveyEmptyHint'))

interface SurveyItem {
  id: string
  respondent_name: string
  email?: string
  organization?: string
  user_role: string
  satisfaction_score: number
  favorite_feature?: string
  avatar?: string
  feedback_notes: string
  survey_date?: string
}

const loading = ref(true)
const surveyRecords = ref<SurveyItem[]>([])

onMounted(async () => {
  try {
    const res = await fetch('/api/v1/system/dynamic/user_survey?per_page=8')
    if (res.ok) {
      const json = await res.json()
      const rawData = json?.data?.data || json?.data
      if (Array.isArray(rawData) && rawData.length > 0) {
        surveyRecords.value = rawData.map((r: Record<string, unknown>) => {
          const data = (r.data || {}) as Record<string, unknown>
          return {
            id: String(r.id || ''),
            respondent_name: String(data.respondent_name || t('pages.about.surveyUser')),
            organization: String(data.organization || ''),
            user_role: String(data.user_role || t('pages.about.surveyRole')),
            satisfaction_score: Number(data.satisfaction_score) || 10,
            favorite_feature: String(data.favorite_feature || ''),
            avatar: String(data.avatar || ''),
            feedback_notes: String(data.feedback_notes || ''),
            survey_date: String(data.survey_date || ''),
          }
        })
      }
    }
  } catch {
    surveyRecords.value = []
  } finally {
    loading.value = false
  }
})
</script>

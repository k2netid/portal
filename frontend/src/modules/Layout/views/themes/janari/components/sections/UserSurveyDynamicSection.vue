<template>
  <section class="py-24 border-t border-border bg-gradient-to-b from-background to-muted/20">
    <div class="container mx-auto px-4">
      <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
        <span class="text-xs font-bold tracking-widest text-primary uppercase bg-primary/10 px-3 py-1 rounded-full inline-block">
          Data Model Studio Live Showcase
        </span>
        <h2 class="text-3xl md:text-5xl font-extrabold text-foreground tracking-tight">
          Apa Kata Pengguna JA-CMS?
        </h2>
        <p class="text-muted-foreground text-sm md:text-base leading-relaxed">
          Umpan balik nyata yang dikumpulkan dan disajikan secara dinamis langsung dari tabel <span class="font-mono text-primary font-semibold">user_survey</span> di Data Model Studio.
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="flex flex-col items-center gap-3 text-muted-foreground">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary" />
          <span class="text-xs font-medium">Memuat data survei dari Data Model Studio...</span>
        </div>
      </div>

      <!-- Survey Testimonials Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">
        <div
          v-for="item in surveyRecords"
          :key="item.id"
          class="flex flex-col justify-between p-6 rounded-2xl border border-border/80 bg-card/70 backdrop-blur-xs shadow-xs hover:shadow-md hover:border-primary/40 transition-all duration-300 group"
        >
          <!-- Card Header: Avatar & Info -->
          <div class="space-y-4">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-primary/20 bg-muted shrink-0 shadow-xs">
                <img
                  :src="item.avatar || '/assets/themes/janari/avatar-placeholder.png'"
                  :alt="item.respondent_name"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  loading="lazy"
                />
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

            <!-- Score & Favorite Feature Badge -->
            <div class="flex flex-wrap items-center gap-2">
              <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-md">
                ⭐ {{ item.satisfaction_score }}/10
              </span>
              <span v-if="item.favorite_feature" class="inline-block text-[10px] font-medium text-primary bg-primary/10 px-2 py-0.5 rounded-md truncate max-w-[170px]">
                {{ item.favorite_feature }}
              </span>
            </div>

            <!-- Feedback Quote -->
            <p class="text-xs text-muted-foreground leading-relaxed italic line-clamp-4">
              "{{ item.feedback_notes }}"
            </p>
          </div>

          <!-- Card Footer -->
          <div class="pt-4 mt-4 border-t border-border/50 flex items-center justify-between text-[11px] text-muted-foreground">
            <span>{{ item.user_role }}</span>
            <span class="font-mono text-[10px]">{{ item.survey_date }}</span>
          </div>
        </div>
      </div>

      <!-- Live API Source Indicator -->
      <div class="mt-12 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-muted/60 text-xs font-mono text-muted-foreground border border-border/60">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>Live API Source: <strong class="text-foreground">/api/v1/system/dynamic/user_survey</strong></span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

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

const fallbackRecords: SurveyItem[] = [
  {
    id: '1',
    respondent_name: 'Arya Pratama',
    organization: 'TechCorp Indonesia',
    user_role: 'Web Developer / Engineer',
    satisfaction_score: 10,
    favorite_feature: 'Data Model Studio (Headless API)',
    avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
    feedback_notes: 'Fitur Data Model Studio sangat mempercepat pembuatan headless API tanpa migrasi manual. Auto-generated OpenAPI 3.0 sangat membantu tim frontend!',
    survey_date: '2026-08-20'
  },
  {
    id: '2',
    respondent_name: 'Siti Nurhaliza',
    organization: 'Creative Hub Agency',
    user_role: 'Content Creator / Editor',
    satisfaction_score: 9,
    favorite_feature: 'Visual Site Editor & Builder',
    avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
    feedback_notes: 'Visual Builder sangat intuitif, live edit langsung terlihat di halaman kanvas dan integrasi Media Picker sangat mulus.',
    survey_date: '2026-08-19'
  },
  {
    id: '3',
    respondent_name: 'Dwi Handoko',
    organization: 'Nusantara Group',
    user_role: 'Site Administrator',
    satisfaction_score: 10,
    favorite_feature: 'Media Library & Asset Manager',
    avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
    feedback_notes: 'Performa query cepat, fitur export data CSV/JSON sangat membantu untuk reporting manajemen berkala.',
    survey_date: '2026-08-18'
  },
  {
    id: '4',
    respondent_name: 'Rina Wijaya',
    organization: 'GrowthLab Digital',
    user_role: 'Marketing / SEO Specialist',
    satisfaction_score: 9,
    favorite_feature: 'Publishing & Content Hub',
    avatar: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=150&auto=format&fit=crop&q=80',
    feedback_notes: 'SEO setting komprehensif, OpenGraph preview memudahkan kurasi artikel sebelum di-publish ke publik.',
    survey_date: '2026-08-17'
  }
]

onMounted(async () => {
  try {
    const res = await fetch('/api/v1/system/dynamic/user_survey?per_page=8')
    if (res.ok) {
      const json = await res.json()
      const rawData = json?.data?.data || json?.data
      if (Array.isArray(rawData) && rawData.length > 0) {
        surveyRecords.value = rawData.map((r: any) => ({
          id: r.id,
          respondent_name: r.data?.respondent_name || 'Pengguna JA-CMS',
          organization: r.data?.organization || '',
          user_role: r.data?.user_role || 'User',
          satisfaction_score: Number(r.data?.satisfaction_score) || 10,
          favorite_feature: r.data?.favorite_feature || '',
          avatar: r.data?.avatar || '',
          feedback_notes: r.data?.feedback_notes || '',
          survey_date: r.data?.survey_date || ''
        }))
      } else {
        surveyRecords.value = fallbackRecords
      }
    } else {
      surveyRecords.value = fallbackRecords
    }
  } catch {
    surveyRecords.value = fallbackRecords
  } finally {
    loading.value = false
  }
})
</script>

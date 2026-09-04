<template>
  <div class="sarangenge-theme flex-1 flex flex-col py-10 md:py-12">
    <BlockRenderer
      v-if="hasBuilderBlocks"
      :blocks="builderBlocks"
      :context="{ site: { name: displaySchoolName } }"
    />

    <template v-else>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 w-full">
        <div class="space-y-4 text-center md:text-left">
          <Breadcrumb :items="[{ name: t('pages.programs.title', 'Program Keahlian') }]" />
          <div class="max-w-3xl space-y-3 mx-auto md:mx-0">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-foreground font-heading tracking-tight">
              {{ t('pages.programs.heading', 'Kompetensi Keahlian') }}
            </h1>
            <p class="text-base sm:text-lg text-muted-foreground leading-relaxed">
              {{ t('pages.programs.subtitle', 'Program keahlian unggulan di SMK Negeri 6 Bandung yang diselaraskan dengan kebutuhan dunia usaha dan industri (DUDI).') }}
            </p>
          </div>
        </div>

        <div v-if="loading" class="min-h-[300px] flex items-center justify-center">
          <div class="w-8 h-8 rounded-full border-2 border-[var(--sarangenge-teal,#0f766e)] border-t-transparent animate-spin" />
        </div>

        <div v-else-if="programs.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="program in programs"
            :key="program.id"
            class="sarangenge-panel group flex flex-col overflow-hidden transition-all duration-300 hover:shadow-xl hover:border-[var(--sarangenge-teal,#0f766e)]/30"
          >
            <div class="p-6 sm:p-8 flex-1 space-y-4">
              <div class="w-12 h-12 rounded-xl bg-[var(--sarangenge-teal,#0f766e)]/10 flex items-center justify-center text-[var(--sarangenge-teal-deep,#115e59)] dark:text-teal-400 mb-6 group-hover:scale-110 transition-transform">
                <BookOpen class="w-6 h-6" />
              </div>
              <h3 class="text-xl font-bold font-heading text-foreground group-hover:text-[var(--sarangenge-teal,#0f766e)] transition-colors">
                {{ program.title }}
              </h3>
              <p class="text-sm text-muted-foreground line-clamp-3">
                {{ program.excerpt || program.intro }}
              </p>
            </div>
            <div class="px-6 py-4 border-t bg-muted/20 flex items-center justify-between">
              <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">SMK Pusat Keunggulan</span>
              <router-link
                :to="`/blog/${program.slug}`"
                class="text-sm font-medium text-[var(--sarangenge-teal,#0f766e)] hover:underline inline-flex items-center gap-1"
              >
                Detail Program
                <ArrowRight class="w-4 h-4" />
              </router-link>
            </div>
          </div>
        </div>

        <div v-else class="sarangenge-panel p-10 text-center text-muted-foreground space-y-3">
          <p class="text-base font-semibold text-foreground">
            {{ t('pages.programs.noData', 'Data program keahlian belum tersedia.') }}
          </p>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import api from '@/engine/api/client';
import { publishingPaths } from '@/engine/api/paths';
import { useThemePageOverride } from '@/modules/Layout/composables/useThemePageOverride';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import Breadcrumb from '@/modules/Layout/views/themes/sarangenge/components/shared/Breadcrumb.vue';
import { useSarangengeIdentity } from '@/modules/Layout/views/themes/sarangenge/composables/useSarangengeIdentity';
import type { Content } from '@/modules/Publishing/types/content';
import { BookOpen, ArrowRight } from 'lucide-vue-next';

const { t } = useThemeI18n('sarangenge');
const { displaySchoolName } = useSarangengeIdentity();
const { builderBlocks, hasBuilderBlocks } = useThemePageOverride('programs');

const programs = ref<Content[]>([]);
const loading = ref(true);

onMounted(async () => {
  loading.value = true;
  try {
    const res = await api.get(publishingPaths.publicContents, {
      params: { category: 'program-keahlian', status: 'published', sort: 'title' },
    });
    const data = res.data;
    programs.value = Array.isArray(data) ? data : data?.data || [];
  } catch (err) {
    programs.value = [];
  } finally {
    loading.value = false;
  }
});
</script>

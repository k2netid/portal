<template>
  <div class="space-y-6">
    <PageHeader
      :title="t('publishing.dashboard.viewer.welcome', { name: authStore.user?.name ?? '' })"
      :subtitle="t('publishing.dashboard.viewer.subtitle')"
      display-size
      borderless
    />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <Card class="border-border/40 bg-card shadow-none rounded-xl">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Newspaper class="w-5 h-5 text-primary" />
            {{ t('publishing.dashboard.viewer.latestPublished') }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <PageSkeleton
            v-if="loading"
            :stat-count="0"
            :show-panel="false"
            :label="t('common.labels.loading')"
          />
          <EmptyState
            v-else-if="recentContent.length === 0"
            :title="t('publishing.dashboard.viewer.empty')"
            :icon="Newspaper"
            compact
          />
          <ul
            v-else
            class="space-y-3"
          >
            <li
              v-for="item in recentContent"
              :key="String(item.id)"
              class="flex items-center gap-3 rounded-xl border border-border/50 bg-muted/10 p-3"
            >
              <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                <FileText class="w-4 h-4" />
              </div>
              <div class="min-w-0">
                <p class="font-medium text-sm text-foreground truncate">{{ item.title }}</p>
                <p class="text-xs text-muted-foreground">
                  {{ t('publishing.dashboard.viewer.publishedOn', { date: formatDate(item.created_at) }) }}
                </p>
              </div>
            </li>
          </ul>
        </CardContent>
      </Card>

      <Card class="border-border/40 bg-card shadow-none rounded-xl">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <HelpCircle class="w-5 h-5 text-primary" />
            {{ t('publishing.dashboard.viewer.quickResources') }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <a
            href="/"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center rounded-xl border border-border/50 p-3 hover:bg-muted/20 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
          >
            <Home class="w-5 h-5 text-muted-foreground mr-3 shrink-0" />
            <div>
              <p class="font-medium text-sm">{{ t('publishing.dashboard.viewer.visitPublicSite') }}</p>
              <p class="text-xs text-muted-foreground">{{ t('publishing.dashboard.viewer.viewLiveSite') }}</p>
            </div>
          </a>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import api from '@/engine/api/client';
import { parseSingleResponse, ensureArray } from '@/shared/utils/responseParser';
import { PageHeader } from '@/shared/components/shell';
import { EmptyState, PageSkeleton } from '@/shared/components/feedback';
import dayjs from 'dayjs';
import { Card, CardHeader, CardTitle, CardContent } from '@/shared/components/ui';
import { FileText, HelpCircle, Home, Newspaper } from 'lucide-vue-next';

interface ContentItem {
    id: string | number;
    title: string;
    created_at: string;
}

const { t } = useI18n();
const authStore = useAuthStore();
const recentContent = ref<ContentItem[]>([]);
const loading = ref(false);

const fetchDashboard = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await api.get('/dashboard/viewer');
        const data = parseSingleResponse<Record<string, unknown>>(response);
        if (data && 'recentContent' in data) {
            recentContent.value = ensureArray<ContentItem>(data.recentContent as ContentItem[]);
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch viewer dashboard:', error);
    } finally {
        loading.value = false;
    }
};

const formatDate = (date: string): string => dayjs(date).format('MMM D, YYYY');

onMounted(() => {
    void fetchDashboard();
});
</script>

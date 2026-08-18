<template>
  <div class="space-y-6">
        <PageHeader
      borderless
      :title="t('system.settings.cache.page.title')"
      :subtitle="t('system.settings.cache.page.subtitle')"
    >
    </PageHeader>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
      <ConsoleStatCard
        :label="t('system.settings.cache.page.status')"
        :value="cacheStatusLabel"
        :icon="Activity"
        tone="primary"
      />
      <ConsoleStatCard
        :label="t('system.settings.cache.page.hits')"
        :value="cacheStats.hits || 0"
        :icon="Target"
        tone="success"
      />
      <ConsoleStatCard
        :label="t('system.settings.cache.page.misses')"
        :value="cacheStats.misses || 0"
        :icon="XCircle"
        tone="destructive"
      />
    </div>

    <ConsoleListCard>
      <div class="p-6 space-y-4">
        <h2 class="text-lg font-semibold text-foreground">
          {{ t('system.settings.cache.page.actionsTitle') }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Button
            variant="secondary"
            :disabled="clearing"
            class="h-auto py-4 px-6 flex flex-col items-start gap-1 justify-start text-left"
            @click="clearAllCache"
          >
            <div class="flex items-center gap-2">
              <Trash2 class="w-4 h-4" />
              <span class="font-bold">{{ t('system.settings.cache.page.clearAll.title') }}</span>
            </div>
            <span class="text-xs text-slate-700 font-normal">{{ t('system.settings.cache.page.clearAll.desc') }}</span>
          </Button>
          <Button
            variant="secondary"
            :disabled="clearing"
            class="h-auto py-4 px-6 flex flex-col items-start gap-1 justify-start text-left bg-amber-800 hover:bg-amber-900 text-white border-0"
            @click="clearContentCache"
          >
            <div class="flex items-center gap-2">
              <FileText class="w-4 h-4" />
              <span class="font-bold">{{ t('system.settings.cache.page.clearContent.title') }}</span>
            </div>
            <span class="text-xs text-white/90 font-normal">{{ t('system.settings.cache.page.clearContent.desc') }}</span>
          </Button>
          <Button
            variant="secondary"
            :disabled="warming"
            class="h-auto py-4 px-6 flex flex-col items-start gap-1 justify-start text-left bg-emerald-800 hover:bg-emerald-900 text-white border-0"
            @click="warmUpCache"
          >
            <div class="flex items-center gap-2">
              <Zap class="w-4 h-4" />
              <span class="font-bold">{{ t('system.settings.cache.page.warmUp.title') }}</span>
            </div>
            <span class="text-xs opacity-80 font-normal">{{ t('system.settings.cache.page.warmUp.desc') }}</span>
          </Button>
        </div>
      </div>
    </ConsoleListCard>

    <Card
      v-if="cacheStats.details"
      class="mt-6"
    >
      <CardHeader>
        <CardTitle class="text-lg font-semibold">
          {{ t('system.settings.cache.page.detailedStats') }}
        </CardTitle>
      </CardHeader>
      <CardContent class="p-0">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>{{ t('system.settings.cache.page.table.key') }}</TableHead>
              <TableHead>{{ t('system.settings.cache.page.table.value') }}</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="(value, key) in cacheStats.details"
              :key="key"
            >
              <TableCell class="font-mono text-xs">
                {{ key }}
              </TableCell>
              <TableCell class="font-medium tabular-nums">
                {{ value }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { PageHeader } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import { Button, Card, CardContent, CardHeader, CardTitle, Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/shared/components/ui';

import {
  Activity,
  FileText,
  Target,
  Trash2,
  XCircle,
  Zap,
} from 'lucide-vue-next';
import toast from '@/shared/services/toastService';
import { useConfirm } from '@/shared/composables/useConfirm';

const { t } = useI18n();

interface CacheStats {
    status: string;
    hits: number;
    misses: number;
    details?: Record<string, string | number> | null;
}

const cacheStats = ref<CacheStats>({
    status: 'active',
    hits: 0,
    misses: 0,
});

const isCacheActive = computed(() => {
    const s = (cacheStats.value.status || '').toLowerCase();
    return s === 'active' || s === 'aktif';
});

const cacheStatusLabel = computed(() => {
    if (isCacheActive.value) return t('system.settings.cache.page.statusActive');
    return t('system.settings.cache.page.statusInactive');
});

const clearing = ref(false);
const warming = ref(false);
const { confirm } = useConfirm();

const fetchCacheStats = async () => {
    try {
        const response = await api.get('/manage/system/cache-status');
        const data = parseSingleResponse<CacheStats>(response);
        if (data) {
            cacheStats.value = {
                status: data.status || 'active',
                hits: data.hits || 0,
                misses: data.misses || 0,
                details: data.details || null,
            };
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch cache stats:', error);
        cacheStats.value = {
            status: 'active',
            hits: 0,
            misses: 0,
        };
    }
};

const clearAllCache = async () => {
    const confirmed = await confirm({
        title: t('system.settings.cache.page.clearAll.confirmTitle'),
        message: t('system.settings.cache.page.clearAll.confirmMessage'),
        variant: 'danger',
        confirmText: t('system.settings.cache.page.clearAll.confirmText'),
    });

    if (!confirmed) return;

    clearing.value = true;
    try {
        await api.post('/manage/system/cache/clear');
        toast.success(t('system.settings.cache.page.clearAll.success'));
        await fetchCacheStats();
    } catch (error: unknown) {
        logger.error('Failed to clear cache:', error);
        toast.error(t('common.messages.toast.error'), t('system.settings.cache.page.clearAll.failed'));
    } finally {
        clearing.value = false;
    }
};

const clearContentCache = async () => {
    const confirmed = await confirm({
        title: t('system.settings.cache.page.clearContent.confirmTitle'),
        message: t('system.settings.cache.page.clearContent.confirmMessage'),
        variant: 'warning',
        confirmText: t('system.settings.cache.page.clearContent.confirmText'),
    });

    if (!confirmed) return;

    clearing.value = true;
    try {
        await api.post('/manage/system/cache/clear-content');
        toast.success(t('system.settings.cache.page.clearContent.success'));
        await fetchCacheStats();
    } catch (error: unknown) {
        logger.error('Failed to clear content cache:', error);
        toast.error(t('common.messages.toast.error'), t('system.settings.cache.page.clearContent.failed'));
    } finally {
        clearing.value = false;
    }
};

const warmUpCache = async () => {
    warming.value = true;
    try {
        await api.post('/manage/system/cache/warm-up');
        toast.success(t('system.settings.cache.page.warmUp.success'));
        await fetchCacheStats();
    } catch (error: unknown) {
        logger.error('Failed to warm up cache:', error);
        toast.error(t('common.messages.toast.error'), t('system.settings.cache.page.warmUp.failed'));
    } finally {
        warming.value = false;
    }
};

onMounted(() => {
    fetchCacheStats();
});
</script>

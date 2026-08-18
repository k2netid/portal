<template>
  <div class="space-y-6">
    <PageHeader
borderless
      :title="t('system.system.logs.title')"
    :subtitle="t('system.system.logs.subtitle')"
    >
      <template #actions>
        <div class="flex items-center space-x-2">
                <Button
                  :disabled="clearing"
                  variant="secondary"
                  class="text-red-800 hover:bg-red-50"
                  @click="clearLogs"
                >
                  {{ clearing ? t('system.system.logs.clearing') : t('system.system.logs.clear') }}
                </Button>
              </div>
      </template>
    </PageHeader>

<ConsoleListCard>
      <div class="p-6 space-y-6">
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Log Files List -->
      <div class="lg:col-span-1">
        <div class="bg-card border border-border rounded-lg">
          <div class="px-6 py-4 border-b border-border">
            <h2 class="text-lg font-semibold text-foreground">
              {{ t('system.system.logs.files') }}
            </h2>
          </div>
          <div class="divide-y divide-border">
            <div
              v-for="logFile in logFiles"
              :key="logFile.name"
              class="w-full flex items-center justify-between px-6 py-4 rounded-none border-b border-border last:border-0 hover:bg-muted"
              :class="[ selectedLogFile?.name === logFile.name ? 'bg-muted border-l-4 border-l-primary' : '' ]"
            >
              <button
                type="button"
                class="text-left min-w-0 flex-1 bg-transparent border-0 p-0 cursor-pointer"
                @click="selectLogFile(logFile)"
              >
                <p class="text-sm font-medium text-foreground">
                  {{ logFile.name }}
                </p>
                <p class="text-xs text-muted-foreground mt-1">
                  {{ formatFileSize(logFile.size) }}
                </p>
              </button>
              <Button
                variant="secondary"
                size="icon"
                :aria-label="t('common.actions.download') + ': ' + logFile.name"
                class="h-8 w-8 shrink-0"
                @click.stop="downloadLog(logFile)"
              >
                <Download class="w-5 h-5" />
              </Button>
            </div>
          </div>
        </div>
      </div>

      <!-- Log Viewer -->
      <div class="lg:col-span-2">
        <div class="bg-card border border-border rounded-lg">
          <div class="px-6 py-4 border-b border-border flex items-center justify-between">
            <h2 class="text-lg font-semibold text-foreground">
              {{ selectedLogFile ? selectedLogFile.name : t('system.system.logs.select') }}
            </h2>
            <div
              v-if="selectedLogFile"
              class="flex items-center space-x-2"
            >
              <Input
                v-model="logSearch"
                type="text"
                :placeholder="t('system.system.logs.search')"
                class="h-8 w-48"
              />
              <Button
                variant="outline"
                size="sm"
                @click="refreshLog"
              >
                {{ t('system.system.logs.refresh') }}
              </Button>
            </div>
          </div>
          <div class="p-6">
            <div
              v-if="!selectedLogFile"
              class="text-center py-12"
            >
              <FileText class="mx-auto h-12 w-12 text-muted-foreground" />
              <p class="mt-4 text-muted-foreground">
                {{ t('system.system.logs.empty') }}
              </p>
            </div>
            <div
              v-else-if="loadingLog"
              class="text-center py-12"
            >
              <p class="text-muted-foreground">
                {{ t('system.system.logs.loading') }}
              </p>
            </div>
            <div
              v-else
              class="bg-background rounded-lg p-4 overflow-x-auto max-h-[600px] overflow-y-auto"
            >
              <SafeHtml
                tag="pre"
                class="text-xs font-mono text-muted-foreground"
                :html="highlightedLogContent"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
      </div>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import {PageHeader, ConsoleListCard} from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import SafeHtml from '@/modules/Core/System/components/ui/SafeHtml.vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { parseResponse, ensureArray, parseSingleResponse } from '@/shared/utils/responseParser';
import { Button, Input } from '@/shared/components/ui';
import {
  Download,
  FileText} from 'lucide-vue-next';

interface LogFile {
    name: string;
    size: number;
    modified_at?: string;
}

interface LogResponse {
    content: string;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const logFiles = ref<LogFile[]>([]);
const selectedLogFile = ref<LogFile | null>(null);
const logContent = ref('');
const loadingLog = ref(false);
const clearing = ref(false);
const logSearch = ref('');

const highlightedLogContent = computed(() => {
    if (!logContent.value) return '';
    
    let content = logContent.value;
    
    // Highlight error lines
    content = content.replace(/\[ERROR\]/g, '<span class="text-destructive font-bold">[ERROR]</span>');
    content = content.replace(/\[WARNING\]/g, '<span class="text-yellow-400 font-bold">[WARNING]</span>');
    content = content.replace(/\[INFO\]/g, '<span class="text-blue-400 font-bold">[INFO]</span>');
    
    // Highlight search term
    if (logSearch.value) {
        const regex = new RegExp(`(${logSearch.value})`, 'gi');
        content = content.replace(regex, '<span class="bg-yellow-500 text-black">$1</span>');
    }
    
    return content;
});

const fetchLogFiles = async () : Promise<void> => {
    try {
        const response = await api.get('manage/system-journal');
        const { data } = parseResponse<LogFile[]>(response);
        logFiles.value = ensureArray(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch log files:', error);
        logFiles.value = [];
    }
};

const selectLogFile = async (logFile: LogFile) : Promise<void> => {
    selectedLogFile.value = logFile;
    loadingLog.value = true;
    try {
        const response = await api.get(`manage/system-journal/${logFile.name}`);
        const data = parseSingleResponse<LogResponse>(response) || { content: '' };
        logContent.value = data.content || '';
    } catch (error: unknown) {
        logger.error('Failed to fetch log content:', error);
        logContent.value = t('system.system.logs.failed_load');
    } finally {
        loadingLog.value = false;
    }
};

const refreshLog = () => {
    if (selectedLogFile.value) {
        selectLogFile(selectedLogFile.value);
    }
};

const downloadLog = async (logFile: LogFile) : Promise<void> => {
    try {
        const response = await api.get(`manage/system-journal/${logFile.name}/download`, {
            responseType: 'blob'});
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', logFile.name || 'system.log');
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error: unknown) {
        logger.error('Failed to download logs:', error);
        toast.error.fromResponse(error);
    }
};

const clearLogs = async () : Promise<void> => {
    const confirmed = await confirm({
        title: t('system.system.logs.actions.clear'),
        message: t('system.system.logs.confirm.clear'),
        variant: 'danger',
        confirmText: t('common.actions.clear')});

    if (!confirmed) return;

    clearing.value = true;
    try {
        await api.post('manage/system-journal/clear', {
            reason: `Manual clear from system journal at ${new Date().toISOString()}`});
        toast.success.action(t('system.system.logs.messages.cleared'));
        logContent.value = '';
        await fetchLogFiles();
        selectedLogFile.value = null;
    } catch (error: unknown) {
        logger.error('Failed to clear logs:', error);
        toast.error.fromResponse(error);
    } finally {
        clearing.value = false;
    }
};

const formatFileSize = (bytes?: number) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

onMounted(() => {
    fetchLogFiles();
});
</script>


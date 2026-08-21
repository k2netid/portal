<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="t('system.system.backups.title')"
      :subtitle="t('system.system.backups.description')"
    >
      <template #actions>
        <Button
          size="sm"
          :disabled="creating"
          class="min-w-[140px]"
          @click="createBackup"
        >
          <Loader2
            v-if="creating"
            class="w-4 h-4 mr-2"
          />
          <Plus
            v-else
            class="w-4 h-4 mr-2"
          />
          {{ creating ? t('system.system.backups.creating') : t('system.system.backups.create') }}
        </Button>
      </template>
    </PageHeader>

    <div
      v-if="statistics"
      class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
    >
      <ConsoleStatCard
        :label="t('system.system.backups.stats.total')"
        :value="statistics.total || 0"
        :icon="Database"
        tone="primary"
      />
      <ConsoleStatCard
        :label="t('system.system.backups.stats.size')"
        :value="formatFileSize(statistics.total_size || 0)"
        :icon="HardDrive"
        tone="success"
      />
      <ConsoleStatCard
        :label="t('system.system.backups.stats.last')"
        :value="formatDate(statistics.last_backup) || t('system.system.backups.stats.never')"
        :icon="Clock"
        tone="info"
      />
      <ConsoleStatCard
        :label="t('system.system.backups.stats.auto')"
        :value="statistics.schedule?.enabled ? t('system.system.backups.stats.enabled') : t('system.system.backups.stats.disabled')"
        :icon="Calendar"
        :tone="statistics.schedule?.enabled ? 'success' : 'muted'"
      />
    </div>

    <ConsoleListCard>
      <section class="border-b border-border/50 p-4 sm:p-6">
        <div class="mb-4 flex flex-row items-center justify-between gap-3">
          <h3 class="text-lg font-semibold text-foreground">
            {{ t('system.system.backups.schedule.title') }}
          </h3>
          <Button
            variant="ghost"
            size="sm"
            class="h-8 text-primary hover:bg-primary/10 hover:text-primary"
            @click="openScheduleModal"
          >
            <Settings class="mr-2 h-4 w-4" />
            {{ t('system.system.backups.schedule.configure') }}
          </Button>
        </div>
        <div
          v-if="statistics?.schedule"
          class="grid grid-cols-2 gap-6 md:grid-cols-4"
        >
          <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              {{ t('system.system.backups.schedule.status') }}
            </p>
            <div class="flex items-center gap-2">
              <span
                class="h-2 w-2 rounded-full"
                :class="statistics.schedule.enabled ? 'bg-emerald-500' : 'bg-muted-foreground/30'"
              />
              <p
                class="text-sm font-medium"
                :class="statistics.schedule.enabled ? 'text-emerald-600 dark:text-emerald-400' : 'text-muted-foreground'"
              >
                {{ statistics.schedule.enabled ? t('system.system.backups.stats.enabled') : t('system.system.backups.stats.disabled') }}
              </p>
            </div>
          </div>
          <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              {{ t('system.system.backups.schedule.frequency') }}
            </p>
            <p class="text-sm font-medium capitalize text-foreground">
              {{ t(`system.system.backups.schedule.frequencies.${statistics.schedule.frequency}`) }}
            </p>
          </div>
          <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              {{ t('system.system.backups.schedule.time') }}
            </p>
            <p class="text-sm font-medium text-foreground">
              {{ statistics.schedule.time || '02:00' }}
            </p>
          </div>
          <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              {{ t('system.system.backups.schedule.retention') }}
            </p>
            <p class="text-sm font-medium text-foreground">
              {{ statistics.schedule.retention_days || 30 }} {{ t('system.system.backups.schedule.days') }}
            </p>
          </div>
        </div>
      </section>

      <template #toolbar>
        <div class="relative w-full sm:max-w-xs">
          <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            v-model="search"
            :placeholder="t('system.system.backups.search')"
            class="h-10 w-full pl-9 bg-background"
          />
        </div>
      </template>


        <div
          v-if="loading"
          class="p-12 text-center"
        >
          <Loader2 class="w-8 h-8 mx-auto text-muted-foreground mb-4" />
          <p class="text-muted-foreground font-medium">
            {{ t('system.system.backups.loading') }}
          </p>
        </div>

        <div
          v-else-if="filteredBackups.length === 0"
          class="p-12 text-center"
        >
          <Database class="w-12 h-12 mx-auto text-muted-foreground/20 mb-4" />
          <p class="text-muted-foreground font-medium">
            {{ t('system.system.backups.empty') }}
          </p>
        </div>

        <Table v-else>
          <TableHeader>
            <TableRow>
              <TableHead>{{ t('system.system.backups.table.name') }}</TableHead>
              <TableHead>{{ t('system.system.backups.table.size') }}</TableHead>
              <TableHead>Password</TableHead>
              <TableHead>{{ t('system.system.backups.table.created') }}</TableHead>
              <TableHead class="text-right">
                {{ t('system.system.backups.table.actions') }}
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="backup in filteredBackups"
              :key="backup.id"
              class="hover:bg-muted/50 group"
            >
              <TableCell>
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-muted rounded-lg group-hover:bg-background">
                    <FileArchive class="w-4 h-4 text-muted-foreground group-hover:text-primary" />
                  </div>
                  <div>
                    <div class="text-sm font-semibold text-foreground">
                      {{ backup.name }}
                    </div>
                    <div class="text-xs text-muted-foreground tracking-tight">
                      {{ backup.type || 'Full System Backup' }}
                    </div>
                  </div>
                </div>
              </TableCell>
              <TableCell class="text-sm tabular-nums">
                {{ formatFileSize(backup.size) }}
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-2 max-w-[200px]">
                  <div class="relative flex-1">
                    <input 
                      :type="visiblePasswords[backup.id] ? 'text' : 'password'" 
                      :value="backup.password || '****************'" 
                      readonly
                      :aria-label="t('system.system.backups.table.password')"
                      class="w-full h-8 px-2 pr-8 text-sm bg-background border border-border/60 rounded-md focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                    <button 
                      v-if="backup.password"
                      type="button"
                      class="absolute right-1 top-1/2 -translate-y-1/2 p-1 text-muted-foreground hover:text-foreground"
                      tabindex="-1"
                      :aria-label="visiblePasswords[backup.id] ? t('system.system.backups.table.hidePassword') : t('system.system.backups.table.showPassword')"
                      @click="togglePasswordVisibility(backup.id)"
                    >
                      <EyeOff
                        v-if="visiblePasswords[backup.id]"
                        class="w-3.5 h-3.5"
                      />
                      <Eye
                        v-else
                        class="w-3.5 h-3.5"
                      />
                    </button>
                  </div>
                  <Button
                    v-if="backup.password"
                    variant="ghost"
                    size="icon" :aria-label="t('common.actions.copy')"
                    class="h-8 w-8 shrink-0"
                    @click="copyPassword(backup.id, backup.password)"
                  >
                    <Check
                      v-if="copiedPasswords[backup.id]"
                      class="w-3.5 h-3.5 text-green-500"
                    />
                    <Copy
                      v-else
                      class="w-3.5 h-3.5"
                    />
                  </Button>
                  <span
                    v-else
                    class="text-xs text-muted-foreground italic"
                  >No Password</span>
                </div>
              </TableCell>
              <TableCell class="text-sm text-muted-foreground">
                {{ formatDate(backup.created_at) }}
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100">
                  <Button 
                    variant="ghost" 
                    size="icon"
              :aria-label="t('system.system.backups.table.download')"
                    :title="t('system.system.backups.table.download')"
                    class="h-8 w-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20"
                    @click="downloadBackup(backup)"
                  >
                    <Download class="w-4 h-4" />
                  </Button>
                  <Button 
                    variant="ghost" 
                    size="icon"
              :aria-label="t('system.system.backups.table.restore')"
                    :title="t('system.system.backups.table.restore')"
                    class="h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/20"
                    @click="restoreBackup(backup)"
                  >
                    <RotateCcw class="w-4 h-4" />
                  </Button>
                  <Button 
                    variant="ghost" 
                    size="icon"
              :aria-label="t('system.system.backups.table.delete')"
                    :title="t('system.system.backups.table.delete')"
                    class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                    @click="deleteBackup(backup)"
                  >
                    <Trash2 class="w-4 h-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
    </ConsoleListCard>

    <!-- Schedule Modal -->
    <Dialog v-model:open="showScheduleModal">
      <DialogContent class="console-dialog-sm">
        <DialogHeader>
          <DialogTitle>{{ t('system.system.backups.schedule.modal.title') }}</DialogTitle>
          <DialogDescription class="sr-only">
            {{ t('system.system.backups.schedule.modal.title') }}
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="flex items-center space-x-2">
            <Checkbox 
              id="scheduleEnabled" 
              :checked="scheduleForm.backup_schedule_enabled"
              @update:checked="scheduleForm.backup_schedule_enabled = $event"
            />
            <Label
              for="scheduleEnabled"
              class="text-sm font-medium leading-none cursor-pointer"
            >
              {{ t('system.system.backups.schedule.modal.enable') }}
            </Label>
          </div>
          <div class="grid gap-2">
            <Label for="backupScheduleFrequency">{{ t('system.system.backups.schedule.frequency') }}</Label>
            <Select v-model="scheduleForm.backup_schedule_frequency">
              <SelectTrigger id="backupScheduleFrequency" :aria-label="t('system.system.backups.schedule.frequency')">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="daily">
                  {{ t('system.system.backups.schedule.frequencies.daily') }}
                </SelectItem>
                <SelectItem value="weekly">
                  {{ t('system.system.backups.schedule.frequencies.weekly') }}
                </SelectItem>
                <SelectItem value="monthly">
                  {{ t('system.system.backups.schedule.frequencies.monthly') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="grid gap-2">
            <Label for="backupScheduleTime">{{ t('system.system.backups.schedule.time') }}</Label>
            <Input
              id="backupScheduleTime"
              v-model="scheduleForm.backup_schedule_time"
              type="time"
            />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="grid gap-2">
              <Label for="backupScheduleRetention">{{ t('system.system.backups.schedule.retention') }} ({{ t('system.system.backups.schedule.days') }})</Label>
              <Input
                id="backupScheduleRetention"
                v-model.number="scheduleForm.backup_retention_days"
                type="number"
                min="1"
                max="365"
              />
            </div>
            <div class="grid gap-2">
              <Label for="backupScheduleMaxCount">{{ t('system.system.backups.schedule.modal.max') }}</Label>
              <Input
                id="backupScheduleMaxCount"
                v-model.number="scheduleForm.backup_max_count"
                type="number"
                min="1"
                max="100"
              />
            </div>
          </div>
        </div>
        <DialogFooter>
          <Button
            variant="outline"
            @click="showScheduleModal = false"
          >
            {{ t('system.system.backups.schedule.modal.cancel') }}
          </Button>
          <Button
            :disabled="savingSchedule || !isScheduleDirty"
            @click="saveSchedule"
          >
            <Loader2
              v-if="savingSchedule"
              class="w-4 h-4 mr-2"
            />
            {{ savingSchedule ? t('system.system.backups.schedule.modal.saving') : t('system.system.backups.schedule.modal.save') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Restoring Fullscreen Overlay -->
    <div v-if="restoring" class="fixed inset-0 bg-background/90 backdrop-blur-md z-[100] flex items-center justify-center p-4">
      <div class="bg-card border border-border/50 max-w-sm w-full p-8 rounded-2xl shadow-2xl relative overflow-hidden flex flex-col items-center text-center">
        <div class="animate-spin mb-6">
          <Loader2 class="w-16 h-16 text-primary" />
        </div>
        
        <h2 class="text-xl font-bold text-foreground mb-2">Restoring System</h2>
        <p class="text-sm text-muted-foreground mb-8">Please wait while we recover your data from the archive. Do not close this window.</p>
        
        <div class="w-full space-y-2">
          <div class="flex justify-between items-end">
            <span class="text-sm font-semibold text-foreground animate-pulse">{{ statusText }}</span>
            <span class="text-xs font-mono text-muted-foreground">{{ Math.round(progress) }}%</span>
          </div>
          
          <div class="h-2 w-full bg-secondary overflow-hidden rounded-full relative">
            <div 
              class="absolute top-0 left-0 h-full bg-primary transition-all duration-300 ease-out"
              :style="{ width: `${progress}%` }"
            ></div>
            <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full animate-[shimmer_1.5s_infinite]"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Backup Modal -->
    <Dialog :open="showDeleteModal" @update:open="showDeleteModal = $event">
      <DialogContent class="console-dialog-sm">
        <DialogHeader>
          <DialogTitle class="flex items-center gap-2">
            <Trash2 class="w-5 h-5 text-destructive" />
            {{ t('system.system.backups.actions.delete') }}
          </DialogTitle>
          <DialogDescription class="sr-only">
            {{ backupToDelete ? t('system.system.backups.confirm.delete', { name: backupToDelete.name }) : t('system.system.backups.actions.delete') }}
          </DialogDescription>
        </DialogHeader>
        <div class="py-4">
          <p class="text-sm text-muted-foreground mb-4 whitespace-pre-wrap break-words">
            {{ backupToDelete ? t('system.system.backups.confirm.delete', { name: backupToDelete.name }) : '' }}
          </p>
          <div class="flex items-start space-x-2">
            <Checkbox id="deletePhysicalFile" v-model:checked="deletePhysicalFile" />
            <div class="grid gap-1.5 leading-none">
              <label
                for="deletePhysicalFile"
                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
              >
                Hapus juga file fisik dari server
              </label>
              <p class="text-xs text-muted-foreground">
                Direkomendasikan agar storage server tidak penuh dengan orphaned files.
              </p>
            </div>
          </div>
        </div>
        <DialogFooter>
          <Button
            variant="outline"
            @click="showDeleteModal = false"
            :disabled="deleting"
          >
            {{ t('common.actions.cancel') }}
          </Button>
          <Button
            variant="destructive"
            @click="confirmDeleteBackup"
            :disabled="deleting"
          >
            <Loader2 v-if="deleting" class="w-4 h-4 mr-2 animate-spin" />
            {{ t('common.actions.delete') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleListCard, ConsoleStatCard } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { parseResponse, ensureArray, parseSingleResponse } from '@/shared/utils/responseParser';
import { Button, Checkbox, Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, Input, Label, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/shared/components/ui';

import {
  Calendar,
  Check,
  Clock,
  Copy,
  Database,
  Download,
  Eye,
  EyeOff,
  FileArchive,
  HardDrive,
  Loader2,
  Plus,
  RotateCcw,
  Search,
  Settings,
  Trash2} from 'lucide-vue-next';

interface Backup {
    id: string;
    name: string;
    type: string;
    size: number;
    password?: string;
    created_at: string;
}

interface BackupSchedule {
    enabled: boolean;
    frequency: string;
    time: string;
    retention_days: number;
    max_count: number;
}

interface BackupStatistics {
    total: number;
    total_size: number;
    last_backup: string | null;
    schedule: BackupSchedule;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
const backups = ref<Backup[]>([]);
const statistics = ref<BackupStatistics | null>(null);
const loading = ref(false);
const creating = ref(false);
const search = ref('');
const showScheduleModal = ref(false);
const savingSchedule = ref(false);
const scheduleForm = ref({
    backup_schedule_enabled: false,
    backup_schedule_frequency: 'daily',
    backup_schedule_time: '02:00',
    backup_retention_days: 30,
    backup_max_count: 10
});
const initialScheduleForm = ref<typeof scheduleForm.value | null>(null);

// Visibility toggle state for each backup row
const visiblePasswords = ref<Record<string, boolean>>({});
const copiedPasswords = ref<Record<string, boolean>>({});

const togglePasswordVisibility = (id: string) => {
    visiblePasswords.value[id] = !visiblePasswords.value[id];
};

const copyPassword = async (id: string, password: string) => {
    try {
        await navigator.clipboard.writeText(password);
        copiedPasswords.value[id] = true;
        toast.success.action(t('common.messages.copied'));
        setTimeout(() => {
            copiedPasswords.value[id] = false;
        }, 2000);
    } catch (err) {
        logger.error('Failed to copy password:', err);
    }
};

const isScheduleDirty = computed(() => {
    if (!initialScheduleForm.value) return false;
    return JSON.stringify(scheduleForm.value) !== JSON.stringify(initialScheduleForm.value);
});

const filteredBackups = computed(() => {
    if (!search.value) return backups.value;
    
    const searchLower = search.value.toLowerCase();
    return backups.value.filter(backup => 
        backup.name.toLowerCase().includes(searchLower)
    );
});

const fetchBackups = async () => {
    loading.value = true;
    try {
        const response = await api.get('/manage/system/backups');
        const { data } = parseResponse(response);
        backups.value = ensureArray(data);
        
        // Fetch statistics
        try {
            const statsResponse = await api.get('/manage/system/backups/statistics');
            statistics.value = parseSingleResponse<BackupStatistics>(statsResponse);
        } catch {
            // Calculate from backups if endpoint doesn't exist
            statistics.value = {
                total: backups.value.length,
                total_size: backups.value.reduce((sum, b) => sum + (b.size || 0), 0),
                last_backup: backups.value[0]?.created_at || null,
                schedule: {
                    enabled: false,
                    frequency: 'daily',
                    time: '02:00',
                    retention_days: 30,
                    max_count: 10
                }
            };
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch backups:', error);
    } finally {
        loading.value = false;
    }
};

const createBackup = async () => {
    creating.value = true;
    try {
        await api.post('/manage/system/backups');
        toast.success.action(t('system.system.backups.messages.created'));
        await fetchBackups();
    } catch (error: unknown) {
        logger.error('Failed to create backup:', error);
        toast.error.fromResponse(error);
    } finally {
        creating.value = false;
    }
};

const downloadBackup = async (backup: Backup) => {
    try {
        const response = await api.get(`/manage/system/backups/${backup.id}/download`, {
            responseType: 'blob'});
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', backup.name);
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error: unknown) {
        logger.error('Failed to download backup:', error);
        toast.error.fromResponse(error);
    }
};

const restoreBackup = async (backup: Backup) => {
    const confirmed = await confirm({
        title: t('system.system.backups.actions.restore'),
        message: t('system.system.backups.confirm.restore', { name: backup.name }),
        variant: 'warning',
        confirmText: t('system.system.backups.actions.restore')});

    if (!confirmed) return;

    const doubleConfirmed = await confirm({
        title: t('system.system.backups.actions.restore'),
        message: t('system.system.backups.confirm.restore_warning'),
        variant: 'danger',
        confirmText: t('common.actions.confirm')});

    if (!doubleConfirmed) return;

    restoring.value = true;
    startProgressSimulation([
        'Extracting Backup Archive...',
        'Restoring Database Schema & Data...',
        'Recovering Media Files...',
        'Finalizing Configuration...'
    ]);

    try {
        await api.post(`/manage/system/backups/${backup.id}/restore`);
        stopProgressSimulation();
        statusText.value = 'Restore Complete! Reloading...';
        toast.success.action(t('system.system.backups.messages.restored'));
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    } catch (error: unknown) {
        stopProgressSimulation();
        restoring.value = false;
        logger.error('Failed to restore backup:', error);
        toast.error.fromResponse(error);
    }
};

const showDeleteModal = ref(false);
const backupToDelete = ref<Backup | null>(null);
const deletePhysicalFile = ref(true);
const deleting = ref(false);

const deleteBackup = (backup: Backup) => {
    backupToDelete.value = backup;
    deletePhysicalFile.value = true;
    showDeleteModal.value = true;
};

const confirmDeleteBackup = async () => {
    if (!backupToDelete.value) return;
    
    deleting.value = true;
    try {
        await api.delete(`/manage/system/backups/${backupToDelete.value.id}?delete_physical=${deletePhysicalFile.value}`);
        toast.success.delete();
        showDeleteModal.value = false;
        await fetchBackups();
    } catch (error: unknown) {
        logger.error('Failed to delete backup:', error);
        toast.error.fromResponse(error);
    } finally {
        deleting.value = false;
        backupToDelete.value = null;
    }
};

const formatFileSize = (bytes: number) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const saveSchedule = async () => {
    savingSchedule.value = true;
    try {
        await api.post('/manage/system/backups/schedule', scheduleForm.value);
        showScheduleModal.value = false;
        await fetchBackups(); // Refresh statistics
        toast.success.save();
    } catch (error: unknown) {
        logger.error('Failed to save schedule:', error);
        toast.error.fromResponse(error);
    } finally {
        savingSchedule.value = false;
    }
};

const openScheduleModal = () => {
    // Populate form from statistics
    if (statistics.value && statistics.value.schedule) {
        scheduleForm.value = {
            backup_schedule_enabled: Boolean(statistics.value.schedule.enabled),
            backup_schedule_frequency: statistics.value.schedule.frequency || 'daily',
            backup_schedule_time: statistics.value.schedule.time || '02:00',
            backup_retention_days: Number(statistics.value.schedule.retention_days) || 30,
            backup_max_count: Number(statistics.value.schedule.max_count) || 10
        };
    }
    initialScheduleForm.value = JSON.parse(JSON.stringify(scheduleForm.value));
    showScheduleModal.value = true;
};

const restoring = ref(false);
const statusText = ref('');
const progress = ref(0);
let progressInterval: ReturnType<typeof setInterval> | null = null;

const startProgressSimulation = (steps: string[]) => {
    progress.value = 0;
    let stepIndex = 0;
    statusText.value = steps[0] ?? '';
    
    progressInterval = setInterval(() => {
        if (progress.value < 95) {
            progress.value += (95 - progress.value) * 0.05;
        }
        
        const expectedIndex = Math.floor((progress.value / 100) * steps.length);
        if (expectedIndex > stepIndex && expectedIndex < steps.length) {
            stepIndex = expectedIndex;
            statusText.value = steps[stepIndex] ?? '';
        }
    }, 300);
};

const stopProgressSimulation = () => {
    if (progressInterval) clearInterval(progressInterval);
    progress.value = 100;
};

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleString(); 
};

onMounted(() => {
    fetchBackups();
});
</script>


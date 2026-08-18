<template>
  <div
    v-if="isActive"
    class="fixed inset-0 bg-black/80 backdrop-blur-md z-[100] flex items-center justify-center p-4"
  >
    <div class="bg-card border border-primary/20 max-w-2xl w-full rounded-2xl shadow-2xl relative overflow-hidden flex flex-col md:flex-row min-h-[420px]">
      <div class="bg-primary/10 p-8 flex flex-col justify-center items-center text-center md:w-2/5 border-b md:border-b-0 md:border-r border-primary/10 shrink-0">
        <div class="bg-primary/20 p-4 rounded-full mb-6">
          <Sparkles class="w-12 h-12 text-primary" />
        </div>
        <h2 class="text-2xl font-bold text-foreground mb-2">Welcome Back</h2>
        <p class="text-sm text-muted-foreground">
          System is clean and ready. Let's get things set up.
        </p>
      </div>

      <div class="p-8 md:w-3/5 flex flex-col relative flex-1 min-h-0">
        <Transition name="wizard-slide" mode="out-in">
          <div v-if="step === 'choice'" key="choice" class="space-y-6 flex-1">
            <div class="space-y-4">
              <h3 class="text-lg font-semibold text-foreground border-b border-border pb-2">
                How do you want to start?
              </h3>

              <button
                type="button"
                class="w-full text-left p-4 rounded-xl border border-border/50 hover:border-primary/50 hover:bg-primary/5 transition-all group relative overflow-hidden disabled:opacity-50"
                :disabled="loading"
                @click="goToSelectBackup"
              >
                <div class="flex items-start gap-3 relative z-10">
                  <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-500 mt-0.5">
                    <DatabaseBackup class="w-5 h-5" />
                  </div>
                  <div>
                    <h4 class="font-bold text-foreground group-hover:text-primary transition-colors">
                      Restore from Backup
                    </h4>
                    <p class="text-xs text-muted-foreground mt-1">
                      Recover settings, media, and data from an existing backup archive.
                    </p>
                  </div>
                </div>
              </button>

              <button
                type="button"
                class="w-full text-left p-4 rounded-xl border border-border/50 hover:border-primary/50 hover:bg-primary/5 transition-all group relative overflow-hidden disabled:opacity-50"
                :disabled="loading"
                @click="handleStartFresh"
              >
                <div class="flex items-start gap-3 relative z-10">
                  <div class="p-2 rounded-lg bg-blue-500/10 text-blue-500 mt-0.5">
                    <Leaf class="w-5 h-5" />
                  </div>
                  <div>
                    <h4 class="font-bold text-foreground group-hover:text-primary transition-colors">
                      Start Fresh
                    </h4>
                    <p class="text-xs text-muted-foreground mt-1">
                      Initialize with factory default settings, roles, and dummy data.
                    </p>
                  </div>
                </div>
              </button>
            </div>
          </div>

          <div v-else-if="step === 'select-backup'" key="select-backup" class="flex flex-col flex-1 min-h-0 space-y-4">
            <div class="flex items-center gap-2 border-b border-border pb-2">
              <button
                type="button"
                class="p-1.5 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                :disabled="loading"
                aria-label="Back"
                @click="goBackToChoice"
              >
                <ArrowLeft class="w-5 h-5" />
              </button>
              <h3 class="text-lg font-semibold text-foreground">Select a backup</h3>
            </div>

            <div v-if="loadingBackups" class="flex-1 flex flex-col items-center justify-center py-8 text-muted-foreground">
              <Loader2 class="w-8 h-8 animate-spin mb-3 text-primary" />
              <p class="text-sm">Loading available backups...</p>
            </div>

            <div v-else-if="completedBackups.length === 0" class="flex-1 flex flex-col items-center justify-center py-8 text-center">
              <DatabaseBackup class="w-10 h-10 text-muted-foreground/40 mb-3" />
              <p class="text-sm text-muted-foreground mb-4">
                No completed backups found. Use Start Fresh or create a backup later from Backup Center.
              </p>
              <button type="button" class="text-sm font-medium text-primary hover:underline" @click="goBackToChoice">
                Back to options
              </button>
            </div>

            <div v-else class="flex flex-col flex-1 min-h-0 space-y-3">
              <p class="text-xs text-muted-foreground">
                Choose a backup archive to restore. This will replace the current database.
              </p>

              <div class="flex-1 overflow-y-auto space-y-2 pr-1 max-h-[240px]">
                <label
                  v-for="backup in completedBackups"
                  :key="backup.id"
                  class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
                  :class="selectedBackupId === backup.id ? 'border-primary bg-primary/5' : 'border-border/50 hover:border-primary/30 hover:bg-muted/30'"
                >
                  <input v-model="selectedBackupId" type="radio" class="mt-1" :value="backup.id" :disabled="loading">
                  <div class="min-w-0 flex-1">
                    <div class="text-sm font-semibold text-foreground truncate">{{ backup.name }}</div>
                    <div class="text-xs text-muted-foreground mt-0.5">
                      {{ formatFileSize(backup.size) }} · {{ formatDate(backup.created_at) }}
                    </div>
                  </div>
                </label>
              </div>

              <button
                type="button"
                class="w-full py-2.5 rounded-xl bg-primary text-primary-foreground font-semibold text-sm hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="loading || !selectedBackupId"
                @click="executeRestore"
              >
                Restore Now
              </button>
            </div>
          </div>
        </Transition>

        <div v-if="loading" class="absolute inset-0 bg-card/90 backdrop-blur-md flex flex-col items-center justify-center z-20 px-8">
          <div class="animate-spin mb-6">
            <Loader2 class="w-16 h-16 text-primary" />
          </div>
          <div class="w-full max-w-sm space-y-2">
            <div class="flex justify-between items-end">
              <span class="text-sm font-semibold text-foreground animate-pulse">{{ statusText }}</span>
              <span class="text-xs font-mono text-muted-foreground">{{ Math.round(progress) }}%</span>
            </div>
            <div class="h-2 w-full bg-secondary overflow-hidden rounded-full relative">
              <div class="absolute top-0 left-0 h-full bg-primary transition-all duration-300 ease-out" :style="{ width: `${progress}%` }" />
              <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full animate-[shimmer_1.5s_infinite]" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Sparkles, DatabaseBackup, Leaf, Loader2, ArrowLeft } from 'lucide-vue-next';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import { logger } from '@/shared/utils/logger';

interface Backup {
    id: string;
    name: string;
    type?: string;
    size: number;
    status?: string;
    created_at: string;
}

type WizardStep = 'choice' | 'select-backup';

const isActive = ref(false);
const step = ref<WizardStep>('choice');
const loading = ref(false);
const loadingBackups = ref(false);
const statusText = ref('');
const progress = ref(0);
const backups = ref<Backup[]>([]);
const selectedBackupId = ref<string | null>(null);
const toast = useToast();

let progressInterval: ReturnType<typeof setInterval> | null = null;

const completedBackups = computed(() =>
    backups.value.filter((b) => !b.status || b.status === 'completed'),
);

const startProgressSimulation = (steps: string[]) => {
    progress.value = 0;
    let stepIndex = 0;
    statusText.value = steps[0] ?? '';

    progressInterval = setInterval(() => {
        if (progress.value < 95) {
            progress.value += (95 - progress.value) * 0.08;
        }

        const expectedIndex = Math.floor((progress.value / 100) * steps.length);
        if (expectedIndex > stepIndex && expectedIndex < steps.length) {
            stepIndex = expectedIndex;
            statusText.value = steps[stepIndex] ?? '';
        }
    }, 200);
};

const stopProgressSimulation = () => {
    if (progressInterval) {
        clearInterval(progressInterval);
        progressInterval = null;
    }
    progress.value = 100;
};

const formatFileSize = (bytes: number) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${Math.round((bytes / Math.pow(k, i)) * 100) / 100} ${sizes[i]}`;
};

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleString();
};

const fetchBackups = async () => {
    loadingBackups.value = true;
    try {
        const response = await api.get('/manage/system/backups');
        const { data } = parseResponse(response);
        backups.value = ensureArray<Backup>(data);
        if (completedBackups.value.length > 0 && !selectedBackupId.value) {
            selectedBackupId.value = completedBackups.value[0]?.id ?? null;
        }
    } catch (e) {
        logger.error('PostResetWizard: failed to fetch backups', e);
        toast.error.default('Failed to load backups');
        backups.value = [];
    } finally {
        loadingBackups.value = false;
    }
};

onMounted(async () => {
    try {
        const res = await api.get('/manage/system/maintenance/post-reset-welcome');
        if (res.data?.active) {
            isActive.value = true;
        }
    } catch {
        // Ignored
    }
});

const goToSelectBackup = async () => {
    step.value = 'select-backup';
    selectedBackupId.value = null;
    await fetchBackups();
};

const goBackToChoice = () => {
    if (loading.value) return;
    step.value = 'choice';
    selectedBackupId.value = null;
};

const executeRestore = async () => {
    if (!selectedBackupId.value) return;

    loading.value = true;
    startProgressSimulation([
        'Extracting Backup Archive...',
        'Restoring Database Schema & Data...',
        'Recovering Media Files...',
        'Finalizing Configuration...',
    ]);

    try {
        await api.post(`/manage/system/backups/${selectedBackupId.value}/restore`);
        try {
            await api.post('/manage/system/maintenance/dismiss-welcome');
        } catch {
            // Flag may already be cleared after restore
        }
        stopProgressSimulation();
        statusText.value = 'Restore complete! Reloading...';
        toast.success.default('Backup restored successfully');
        setTimeout(() => {
            window.location.reload();
        }, 1200);
    } catch (e: unknown) {
        stopProgressSimulation();
        loading.value = false;
        const err = e as { response?: { data?: { message?: string } } };
        toast.error.default(err.response?.data?.message || 'Failed to restore backup');
    }
};

const handleStartFresh = async () => {
    loading.value = true;
    startProgressSimulation([
        'Applying Factory Defaults...',
        'Seeding Foundation Data...',
        'Configuring Roles & Permissions...',
        'Building Local Intelligence...',
        'Finalizing Setup...',
    ]);
    try {
        await api.post('/manage/system/maintenance/seed-fresh', {}, { timeout: 180000 });
        stopProgressSimulation();
        statusText.value = 'System initialized successfully!';
        toast.success.default('System initialized successfully!');

        setTimeout(() => {
            window.location.reload();
        }, 800);
    } catch (e: unknown) {
        stopProgressSimulation();
        const err = e as { response?: { data?: { message?: string } } };
        toast.error.default(err.response?.data?.message || 'Failed to initialize defaults');
        loading.value = false;
    }
};
</script>

<style scoped>
.wizard-slide-enter-active,
.wizard-slide-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.wizard-slide-enter-from {
    opacity: 0;
    transform: translateX(12px);
}

.wizard-slide-leave-to {
    opacity: 0;
    transform: translateX(-12px);
}
</style>

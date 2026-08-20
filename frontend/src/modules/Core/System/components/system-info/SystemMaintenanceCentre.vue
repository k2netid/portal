<template>
  <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
    <div class="flex items-center gap-3 mb-4">
      <Settings class="h-6 w-6 text-primary" />
      <div>
        <h2 class="text-lg font-bold text-foreground">
          {{ t('system.system.info.maintenance.title') }}
        </h2>
        <p class="text-sm text-muted-foreground">
          {{ t('system.system.info.maintenance.subtitle') }}
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
      <!-- Clean Junk Files -->
      <div class="bg-accent/25 border border-border rounded-lg p-5 flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-2 mb-2">
            <Trash2 class="h-5 w-5 text-red-500" />
            <h3 class="font-semibold text-foreground text-sm">{{ t('system.system.info.maintenance.cards.junk.title') }}</h3>
          </div>
          <p class="text-xs text-muted-foreground mb-4">
            {{ t('system.system.info.maintenance.cards.junk.description') }}
          </p>
        </div>
        <button
          type="button"
          @click="handleCleanJunk"
          :disabled="actionLoading"
          class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-xs font-semibold disabled:opacity-50 transition"
        >
          <Trash2 class="h-3.5 w-3.5" />
          {{ t('system.system.info.maintenance.cards.junk.action') }}
        </button>
      </div>

      <!-- Optimize Database -->
      <div class="bg-accent/25 border border-border rounded-lg p-5 flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-2 mb-2">
            <Database class="h-5 w-5 text-indigo-500" />
            <h3 class="font-semibold text-foreground text-sm">{{ t('system.system.info.maintenance.cards.database.title') }}</h3>
          </div>
          <p class="text-xs text-muted-foreground mb-4">
            {{ t('system.system.info.maintenance.cards.database.description') }}
          </p>
        </div>
        <button
          type="button"
          @click="handleOptimizeDb"
          :disabled="actionLoading"
          class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-xs font-semibold disabled:opacity-50 transition"
        >
          <Database class="h-3.5 w-3.5" />
          {{ t('system.system.info.maintenance.cards.database.action') }}
        </button>
      </div>

      <!-- Performance Boost -->
      <div class="bg-accent/25 border border-border rounded-lg p-5 flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-2 mb-2">
            <Zap class="h-5 w-5 text-amber-500" />
            <h3 class="font-semibold text-foreground text-sm">{{ t('system.system.info.maintenance.cards.performance.title') }}</h3>
          </div>
          <p class="text-xs text-muted-foreground mb-4">
            {{ t('system.system.info.maintenance.cards.performance.description') }}
          </p>
        </div>
        <button
          type="button"
          @click="handleBoostPerf"
          :disabled="actionLoading"
          class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-md text-xs font-semibold disabled:opacity-50 transition"
        >
          <Zap class="h-3.5 w-3.5" />
          {{ t('system.system.info.maintenance.cards.performance.action') }}
        </button>
      </div>

      <!-- Factory Reset -->
      <div class="bg-accent/25 border border-border rounded-lg p-5 flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-2 mb-2">
            <RefreshCw class="h-5 w-5 text-rose-600" />
            <h3 class="font-semibold text-foreground text-sm">{{ t('system.system.info.maintenance.cards.reset.title') }}</h3>
          </div>
          <p class="text-xs text-muted-foreground mb-4">
            {{ t('system.system.info.maintenance.cards.reset.description') }}
          </p>
        </div>
        <button
          type="button"
          @click="openResetModal"
          :disabled="actionLoading"
          class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-md text-xs font-semibold disabled:opacity-50 transition"
        >
          <RefreshCw class="h-3.5 w-3.5" />
          {{ t('system.system.info.maintenance.cards.reset.action') }}
        </button>
      </div>
    </div>

    <!-- Operations Console Output -->
    <div v-if="consoleOutput" class="mt-6 border border-border bg-black rounded-lg p-4 font-mono text-xs text-green-400 max-h-48 overflow-y-auto">
      <div class="flex justify-between items-center mb-2 border-b border-green-900 pb-2">
        <span>{{ t('system.system.info.maintenance.console.header') }}</span>
        <button type="button" @click="consoleOutput = ''" class="text-red-400 hover:underline">{{ t('system.system.info.maintenance.console.clear') }}</button>
      </div>
      <pre class="whitespace-pre-wrap">{{ consoleOutput }}</pre>
    </div>

    <!-- Factory Reset Password Confirmation Modal -->
    <div v-if="showResetModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 flex items-center justify-center p-4">
      <div class="bg-card border border-rose-500/30 max-w-lg w-full rounded-xl p-6 shadow-2xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-tr from-rose-500/10 to-transparent pointer-events-none" />
        
        <div class="flex items-center gap-3 mb-6 border-b border-border pb-4">
          <div class="p-3 rounded-xl bg-rose-500/20 text-rose-500">
            <AlertTriangle class="h-8 w-8 animate-pulse" />
          </div>
          <div>
            <h3 class="text-xl font-bold text-foreground">{{ t('system.system.info.maintenance.resetModal.title') }}</h3>
            <p class="text-xs text-muted-foreground">{{ t('system.system.info.maintenance.resetModal.subtitle') }}</p>
          </div>
        </div>

        <!-- Pre-Reset Protections State -->
        <div v-if="!isResetting">
          <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-4 mb-6">
            <p class="text-sm text-amber-500 font-semibold mb-2">Sangat Disarankan: Backup Data Anda</p>
            <p class="text-xs text-amber-500/80 mb-3">
              Sebelum melanjutkan, buat salinan data untuk mencegah kehilangan permanen.
            </p>
            <router-link :to="consolePath('/backups')" class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-500/20 hover:bg-amber-500/30 text-amber-500 rounded text-xs font-semibold transition">
              <Download class="h-3.5 w-3.5" /> Lakukan Backup Sekarang
            </router-link>
          </div>

          <div class="space-y-3 mb-6">
            <label class="flex items-start gap-3 cursor-pointer group">
              <div class="pt-0.5">
                <input type="checkbox" v-model="resetConfirm1" class="w-4 h-4 rounded border-border text-rose-600 focus:ring-rose-500 bg-accent" />
              </div>
              <span class="text-xs text-foreground group-hover:text-rose-400 transition">Saya mengerti bahwa <strong>seluruh isi database</strong> akan dikosongkan.</span>
            </label>
            
            <label class="flex items-start gap-3 cursor-pointer group">
              <div class="pt-0.5">
                <input type="checkbox" v-model="resetConfirm2" class="w-4 h-4 rounded border-border text-rose-600 focus:ring-rose-500 bg-accent" />
              </div>
              <span class="text-xs text-foreground group-hover:text-rose-400 transition">Saya mengerti bahwa <strong>seluruh file media dan unggahan</strong> akan dihapus permanen.</span>
            </label>

            <label class="flex items-start gap-3 cursor-pointer group">
              <div class="pt-0.5">
                <input type="checkbox" v-model="resetConfirm3" class="w-4 h-4 rounded border-border text-rose-600 focus:ring-rose-500 bg-accent" />
              </div>
              <span class="text-xs text-foreground group-hover:text-rose-400 transition">Saya mengerti bahwa tindakan ini <strong>tidak dapat dibatalkan</strong>.</span>
            </label>
          </div>

          <div class="space-y-4 mb-2">
            <div>
              <label class="block text-xs font-semibold text-muted-foreground mb-1.5">
                Ketik <strong class="text-foreground select-all">RESET SYSTEM</strong> untuk mengonfirmasi
              </label>
              <input
                type="text"
                v-model="resetChallenge"
                placeholder="RESET SYSTEM"
                class="w-full px-3 py-2 bg-accent/20 border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-rose-500 font-mono uppercase tracking-widest"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-muted-foreground mb-1.5">
                {{ t('system.system.info.maintenance.resetModal.passwordLabel') }}
              </label>
              <input
                type="password"
                v-model="resetPassword"
                :placeholder="t('common.placeholders.passwordMask')"
                class="w-full px-3 py-2 bg-accent/20 border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-rose-500"
              />
            </div>
          </div>

          <div class="flex justify-end gap-2 mt-6 border-t border-border pt-4">
            <button
              type="button"
              @click="showResetModal = false"
              class="px-4 py-2 bg-accent hover:bg-accent/80 text-foreground rounded-lg text-xs font-semibold transition"
            >
              {{ t('system.system.info.maintenance.resetModal.cancel') }}
            </button>
            <button
              type="button"
              @click="handleFactoryReset"
              :disabled="!canReset"
              class="px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-2"
            >
              <RefreshCw class="h-3.5 w-3.5" /> MULAI FACTORY RESET
            </button>
          </div>
        </div>

        <!-- Resetting Progress State -->
        <div v-else class="py-6 text-center">
          <div class="mb-6 relative w-24 h-24 mx-auto">
            <RefreshCw class="h-24 w-24 text-rose-500 animate-spin opacity-20" />
            <div class="absolute inset-0 flex items-center justify-center">
              <span class="text-xl font-bold text-foreground">{{ resetProgress }}%</span>
            </div>
          </div>
          
          <h4 class="text-lg font-bold text-foreground mb-2">Memproses Factory Reset</h4>
          <p class="text-sm text-rose-400 font-mono mb-6 animate-pulse">{{ resetStepText }}</p>
          
          <div class="w-full bg-accent/50 rounded-full h-2 mb-4 overflow-hidden border border-border">
            <div class="bg-rose-500 h-2 rounded-full transition-all duration-500 ease-out" :style="{ width: `${resetProgress}%` }"></div>
          </div>
          
          <p class="text-xs text-muted-foreground">Mohon jangan menutup jendela ini atau me-refresh browser.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { consolePath } from '@/shared/utils/consoleRoute';
import { logger } from '@/shared/utils/logger';
import api from '@/engine/api/client';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import {
  AlertTriangle,
  Database,
  Download,
  RefreshCw,
  Settings,
  Trash2,
  Zap,
} from 'lucide-vue-next';

const { t } = useI18n();

const actionLoading = ref(false);
const consoleOutput = ref('');
const showResetModal = ref(false);
const resetPassword = ref('');

const resetConfirm1 = ref(false);
const resetConfirm2 = ref(false);
const resetConfirm3 = ref(false);
const resetChallenge = ref('');
const resetProgress = ref(0);
const resetStepText = ref('');
const isResetting = ref(false);

const canReset = computed(() => {
    return resetConfirm1.value && 
           resetConfirm2.value && 
           resetConfirm3.value && 
           resetChallenge.value === 'RESET SYSTEM' && 
           resetPassword.value.length > 0 &&
           !isResetting.value;
});

const formatBytes = (bytes: number): string => {
    if (!bytes || typeof bytes !== 'number') return '-';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const appendConsole = (msg: string): void => {
    const timestamp = new Date().toLocaleTimeString();
    consoleOutput.value += `[${timestamp}] ${msg}\n`;
};

const handleCleanJunk = async (): Promise<void> => {
    actionLoading.value = true;
    appendConsole(t('system.system.info.maintenance.logs.junk.start'));
    try {
        const res = await api.post('/manage/system/maintenance/clean-junk');
        const data = parseSingleResponse<{ deleted_files?: number; files_removed?: number; freed_bytes?: number; cleaned_bytes?: number }>(res);
        const count = data?.deleted_files ?? data?.files_removed ?? 0;
        const bytes = data?.freed_bytes ?? data?.cleaned_bytes ?? 0;
        appendConsole(t('system.system.info.maintenance.logs.junk.success'));
        appendConsole(t('system.system.info.maintenance.logs.junk.filesDeleted', { count }));
        appendConsole(t('system.system.info.maintenance.logs.junk.spaceFreed', { size: formatBytes(bytes) }));
    } catch (err: unknown) {
        logger.error('Failed to clean junk:', err);
        const message = err instanceof Error ? err.message : String(err);
        appendConsole(t('system.system.info.maintenance.logs.junk.error', { message }));
    } finally {
        actionLoading.value = false;
    }
};

const handleOptimizeDb = async (): Promise<void> => {
    actionLoading.value = true;
    appendConsole(t('system.system.info.maintenance.logs.database.start'));
    try {
        const res = await api.post('/manage/system/maintenance/optimize-db');
        const data = parseSingleResponse<{ optimized_tables: number; purged_orphans: number }>(res);
        appendConsole(t('system.system.info.maintenance.logs.database.success'));
        appendConsole(t('system.system.info.maintenance.logs.database.tablesOptimized', { count: data?.optimized_tables || 0 }));
        appendConsole(t('system.system.info.maintenance.logs.database.orphansPurged', { count: data?.purged_orphans || 0 }));
    } catch (err: unknown) {
        logger.error('Failed to optimize database:', err);
        const message = err instanceof Error ? err.message : String(err);
        appendConsole(t('system.system.info.maintenance.logs.database.error', { message }));
    } finally {
        actionLoading.value = false;
    }
};

const handleBoostPerf = async (): Promise<void> => {
    actionLoading.value = true;
    appendConsole(t('system.system.info.maintenance.logs.performance.start'));
    try {
        await api.post('/manage/system/maintenance/boost');
        appendConsole(t('system.system.info.maintenance.logs.performance.success'));
        appendConsole(t('system.system.info.maintenance.logs.performance.mode'));
    } catch (err: unknown) {
        logger.error('Failed to boost performance:', err);
        const message = err instanceof Error ? err.message : String(err);
        appendConsole(t('system.system.info.maintenance.logs.performance.error', { message }));
    } finally {
        actionLoading.value = false;
    }
};

const openResetModal = (): void => {
    resetPassword.value = '';
    resetConfirm1.value = false;
    resetConfirm2.value = false;
    resetConfirm3.value = false;
    resetChallenge.value = '';
    resetProgress.value = 0;
    resetStepText.value = '';
    isResetting.value = false;
    showResetModal.value = true;
};

const handleFactoryReset = async (): Promise<void> => {
    if (!canReset.value) return;
    
    isResetting.value = true;
    resetProgress.value = 10;
    resetStepText.value = 'Preparing Factory Reset...';
    appendConsole(t('system.system.info.maintenance.logs.reset.start'));

    try {
        window.__factoryResetInProgress = true;
        const payload = { password: resetPassword.value };

        resetStepText.value = 'Step 1/3: Clearing Sandboxes & Cache...';
        await api.post('/manage/system/maintenance/factory-reset/step-1', payload);
        resetProgress.value = 40;
        appendConsole('Sandbox & Caches cleared.');

        resetStepText.value = 'Step 2/3: Wiping Media & Logs...';
        await api.post('/manage/system/maintenance/factory-reset/step-2', payload);
        resetProgress.value = 75;
        appendConsole('Media and logs wiped.');

        resetStepText.value = 'Step 3/3: Wiping Database Schema...';
        const res = await api.post('/manage/system/maintenance/factory-reset/step-3', payload);
        const setupToken = String(
            (res.data as { setup_token?: string })?.setup_token
            ?? (res.data as { data?: { setup_token?: string } })?.data?.setup_token
            ?? '',
        ).trim();

        if (!setupToken) {
            throw new Error('Setup token missing from factory reset response');
        }

        resetProgress.value = 100;
        resetStepText.value = 'Reset Complete. Redirecting to setup...';
        appendConsole('Database migrated. Redirecting to post-reset setup.');

        const { resetLockdown } = await import('@/engine/api/client');
        resetLockdown();
        window.__factoryResetInProgress = true;
        window.location.replace(`/setup?token=${encodeURIComponent(setupToken)}`);
        return;
    } catch (err: unknown) {
        window.__factoryResetInProgress = false;
        logger.error('Failed to factory reset:', err);
        const message = err instanceof Error ? err.message : String(err);
        appendConsole(t('system.system.info.maintenance.logs.reset.error', { message }));
        isResetting.value = false;
    }
};
</script>

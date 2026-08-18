<template>
  <div class="space-y-6">
    <PageHeader
borderless
      :title="t('system.system.info.title')"
    :subtitle="t('system.system.info.subtitle')"
    >
    </PageHeader>

<ConsoleListCard>
      <div class="p-6 space-y-6">
<!-- Loading Skeleton -->
    <div
      v-if="loading"
      class="space-y-6"
    >
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-card border border-border rounded-lg p-6 h-24" />
        <div class="bg-card border border-border rounded-lg p-6 h-24" />
        <div class="bg-card border border-border rounded-lg p-6 h-24" />
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-card border border-border rounded-lg p-6 h-64" />
        <div class="bg-card border border-border rounded-lg p-6 h-64" />
      </div>
    </div>

    <!-- Main Content (only show when loaded) -->
    <template v-else>
      <!-- System Health -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-card border border-border rounded-lg p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-muted-foreground">
                {{ t('system.system.info.health.title') }}
              </p>
              <p
                class="text-2xl font-semibold mt-1"
                :class="systemHealth === 'healthy' ? 'text-green-600' : systemHealth === 'warning' ? 'text-yellow-600' : 'text-red-600'"
              >
                {{ systemHealth === 'healthy' ? t('system.system.info.health.healthy') : systemHealth === 'warning' ? t('system.system.info.health.warning') : t('system.system.info.health.critical') }}
              </p>
            </div>
            <div>
              <CheckCircle
                v-if="systemHealth === 'healthy'"
                class="h-12 w-12 text-green-600"
              />
              <AlertTriangle
                v-else
                class="h-12 w-12"
                :class="systemHealth === 'warning' ? 'text-yellow-600' : 'text-red-600'"
              />
            </div>
          </div>
        </div>
        <div class="bg-card border border-border rounded-lg p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <Zap class="h-8 w-8 text-indigo-600 dark:text-indigo-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-muted-foreground">
                {{ t('system.system.info.cache.title') }}
              </p>
              <p class="text-2xl font-semibold text-foreground">
                {{ cacheStatusLabel }}
              </p>
            </div>
          </div>
        </div>
        <div class="bg-card border border-border rounded-lg p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ShieldCheck class="h-8 w-8 text-green-600 dark:text-green-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-muted-foreground">
                {{ t('system.system.info.uptime') }}
              </p>
              <p class="text-2xl font-semibold text-foreground">
                {{ formatUptime(systemInfo.uptime) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- System Info -->
        <div class="lg:col-span-2 bg-card border border-border rounded-lg p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-foreground">
              {{ t('system.system.info.title') }}
            </h2>
            <router-link
              :to="consolePath('/settings?tab=performance')"
              class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 text-sm"
            >
              <RotateCcw class="h-4 w-4" />
              {{ t('system.system.info.cache.manage') }}
            </router-link>
          </div>
          <div class="grid grid-cols-1 gap-6">
            <div>
              <h3 class="text-sm font-medium text-foreground mb-3 font-bold border-b pb-1">
                {{ t('system.system.info.sections.application') }}
              </h3>
              <dl class="space-y-2">
                <div class="flex justify-between">
                  <dt class="text-sm text-muted-foreground">
                    {{ t('system.system.info.sections.phpVersion') }}
                  </dt>
                  <dd class="text-sm text-foreground font-mono">
                    {{ systemInfo.php_version || '-' }}
                  </dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-muted-foreground">
                    {{ t('system.system.info.sections.laravelVersion') }}
                  </dt>
                  <dd class="text-sm text-foreground font-mono">
                    {{ systemInfo.laravel_version || '-' }}
                  </dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-muted-foreground">
                    {{ t('system.system.info.sections.environment') }}
                  </dt>
                  <dd class="text-sm text-foreground capitalize">
                    {{ systemInfo.environment || '-' }}
                  </dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-muted-foreground">
                    {{ t('system.system.info.sections.debugMode') }}
                  </dt>
                  <dd
                    class="text-sm"
                    :class="systemInfo.debug_mode ? 'text-red-500' : 'text-foreground'"
                  >
                    {{ systemInfo.debug_mode ? t('system.system.info.sections.enabled') : t('system.system.info.sections.disabled') }}
                  </dd>
                </div>
              </dl>
            </div>
            <div>
              <h3 class="text-sm font-medium text-foreground mb-3 font-bold border-b pb-1">
                {{ t('system.system.info.sections.server') }}
              </h3>
              <dl class="space-y-2">
                <div class="flex justify-between">
                  <dt class="text-sm text-muted-foreground">
                    {{ t('system.system.info.sections.serverSoftware') }}
                  </dt>
                  <dd
                    class="text-sm text-foreground truncate max-w-[200px]"
                    :title="systemInfo.server_software"
                  >
                    {{ systemInfo.server_software || '-' }}
                  </dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-muted-foreground">
                    {{ t('system.system.info.sections.memoryUsage') }}
                  </dt>
                  <dd class="text-sm text-foreground font-mono">
                    {{ displayMemory }}
                  </dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-muted-foreground">
                    {{ t('system.system.info.sections.diskUsage') }}
                  </dt>
                  <dd class="text-sm text-foreground font-mono">
                    {{ displayDisk }}
                  </dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-muted-foreground">
                    {{ t('system.system.info.sections.database') }}
                  </dt>
                  <dd class="text-sm text-foreground font-semibold">
                    {{ systemInfo.database || '-' }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-card border border-border rounded-lg p-6">
          <h2 class="text-lg font-semibold text-foreground mb-4">
            {{ t('system.system.info.quickActions.title') }}
          </h2>
          <div class="grid grid-cols-2 gap-3">
            <router-link
              :to="consolePath('/settings')"
              class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50"
            >
              <Settings class="h-8 w-8 text-primary mb-2" />
              <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.settings') }}</span>
            </router-link>
                    
            <router-link
              :to="consolePath('/backups')"
              class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50"
            >
              <Download class="h-8 w-8 text-green-600 dark:text-green-400 mb-2" />
              <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.backups') }}</span>
            </router-link>
                    
            <router-link
              :to="consolePath('/redis')"
              class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50"
            >
              <Database class="h-8 w-8 text-red-500 dark:text-red-400 mb-2" />
              <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.redis') }}</span>
            </router-link>
                    
            <router-link
              :to="consolePath('/scheduled-tasks')"
              class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50"
            >
              <Clock class="h-8 w-8 text-blue-500 dark:text-blue-400 mb-2" />
              <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.scheduledTasks') }}</span>
            </router-link>
                    
            <router-link
              :to="consolePath('/scheduled-tasks?action=run_command')"
              class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50"
            >
              <Terminal class="h-8 w-8 text-yellow-500 dark:text-yellow-400 mb-2" />
              <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.commandRunner') }}</span>
            </router-link>

            <router-link
              :to="consolePath('/system/notifications')"
              class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50"
            >
              <Bell class="h-8 w-8 text-purple-500 dark:text-purple-400 mb-2" />
              <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.notifications') }}</span>
            </router-link>

            <router-link
              :to="consolePath('/settings?tab=email')"
              class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50"
            >
              <Mail class="h-8 w-8 text-orange-500 dark:text-orange-400 mb-2" />
              <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.emailSettings') }}</span>
            </router-link>

            <router-link
              :to="consolePath('/email-templates')"
              class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50"
            >
              <FileText class="h-8 w-8 text-sky-500 dark:text-sky-400 mb-2" />
              <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.emailTemplates') }}</span>
            </router-link>
          </div>
        </div>
      </div>

      <!-- System Care & Maintenance Centre -->
      <div class="mt-6 bg-card border border-border/80 rounded-xl p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
          <Settings class="h-6 w-6 text-primary animate-spin-slow" />
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
            <button @click="consoleOutput = ''" class="text-red-400 hover:underline">{{ t('system.system.info.maintenance.console.clear') }}</button>
          </div>
          <pre class="whitespace-pre-wrap">{{ consoleOutput }}</pre>
        </div>
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
              <h3 class="text-xl font-bold text-foreground">Peringatan: Factory Reset</h3>
              <p class="text-xs text-muted-foreground">Tindakan ini bersifat destruktif dan permanen.</p>
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
                @click="showResetModal = false"
                class="px-4 py-2 bg-accent hover:bg-accent/80 text-foreground rounded-lg text-xs font-semibold transition"
              >
                {{ t('system.system.info.maintenance.resetModal.cancel') }}
              </button>
              <button
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
    </template>
      </div>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { consolePath } from '@/shared/utils/consoleRoute';
import {PageHeader, ConsoleListCard} from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import {
  AlertTriangle,
  CheckCircle,
  Clock,
  Database,
  Download,
  FileText,
  Mail,
  RefreshCw,
  RotateCcw,
  Settings,
  ShieldCheck,
  Terminal,
  Trash2,
  Zap,
} from 'lucide-vue-next';

interface DiskUsage {
    used: string;
    total: string;
    percent?: number;
}

interface SystemInfo {
    uptime: number;
    php_version: string;
    laravel_version: string;
    environment: string;
    debug_mode: boolean;
    server_software: string;
    memory_usage: string;
    memory_usage_percent: number;
    disk_usage: DiskUsage | string;
    disk_usage_percent: number;
    database: string;
}

interface CacheData {
    status: string;
}

const { t } = useI18n();

const loading = ref(true);
const systemInfo = ref<Partial<SystemInfo>>({});
const CACHE_STATUS_ACTIVE = 'active';
const cacheStatus = ref(CACHE_STATUS_ACTIVE);

const cacheStatusLabel = computed(() => {
    const s = (cacheStatus.value || '').toLowerCase();
    if (s === 'active' || s === 'aktif') return t('system.system.info.cache.active');
    return t('system.system.info.cache.inactive');
});

const systemHealth = computed(() => {
    if (!systemInfo.value) return 'healthy';
    
    const memoryUsage = systemInfo.value.memory_usage_percent || 0;
    const diskUsage = systemInfo.value.disk_usage_percent || 0;
    
    if (memoryUsage > 90 || diskUsage > 90) return 'critical';
    if (memoryUsage > 75 || diskUsage > 75) return 'warning';
    return 'healthy';
});

// Computed for display - handle pre-formatted strings from backend
const displayMemory = computed(() => {
    if (!systemInfo.value.memory_usage) return '-';
    // Backend now sends formatted string like "2.44 GB"
    if (typeof systemInfo.value.memory_usage === 'string') {
        return systemInfo.value.memory_usage;
    }
    return formatBytes(systemInfo.value.memory_usage as number);
});

const displayDisk = computed(() => {
    const usage = systemInfo.value.disk_usage;
    if (!usage) return '-';
    if (typeof usage === 'object') {
        // Backend sends: { used: "30.85 GB", total: "97.87 GB", percent: 31.52 }
        return `${usage.used} / ${usage.total} (${usage.percent || 0}%)`;
    }
    return usage;
});

const fetchSystemInfo = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await api.get('/manage/system/info');
        systemInfo.value = parseSingleResponse<SystemInfo>(response) || {};

        // Fetch cache status
        try {
            const cacheResponse = await api.get('/manage/system/cache-status');
            cacheStatus.value = parseSingleResponse<CacheData>(cacheResponse)?.status || CACHE_STATUS_ACTIVE;
        } catch (error: unknown) {
            logger.warning('Failed to fetch cache status:', error);
            cacheStatus.value = CACHE_STATUS_ACTIVE;
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch system info:', error);
    } finally {
        loading.value = false;
    }
};

const formatUptime = (seconds?: number) : string => {
    if (!seconds) return '-';
    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    return `${days}d ${hours}h ${minutes}m`;
};

const formatBytes = (bytes: number) : string => {
    if (!bytes || typeof bytes !== 'number') return '-';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

// Maintenance Centre State & Handlers
const actionLoading = ref(false);
const consoleOutput = ref('');
const showResetModal = ref(false);
const resetPassword = ref('');

// Advanced Reset Protections
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

const appendConsole = (msg: string): void => {
    const timestamp = new Date().toLocaleTimeString();
    consoleOutput.value += `[${timestamp}] ${msg}\n`;
};

const handleCleanJunk = async (): Promise<void> => {
    actionLoading.value = true;
    appendConsole(t('system.system.info.maintenance.logs.junk.start'));
    try {
        const res = await api.post('/manage/system/maintenance/clean-junk');
        const data = parseSingleResponse<{ deleted_files: number; freed_bytes: number }>(res);
        appendConsole(t('system.system.info.maintenance.logs.junk.success'));
        appendConsole(t('system.system.info.maintenance.logs.junk.filesDeleted', { count: data?.deleted_files || 0 }));
        appendConsole(t('system.system.info.maintenance.logs.junk.spaceFreed', { size: formatBytes(data?.freed_bytes || 0) }));
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

        // Redirect immediately — DB wipe invalidates session; delay allows heartbeat → /419
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

onMounted(() => {
    fetchSystemInfo();
});
</script>

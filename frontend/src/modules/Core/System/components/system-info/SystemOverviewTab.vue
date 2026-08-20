<template>
  <div class="space-y-6">
    <!-- System Health Top Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-card border border-border rounded-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-muted-foreground">
              {{ t('system.system.info.health.title') }}
            </p>
            <p
              class="text-2xl font-semibold mt-1"
              :class="systemHealth === 'healthy' ? 'text-green-600 dark:text-green-400' : systemHealth === 'warning' ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400'"
            >
              {{ systemHealth === 'healthy' ? t('system.system.info.health.healthy') : systemHealth === 'warning' ? t('system.system.info.health.warning') : t('system.system.info.health.critical') }}
            </p>
          </div>
          <div>
            <CheckCircle
              v-if="systemHealth === 'healthy'"
              class="h-12 w-12 text-green-600 dark:text-green-400"
            />
            <AlertTriangle
              v-else
              class="h-12 w-12"
              :class="systemHealth === 'warning' ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400'"
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
            <Clock class="h-8 w-8 text-sky-600 dark:text-sky-400" />
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
      <!-- System Info Specifications Card -->
      <div class="lg:col-span-2 bg-card border border-border rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-foreground">
            {{ t('system.system.info.title') }}
          </h2>
          <router-link
            :to="consolePath('/settings?tab=performance')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 text-sm font-medium transition"
          >
            <RotateCcw class="h-4 w-4" />
            {{ t('system.system.info.cache.manage') }}
          </router-link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Application -->
          <div>
            <h3 class="text-sm font-bold text-foreground mb-3 border-b border-border pb-1">
              {{ t('system.system.info.sections.application') }}
            </h3>
            <dl class="space-y-2.5">
              <div class="flex justify-between items-center">
                <dt class="text-sm text-muted-foreground">
                  {{ t('system.system.info.sections.phpVersion') }}
                </dt>
                <dd class="text-sm text-foreground font-mono font-semibold">
                  {{ systemInfo.php_version || '-' }}
                </dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-sm text-muted-foreground">
                  {{ t('system.system.info.sections.laravelVersion') }}
                </dt>
                <dd class="text-sm text-foreground font-mono font-semibold">
                  {{ systemInfo.laravel_version || '-' }}
                </dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-sm text-muted-foreground">
                  {{ t('system.system.info.sections.environment') }}
                </dt>
                <dd class="text-sm text-foreground capitalize font-medium">
                  {{ systemInfo.environment || '-' }}
                </dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-sm text-muted-foreground">
                  {{ t('system.system.info.sections.debugMode') }}
                </dt>
                <dd
                  class="text-sm font-medium"
                  :class="systemInfo.debug_mode ? 'text-red-500' : 'text-foreground'"
                >
                  {{ systemInfo.debug_mode ? t('system.system.info.sections.enabled') : t('system.system.info.sections.disabled') }}
                </dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-sm text-muted-foreground">
                  {{ t('system.system.info.sections.cacheSessionDriver') }}
                </dt>
                <dd class="text-xs font-mono bg-accent px-2 py-0.5 rounded text-foreground">
                  {{ systemInfo.cache_driver }} / {{ systemInfo.session_driver }}
                </dd>
              </div>
            </dl>
          </div>

          <!-- Server & OS -->
          <div>
            <h3 class="text-sm font-bold text-foreground mb-3 border-b border-border pb-1">
              {{ t('system.system.info.sections.server') }}
            </h3>
            <dl class="space-y-2.5">
              <div class="flex justify-between items-center">
                <dt class="text-sm text-muted-foreground">
                  {{ t('system.system.info.sections.osDistro') }}
                </dt>
                <dd class="text-sm text-foreground font-semibold truncate max-w-[200px]" :title="systemInfo.os_distro">
                  {{ systemInfo.os_distro || '-' }}
                </dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-sm text-muted-foreground">
                  {{ t('system.system.info.sections.serverSoftware') }}
                </dt>
                <dd
                  class="text-sm text-foreground truncate max-w-[200px] font-mono text-xs"
                  :title="systemInfo.server_software"
                >
                  {{ systemInfo.server_software || '-' }}
                </dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-sm text-muted-foreground">
                  {{ t('system.system.info.sections.memoryUsage') }}
                </dt>
                <dd class="text-sm text-foreground font-mono">
                  {{ displayMemory }}
                </dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-sm text-muted-foreground">
                  {{ t('system.system.info.sections.diskUsage') }}
                </dt>
                <dd class="text-sm text-foreground font-mono">
                  {{ displayDisk }}
                </dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-sm text-muted-foreground">
                  {{ t('system.system.info.sections.database') }}
                </dt>
                <dd class="text-sm text-foreground font-semibold truncate max-w-[200px]" :title="systemInfo.database_version">
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
            class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition border border-transparent hover:border-border"
          >
            <Settings class="h-7 w-7 text-primary mb-2" />
            <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.settings') }}</span>
          </router-link>
                  
          <router-link
            :to="consolePath('/backups')"
            class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition border border-transparent hover:border-border"
          >
            <Download class="h-7 w-7 text-green-600 dark:text-green-400 mb-2" />
            <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.backups') }}</span>
          </router-link>
                  
          <router-link
            :to="consolePath('/redis')"
            class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition border border-transparent hover:border-border"
          >
            <Database class="h-7 w-7 text-red-500 dark:text-red-400 mb-2" />
            <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.redis') }}</span>
          </router-link>
                  
          <router-link
            :to="consolePath('/scheduled-tasks')"
            class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition border border-transparent hover:border-border"
          >
            <Clock class="h-7 w-7 text-blue-500 dark:text-blue-400 mb-2" />
            <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.scheduledTasks') }}</span>
          </router-link>
                  
          <router-link
            :to="consolePath('/scheduled-tasks?action=run_command')"
            class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition border border-transparent hover:border-border"
          >
            <Terminal class="h-7 w-7 text-yellow-500 dark:text-yellow-400 mb-2" />
            <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.commandRunner') }}</span>
          </router-link>

          <router-link
            :to="consolePath('/system/notifications')"
            class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition border border-transparent hover:border-border"
          >
            <Bell class="h-7 w-7 text-purple-500 dark:text-purple-400 mb-2" />
            <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.notifications') }}</span>
          </router-link>

          <router-link
            :to="consolePath('/settings?tab=email')"
            class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition border border-transparent hover:border-border"
          >
            <Mail class="h-7 w-7 text-orange-500 dark:text-orange-400 mb-2" />
            <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.emailSettings') }}</span>
          </router-link>

          <router-link
            :to="consolePath('/email-templates')"
            class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition border border-transparent hover:border-border"
          >
            <FileText class="h-7 w-7 text-sky-500 dark:text-sky-400 mb-2" />
            <span class="text-xs font-medium text-foreground text-center">{{ t('system.system.info.quickActions.emailTemplates') }}</span>
          </router-link>
        </div>
      </div>
    </div>

    <!-- System Care & Maintenance Centre Subcomponent -->
    <SystemMaintenanceCentre />
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { consolePath } from '@/shared/utils/consoleRoute';
import type { SystemInfo } from './types';
import SystemMaintenanceCentre from './SystemMaintenanceCentre.vue';
import {
  AlertTriangle,
  Bell,
  CheckCircle,
  Clock,
  Database,
  Download,
  FileText,
  Mail,
  RotateCcw,
  Settings,
  Terminal,
  Zap,
} from 'lucide-vue-next';

defineProps<{
  systemInfo: Partial<SystemInfo>;
  systemHealth: string;
  cacheStatusLabel: string;
  displayMemory: string;
  displayDisk: string;
}>();

const { t } = useI18n();

const formatUptime = (seconds?: number): string => {
    if (!seconds) return '-';
    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    return `${days}d ${hours}h ${minutes}m`;
};
</script>

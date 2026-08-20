<template>
  <div class="space-y-6">
    <!-- Score & Quick Actions Banner -->
    <div class="bg-gradient-to-br from-card to-accent/30 border border-border rounded-xl p-6 shadow-sm">
      <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <!-- Score Breakdown -->
        <div class="flex items-center gap-5">
          <div
            class="w-20 h-20 rounded-2xl flex flex-col items-center justify-center border-2 font-black flex-shrink-0"
            :class="requirementsData?.overview?.is_ready ? 'bg-green-500/10 text-green-600 dark:text-green-400 border-green-500/30' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30'"
          >
            <span class="text-2xl leading-none">{{ requirementsData?.overview?.score_percent || 0 }}%</span>
            <span class="text-[10px] tracking-wider uppercase mt-1 font-bold">Kesiapan</span>
          </div>

          <div>
            <div class="flex items-center gap-2 mb-1 flex-wrap">
              <h2 class="text-xl font-bold text-foreground">
                {{ t('system.system.info.requirements.title') }}
              </h2>
              <span
                class="px-2.5 py-0.5 text-xs font-bold rounded-full"
                :class="requirementsData?.overview?.is_ready ? 'bg-green-500/15 text-green-600 dark:text-green-400' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400'"
              >
                {{ requirementsData?.overview?.is_ready ? t('system.system.info.requirements.ready') : t('system.system.info.requirements.needs_attention') }}
              </span>
            </div>
            <p class="text-xs text-muted-foreground max-w-xl">
              {{ t('system.system.info.requirements.subtitle') }}
            </p>

            <!-- Summary Badges -->
            <div class="flex flex-wrap items-center gap-2 mt-3 text-xs">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-green-500/10 text-green-600 dark:text-green-400 font-semibold border border-green-500/20">
                <CheckCircle class="h-3.5 w-3.5" />
                {{ requirementsData?.overview?.passed || 0 }} Lulus
              </span>
              <span v-if="requirementsData?.overview?.warnings" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 font-semibold border border-amber-500/20">
                <AlertTriangle class="h-3.5 w-3.5" />
                {{ t('system.system.info.requirements.warnings_count', { count: requirementsData.overview.warnings }) }}
              </span>
              <span v-if="requirementsData?.overview?.errors" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-red-500/10 text-red-600 dark:text-red-400 font-semibold border border-red-500/20">
                <XCircle class="h-3.5 w-3.5" />
                {{ t('system.system.info.requirements.errors_count', { count: requirementsData.overview.errors }) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2 self-stretch lg:self-auto justify-end">
          <button
            type="button"
            @click="handleAutoFix"
            :disabled="autoFixing || reqLoading"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg text-xs font-bold shadow-sm transition disabled:opacity-50"
          >
            <Wrench v-if="!autoFixing" class="h-4 w-4" />
            <Loader2 v-else class="h-4 w-4 animate-spin" />
            <span>{{ autoFixing ? t('system.system.info.requirements.autofixing') : t('system.system.info.requirements.autofix_btn') }}</span>
          </button>

          <button
            type="button"
            @click="emit('refresh')"
            :disabled="reqLoading"
            class="inline-flex items-center gap-2 px-3.5 py-2 bg-accent hover:bg-accent/80 text-foreground rounded-lg text-xs font-semibold border border-border transition"
          >
            <RotateCcw class="h-3.5 w-3.5" :class="reqLoading ? 'animate-spin' : ''" />
            <span>{{ t('system.system.info.requirements.recheck') }}</span>
          </button>
        </div>
      </div>

      <!-- Server Specs Quick Bar -->
      <div v-if="requirementsData?.server_spec" class="mt-6 pt-4 border-t border-border/70 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
          <span class="text-[10px] text-muted-foreground block font-medium">OS / Distro</span>
          <span class="text-xs font-bold text-foreground truncate block" :title="requirementsData.server_spec.distro">
            {{ requirementsData.server_spec.distro }}
          </span>
        </div>
        <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
          <span class="text-[10px] text-muted-foreground block font-medium">PHP Runtime</span>
          <span class="text-xs font-bold text-foreground font-mono block">
            {{ requirementsData.server_spec.php_version }} ({{ requirementsData.server_spec.php_sapi }})
          </span>
        </div>
        <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
          <span class="text-[10px] text-muted-foreground block font-medium">Database</span>
          <span class="text-xs font-bold text-foreground truncate block" :title="requirementsData.server_spec.database_version">
            {{ requirementsData.server_spec.database_engine.toUpperCase() }}
          </span>
        </div>
        <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
          <span class="text-[10px] text-muted-foreground block font-medium">Redis In-Memory</span>
          <span class="text-xs font-bold text-foreground block">
            v{{ requirementsData.server_spec.redis_version }} ({{ requirementsData.server_spec.redis_latency }})
          </span>
        </div>
        <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
          <span class="text-[10px] text-muted-foreground block font-medium">Queue Worker</span>
          <span class="text-xs font-bold text-foreground block">
            {{ requirementsData.server_spec.queue_workers_count > 0 ? `${requirementsData.server_spec.queue_workers_count} Active` : 'Sync / Idle' }}
          </span>
        </div>
        <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
          <span class="text-[10px] text-muted-foreground block font-medium">Scheduler Cron</span>
          <span
            class="text-xs font-bold block"
            :class="requirementsData.server_spec.cron_configured ? 'text-green-600 dark:text-green-400' : 'text-amber-500'"
          >
            {{ requirementsData.server_spec.cron_configured ? 'Configured (* * * * *)' : 'Unconfigured' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Refined Compact Filter Toolbar (Dropdown Filters & Search) -->
    <div class="bg-card border border-border rounded-xl p-3.5 shadow-sm">
      <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <!-- Left: Dropdown Filter Controls -->
        <div class="flex flex-wrap items-center gap-2.5 flex-1">
          <!-- Category Dropdown -->
          <div class="relative min-w-[200px] flex-1 sm:flex-initial">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-muted-foreground">
              <Filter class="h-3.5 w-3.5" />
            </div>
            <select
              v-model="selectedCategory"
              class="w-full pl-8 pr-8 py-2 bg-accent/30 hover:bg-accent/50 border border-border rounded-lg text-xs font-semibold text-foreground focus:outline-none focus:ring-2 focus:ring-primary/40 appearance-none cursor-pointer transition"
            >
              <option v-for="cat in categoryList" :key="cat.id" :value="cat.id">
                {{ cat.name }} ({{ getCategoryCount(cat.id) }})
              </option>
            </select>
            <div class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-muted-foreground">
              <ChevronDown class="h-3.5 w-3.5" />
            </div>
          </div>

          <!-- Status Dropdown Filter -->
          <div class="relative min-w-[160px] flex-1 sm:flex-initial">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-muted-foreground">
              <CheckCircle2 class="h-3.5 w-3.5" />
            </div>
            <select
              v-model="selectedStatus"
              class="w-full pl-8 pr-8 py-2 bg-accent/30 hover:bg-accent/50 border border-border rounded-lg text-xs font-semibold text-foreground focus:outline-none focus:ring-2 focus:ring-primary/40 appearance-none cursor-pointer transition"
            >
              <option value="all">Semua Status ({{ requirementsData?.items?.length || 0 }})</option>
              <option value="ok">🟢 Hanya Lulus ({{ requirementsData?.overview?.passed || 0 }})</option>
              <option value="warning">🟡 Hanya Peringatan ({{ requirementsData?.overview?.warnings || 0 }})</option>
              <option value="error">🔴 Hanya Kurang ({{ requirementsData?.overview?.errors || 0 }})</option>
            </select>
            <div class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-muted-foreground">
              <ChevronDown class="h-3.5 w-3.5" />
            </div>
          </div>

          <!-- Reset Filter Chip (Shows if filtered) -->
          <button
            v-if="selectedCategory !== 'all' || selectedStatus !== 'all' || searchQuery.trim()"
            type="button"
            @click="resetFilters"
            class="px-2.5 py-1.5 rounded-lg bg-accent text-xs font-medium text-muted-foreground hover:text-foreground hover:bg-accent/80 transition flex items-center gap-1"
          >
            <X class="h-3 w-3" />
            <span>Reset</span>
          </button>
        </div>

        <!-- Right: Search Input Box -->
        <div class="relative w-full md:w-72">
          <Search class="h-3.5 w-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
          <input
            type="text"
            v-model="searchQuery"
            :placeholder="t('system.system.info.requirements.search_placeholder')"
            class="w-full pl-8 pr-8 py-2 bg-accent/20 border border-border rounded-lg text-xs text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/40 transition"
          />
          <button
            v-if="searchQuery"
            type="button"
            @click="searchQuery = ''"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
          >
            <X class="h-3.5 w-3.5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Requirements Matrix Items -->
    <div class="space-y-3">
      <div
        v-for="item in filteredRequirements"
        :key="item.id"
        class="bg-card border border-border/90 rounded-xl p-4 transition hover:border-border shadow-sm"
      >
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <!-- Left: Status Icon & Details -->
          <div class="flex items-start gap-3.5 min-w-0 flex-1">
            <div class="mt-0.5 flex-shrink-0">
              <CheckCircle2
                v-if="item.status === 'ok'"
                class="h-5 w-5 text-green-600 dark:text-green-400"
              />
              <AlertTriangle
                v-else-if="item.status === 'warning'"
                class="h-5 w-5 text-amber-500"
              />
              <XCircle
                v-else
                class="h-5 w-5 text-red-500"
              />
            </div>

            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="font-bold text-sm text-foreground">{{ item.name }}</span>
                <span
                  v-if="item.required"
                  class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-primary/10 text-primary border border-primary/20"
                >
                  Wajib
                </span>
                <span
                  v-else
                  class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-muted text-muted-foreground"
                >
                  Rekomendasi
                </span>
                <span class="text-[11px] text-muted-foreground font-mono bg-accent/50 px-2 py-0.5 rounded">
                  {{ formatCategory(item.category) }}
                </span>
              </div>

              <p class="text-xs text-muted-foreground leading-relaxed">
                {{ item.description }}
              </p>
            </div>
          </div>

          <!-- Right: Values & Remediation Button -->
          <div class="flex sm:flex-col items-end justify-between sm:justify-center gap-2 self-stretch sm:self-auto border-t sm:border-t-0 pt-2 sm:pt-0 border-border/40">
            <div class="text-right">
              <div class="flex items-center gap-1.5 justify-end">
                <span class="text-[11px] text-muted-foreground">Terdeteksi:</span>
                <span
                  class="text-xs font-mono font-bold"
                  :class="item.status === 'ok' ? 'text-green-600 dark:text-green-400' : item.status === 'warning' ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400'"
                >
                  {{ item.current_value }}
                </span>
              </div>
              <div class="text-[10px] text-muted-foreground">
                Min: <span class="font-medium text-foreground">{{ item.required_value }}</span>
              </div>
            </div>

            <button
              v-if="item.status !== 'ok' || activeGuideId === item.id"
              type="button"
              @click="toggleGuide(item.id)"
              class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-accent/50 hover:bg-accent text-foreground rounded-md text-[11px] font-semibold transition border border-border"
            >
              <HelpCircle class="h-3 w-3 text-primary" />
              <span>{{ activeGuideId === item.id ? 'Tutup Panduan' : 'Panduan Perbaikan' }}</span>
              <ChevronDown class="h-3 w-3 transition-transform" :class="activeGuideId === item.id ? 'rotate-180' : ''" />
            </button>
          </div>
        </div>

        <!-- Remediation Guide Accordion Drawer -->
        <div
          v-if="activeGuideId === item.id && item.fix_guide"
          class="mt-4 pt-3 border-t border-border/60 bg-accent/15 -mx-4 -mb-4 p-4 rounded-b-xl space-y-3"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-foreground flex items-center gap-1.5">
              <Terminal class="h-3.5 w-3.5 text-primary" />
              {{ t('system.system.info.requirements.guide.title') }}
            </span>
            <span class="text-[10px] text-muted-foreground">Klik perintah untuk menyalin</span>
          </div>

          <!-- Guide Snippets -->
          <div class="space-y-2 text-xs">
            <!-- Ubuntu / Debian -->
            <div v-if="item.fix_guide.ubuntu" class="bg-background/80 border border-border rounded-lg p-2.5">
              <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-bold text-muted-foreground">
                  {{ t('system.system.info.requirements.guide.ubuntu') }}
                </span>
                <button
                  type="button"
                  @click="copySnippet(item.fix_guide.ubuntu)"
                  class="inline-flex items-center gap-1 text-[10px] text-primary hover:underline"
                >
                  <Copy class="h-3 w-3" /> Salin
                </button>
              </div>
              <code class="block font-mono text-[11px] text-green-600 dark:text-green-400 select-all break-all">
                {{ item.fix_guide.ubuntu }}
              </code>
            </div>

            <!-- RHEL / AlmaLinux / CentOS -->
            <div v-if="item.fix_guide.rhel" class="bg-background/80 border border-border rounded-lg p-2.5">
              <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-bold text-muted-foreground">
                  {{ t('system.system.info.requirements.guide.rhel') }}
                </span>
                <button
                  type="button"
                  @click="copySnippet(item.fix_guide.rhel)"
                  class="inline-flex items-center gap-1 text-[10px] text-primary hover:underline"
                >
                  <Copy class="h-3 w-3" /> Salin
                </button>
              </div>
              <code class="block font-mono text-[11px] text-green-600 dark:text-green-400 select-all break-all">
                {{ item.fix_guide.rhel }}
              </code>
            </div>

            <!-- General / cPanel -->
            <div v-if="item.fix_guide.general" class="bg-background/80 border border-border rounded-lg p-2.5">
              <span class="text-[11px] font-bold text-muted-foreground block mb-1">
                {{ t('system.system.info.requirements.guide.general') }}
              </span>
              <p class="text-xs text-foreground">
                {{ item.fix_guide.general }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredRequirements.length === 0" class="text-center py-12 bg-accent/10 border border-dashed border-border rounded-xl">
        <Search class="h-8 w-8 mx-auto text-muted-foreground mb-2 opacity-50" />
        <p class="text-sm font-semibold text-foreground">Tidak ada kebutuhan sistem yang cocok</p>
        <p class="text-xs text-muted-foreground mt-1">Coba gunakan kata kunci pencarian atau ganti filter kategori/status.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from '@/shared/composables/useToast';
import { logger } from '@/shared/utils/logger';
import api from '@/engine/api/client';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import type { RequirementsData } from './types';
import {
  AlertTriangle,
  CheckCircle,
  CheckCircle2,
  ChevronDown,
  Copy,
  Filter,
  HelpCircle,
  Loader2,
  RotateCcw,
  Search,
  Terminal,
  Wrench,
  X,
  XCircle,
} from 'lucide-vue-next';

const props = defineProps<{
  requirementsData: RequirementsData | null;
  reqLoading: boolean;
}>();

const emit = defineEmits<{
  (e: 'refresh'): void;
}>();

const { t } = useI18n();
const toast = useToast();

const autoFixing = ref(false);
const activeGuideId = ref<string | null>(null);
const selectedCategory = ref<string>('all');
const selectedStatus = ref<string>('all');
const searchQuery = ref<string>('');

const categoryList = computed(() => [
    { id: 'all', name: t('system.system.info.requirements.categories.all') },
    { id: 'php_core', name: t('system.system.info.requirements.categories.php_core') },
    { id: 'php_extensions', name: t('system.system.info.requirements.categories.php_extensions') },
    { id: 'database', name: t('system.system.info.requirements.categories.database') },
    { id: 'caching', name: t('system.system.info.requirements.categories.caching') },
    { id: 'storage_permissions', name: t('system.system.info.requirements.categories.storage_permissions') },
    { id: 'background_services', name: t('system.system.info.requirements.categories.background_services') },
]);

const getCategoryCount = (catId: string): number => {
    if (!props.requirementsData?.items) return 0;
    if (catId === 'all') return props.requirementsData.items.length;
    return props.requirementsData.items.filter(i => i.category === catId).length;
};

const filteredRequirements = computed(() => {
    if (!props.requirementsData?.items) return [];
    let list = props.requirementsData.items;

    if (selectedCategory.value !== 'all') {
        list = list.filter(item => item.category === selectedCategory.value);
    }

    if (selectedStatus.value !== 'all') {
        list = list.filter(item => item.status === selectedStatus.value);
    }

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase().trim();
        list = list.filter(item => 
            item.name.toLowerCase().includes(q) || 
            item.description.toLowerCase().includes(q) ||
            item.current_value.toLowerCase().includes(q)
        );
    }

    return list;
});

const formatCategory = (cat: string): string => {
    switch (cat) {
        case 'php_core': return 'PHP Core';
        case 'php_extensions': return 'PHP Extension';
        case 'database': return 'Database';
        case 'caching': return 'Cache & Redis';
        case 'storage_permissions': return 'Folder Storage';
        case 'background_services': return 'Background Service';
        default: return cat;
    }
};

const toggleGuide = (id: string) => {
    if (activeGuideId.value === id) {
        activeGuideId.value = null;
    } else {
        activeGuideId.value = id;
    }
};

const copySnippet = async (text: string) => {
    try {
        await navigator.clipboard.writeText(text);
        toast.success.action(t('common.messages.copied'));
    } catch {
        toast.error.action('Failed to copy to clipboard');
    }
};

const resetFilters = () => {
    selectedCategory.value = 'all';
    selectedStatus.value = 'all';
    searchQuery.value = '';
};

const handleAutoFix = async (): Promise<void> => {
    autoFixing.value = true;
    try {
        const res = await api.post('/manage/system/requirements/autofix');
        const data = parseSingleResponse<{ fixed?: string[]; failed?: string[]; message?: string }>(res);
        toast.success.action(data?.message || t('system.system.info.requirements.autofix_success'));
        emit('refresh');
    } catch (error: unknown) {
        logger.error('Auto fix failed:', error);
        toast.error.fromResponse(error);
    } finally {
        autoFixing.value = false;
    }
};
</script>

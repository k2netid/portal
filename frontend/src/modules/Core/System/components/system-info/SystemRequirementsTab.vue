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
            <span class="text-[10px] tracking-wider uppercase mt-1 font-bold">
              {{ t('system.system.info.requirements.readiness_label') }}
            </span>
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
                {{ t('system.system.info.requirements.passed_badge', { count: requirementsData?.overview?.passed || 0 }) }}
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
          <span class="text-[10px] text-muted-foreground block font-medium">
            {{ t('system.system.info.requirements.server_specs.distro') }}
          </span>
          <span class="text-xs font-bold text-foreground truncate block" :title="requirementsData.server_spec.distro">
            {{ requirementsData.server_spec.distro }}
          </span>
        </div>
        <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
          <span class="text-[10px] text-muted-foreground block font-medium">
            {{ t('system.system.info.requirements.server_specs.php_runtime') }}
          </span>
          <span class="text-xs font-bold text-foreground font-mono block">
            {{ requirementsData.server_spec.php_version }} ({{ requirementsData.server_spec.php_sapi }})
          </span>
        </div>
        <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
          <span class="text-[10px] text-muted-foreground block font-medium">
            {{ t('system.system.info.requirements.server_specs.database') }}
          </span>
          <span class="text-xs font-bold text-foreground truncate block" :title="requirementsData.server_spec.database_version">
            {{ requirementsData.server_spec.database_engine.toUpperCase() }}
          </span>
        </div>
        <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
          <span class="text-[10px] text-muted-foreground block font-medium">
            {{ t('system.system.info.requirements.server_specs.redis') }}
          </span>
          <span class="text-xs font-bold text-foreground block">
            v{{ requirementsData.server_spec.redis_version }} ({{ requirementsData.server_spec.redis_latency }})
          </span>
        </div>
        <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
          <span class="text-[10px] text-muted-foreground block font-medium">
            {{ t('system.system.info.requirements.server_specs.queue_worker') }}
          </span>
          <span class="text-xs font-bold text-foreground block">
            {{ requirementsData.server_spec.queue_workers_count > 0 ? t('system.system.info.requirements.server_specs.active_workers', { count: requirementsData.server_spec.queue_workers_count }) : t('system.system.info.requirements.server_specs.sync_idle') }}
          </span>
        </div>
        <div class="bg-background/60 border border-border/60 rounded-lg p-2.5">
          <span class="text-[10px] text-muted-foreground block font-medium">
            {{ t('system.system.info.requirements.server_specs.scheduler_cron') }}
          </span>
          <span
            class="text-xs font-bold block"
            :class="requirementsData.server_spec.cron_configured ? 'text-green-600 dark:text-green-400' : 'text-amber-500'"
          >
            {{ requirementsData.server_spec.cron_configured ? t('system.system.info.requirements.server_specs.configured') : t('system.system.info.requirements.server_specs.unconfigured') }}
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
              <option value="all">
                {{ t('system.system.info.requirements.status_filter.all', { count: requirementsData?.items?.length || 0 }) }}
              </option>
              <option value="ok">
                {{ t('system.system.info.requirements.status_filter.ok', { count: requirementsData?.overview?.passed || 0 }) }}
              </option>
              <option value="warning">
                {{ t('system.system.info.requirements.status_filter.warning', { count: requirementsData?.overview?.warnings || 0 }) }}
              </option>
              <option value="error">
                {{ t('system.system.info.requirements.status_filter.error', { count: requirementsData?.overview?.errors || 0 }) }}
              </option>
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
            <span>{{ t('system.system.info.requirements.reset_filter') }}</span>
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
                <span class="font-bold text-sm text-foreground">{{ getItemName(item) }}</span>
                <span
                  v-if="item.required"
                  class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-primary/10 text-primary border border-primary/20"
                >
                  {{ t('system.system.info.requirements.badge_required') }}
                </span>
                <span
                  v-else
                  class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-muted text-muted-foreground"
                >
                  {{ t('system.system.info.requirements.badge_recommended') }}
                </span>
                <span class="text-[11px] text-muted-foreground font-mono bg-accent/50 px-2 py-0.5 rounded">
                  {{ formatCategory(item.category) }}
                </span>
              </div>

              <p class="text-xs text-muted-foreground leading-relaxed">
                {{ getItemDescription(item) }}
              </p>
            </div>
          </div>

          <!-- Right: Values & Remediation Button -->
          <div class="flex sm:flex-col items-end justify-between sm:justify-center gap-2 self-stretch sm:self-auto border-t sm:border-t-0 pt-2 sm:pt-0 border-border/40">
            <div class="text-right">
              <div class="flex items-center gap-1.5 justify-end">
                <span class="text-[11px] text-muted-foreground">
                  {{ t('system.system.info.requirements.detected_label') }}
                </span>
                <span
                  class="text-xs font-mono font-bold"
                  :class="item.status === 'ok' ? 'text-green-600 dark:text-green-400' : item.status === 'warning' ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400'"
                >
                  {{ formatCurrentValue(item.current_value) }}
                </span>
              </div>
              <div class="text-[10px] text-muted-foreground">
                {{ t('system.system.info.requirements.min_label') }}
                <span class="font-medium text-foreground ml-1">
                  {{ formatRequiredValue(item.required_value) }}
                </span>
              </div>
            </div>

            <button
              v-if="item.status !== 'ok' || activeGuideId === item.id"
              type="button"
              @click="toggleGuide(item.id)"
              class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-accent/50 hover:bg-accent text-foreground rounded-md text-[11px] font-semibold transition border border-border"
            >
              <HelpCircle class="h-3 w-3 text-primary" />
              <span>{{ activeGuideId === item.id ? t('system.system.info.requirements.close_guide') : t('system.system.info.requirements.open_guide') }}</span>
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
            <span class="text-[10px] text-muted-foreground">
              {{ t('system.system.info.requirements.click_to_copy') }}
            </span>
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
                  <Copy class="h-3 w-3" /> {{ t('system.system.info.requirements.copy_btn') }}
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
                  <Copy class="h-3 w-3" /> {{ t('system.system.info.requirements.copy_btn') }}
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
        <p class="text-sm font-semibold text-foreground">
          {{ t('system.system.info.requirements.empty_title') }}
        </p>
        <p class="text-xs text-muted-foreground mt-1">
          {{ t('system.system.info.requirements.empty_subtitle') }}
        </p>
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
import type { RequirementsData, RequirementItem } from './types';
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

const { t, locale } = useI18n();
const toast = useToast();

const autoFixing = ref(false);
const activeGuideId = ref<string | null>(null);
const selectedCategory = ref<string>('all');
const selectedStatus = ref<string>('all');
const searchQuery = ref<string>('');

const itemTranslationsEn: Record<string, { name?: string; desc: string }> = {
    php_version: {
        name: 'PHP Version',
        desc: 'Minimum supported PHP runtime version for JA-CMS is 8.2.0 (8.3+ recommended).'
    },
    php_memory_limit: {
        name: 'PHP Memory Limit',
        desc: 'RAM memory allocation limit for PHP script execution, theme rendering, and media processing.'
    },
    php_max_execution_time: {
        name: 'Max Execution Time',
        desc: 'Maximum script execution time to prevent timeouts during backup creation or content import.'
    },
    php_upload_max_filesize: {
        name: 'Upload Max Filesize',
        desc: 'Maximum single file size allowed to be uploaded to the Media Library.'
    },
    php_opcache: {
        name: 'Zend OPcache Accelerator',
        desc: 'Pre-compiles PHP bytecode in memory to boost JA-CMS execution performance by up to 3x.'
    },
    ext_bcmath: { name: 'PHP Ext: BCMath', desc: 'Arbitrary precision mathematics for financial calculations and hashing.' },
    ext_ctype: { name: 'PHP Ext: Ctype', desc: 'Character type checking and user input sanitization.' },
    ext_curl: { name: 'PHP Ext: cURL', desc: 'Outbound HTTP client for third-party APIs, webhooks, and Cloudflare IP updates.' },
    ext_dom: { name: 'PHP Ext: DOM', desc: 'XML document parsing, HTML sanitization, and RSS/Sitemap feed rendering.' },
    ext_fileinfo: { name: 'PHP Ext: FileInfo', desc: 'Secure MIME type detection for uploaded files regardless of extension.' },
    ext_gd: { name: 'PHP Ext: GD / Imagick', desc: 'Image processing, responsive thumbnail generation, and WebP/AVIF conversions.' },
    ext_intl: { name: 'PHP Ext: Intl', desc: 'Internationalization formatting, localized currency, and calendar dates.' },
    ext_json: { name: 'PHP Ext: JSON', desc: 'JSON data serialization for REST APIs and CCK custom fields.' },
    ext_mbstring: { name: 'PHP Ext: Mbstring', desc: 'Multibyte UTF-8 string manipulation for multilingual content.' },
    ext_openssl: { name: 'PHP Ext: OpenSSL', desc: 'Password hashing, Sanctum API tokens, and secure HTTPS connections.' },
    ext_pdo: { name: 'PHP Ext: PDO', desc: 'Database connection abstraction layer for Laravel Eloquent ORM.' },
    ext_tokenizer: { name: 'PHP Ext: Tokenizer', desc: 'PHP source code tokenization for Blade template compilation.' },
    ext_xml: { name: 'PHP Ext: XML', desc: 'XML parser for import/export utilities and sitemap generation.' },
    ext_zip: { name: 'PHP Ext: ZipArchive', desc: 'Zip archive creation and extraction for backups and plugin installs.' },
    ext_redis: { name: 'PHP Ext: PhpRedis (Optional)', desc: 'High-performance native PHP client for Redis cache and queue workers.' },
    ext_exif: { name: 'PHP Ext: EXIF (Optional)', desc: 'Photo metadata extraction and automatic image rotation.' },
    db_connection: { name: 'Database Connection & Encoding', desc: 'Active database connection with UTF-8 character encoding support.' },
    redis_server: { name: 'Redis In-Memory Server', desc: 'In-memory data store for lightning-fast caching and background queue processing.' },
    storage_views: { name: 'storage/framework/views', desc: 'Directory where Laravel compiles Blade templates into PHP.' },
    storage_cache: { name: 'storage/framework/cache', desc: 'Directory for application cache and rate limiter data.' },
    storage_sessions: { name: 'storage/framework/sessions', desc: 'Session storage directory when using the file session driver.' },
    storage_public: { name: 'storage/app/public', desc: 'Main storage directory for user-uploaded media and avatars.' },
    storage_backups: { name: 'storage/app/backups', desc: 'Directory for system snapshot zip archives and backups.' },
    storage_logs: { name: 'storage/logs', desc: 'Directory for system activity and diagnostic error logs.' },
    bootstrap_cache: { name: 'bootstrap/cache', desc: 'Directory for cached routes, configuration, and package manifests.' },
    storage_symlink: { name: 'Public Storage Symlink (public/storage)', desc: 'Symbolic link connecting storage/app/public to public/storage for browser access.' },
    service_cron: { name: 'Cron Scheduler Daemon', desc: 'Server cron job executing scheduled tasks (backups, cleanup, publishing) every minute.' },
    service_queue_worker: { name: 'Background Queue Worker', desc: 'Background daemon processing intensive jobs (bulk emails, image thumbnails, indexing).' }
};

const itemTranslationsSu: Record<string, { name?: string; desc: string }> = {
    php_version: { name: 'Vérsi PHP', desc: 'Vérsi runtime PHP minimum anu dirojong ku JA-CMS nyaéta 8.2.0 (disarankeun 8.3+).' },
    php_memory_limit: { name: 'Wates Mémori PHP', desc: 'Wates alokasi mémori RAM pikeun pamrosésan skrip PHP, rendering téma, sareng ngolah média.' },
    php_max_execution_time: { name: 'Waktu Maksimum Éksékusi', desc: 'Waktu éksékusi maksimum skrip pikeun nyegah timeout nalika cadangan atanapi impor kusi.' },
    php_upload_max_filesize: { name: 'Ukuran Maksimal Unggahan', desc: 'Ukuran maksimal berkas tunggal anu diwidian diunggah ka Pabukon Média.' },
    php_opcache: { name: 'Akselerator Zend OPcache', desc: 'OPcache ngompilasi bytecode PHP dina mémori sangkan performa JA-CMS ningkat dugi ka 3x lipet.' },
    db_connection: { name: 'Sambungan Database & Énkoding', desc: 'Koneksi aktip ka database kalayan rojongan énkoding aksara UTF-8.' },
    redis_server: { name: 'Sérver In-Memory Redis', desc: 'Panyimpenan data in-memory pikeun cache gancang sareng antrean worker.' },
    storage_symlink: { name: 'Symlink Panyimpenan Publik (public/storage)', desc: 'Tautan simbolik ti storage/app/public ka public/storage sangkan berkas média tiasa diaksés ku browser.' },
    service_cron: { name: 'Daemon Tugas Terjadwal (Cron)', desc: 'Cron job sérver anu ngajalankeun tugas otomatis (cadangan, beberesih log, terbitkeun otomatis) unggal menit.' },
    service_queue_worker: { name: 'Worker Antrean Kasang Tukang', desc: 'Worker latar tukang pikeun ngolah tugas beurat (ngirim email massal, komprési gambar, indeks pilarian).' }
};

const getItemName = (item: RequirementItem): string => {
    const loc = String(locale.value || 'id').toLowerCase();
    if (loc.startsWith('en')) {
        const entry = itemTranslationsEn[item.id];
        if (entry?.name) return entry.name;
    }
    if (loc.startsWith('su')) {
        const entry = itemTranslationsSu[item.id];
        if (entry?.name) return entry.name;
    }
    return item.name;
};

const getItemDescription = (item: RequirementItem): string => {
    const loc = String(locale.value || 'id').toLowerCase();
    if (loc.startsWith('en')) {
        const entry = itemTranslationsEn[item.id];
        if (entry?.desc) return entry.desc;
    }
    if (loc.startsWith('su')) {
        const entry = itemTranslationsSu[item.id];
        if (entry?.desc) return entry.desc;
    }
    return item.description;
};

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
            getItemName(item).toLowerCase().includes(q) || 
            getItemDescription(item).toLowerCase().includes(q) ||
            item.current_value.toLowerCase().includes(q)
        );
    }

    return list;
});

const formatCategory = (cat: string): string => {
    switch (cat) {
        case 'php_core': return t('system.system.info.requirements.categories.php_core');
        case 'php_extensions': return t('system.system.info.requirements.categories.php_extensions');
        case 'database': return t('system.system.info.requirements.categories.database');
        case 'caching': return t('system.system.info.requirements.categories.caching');
        case 'storage_permissions': return t('system.system.info.requirements.categories.storage_permissions');
        case 'background_services': return t('system.system.info.requirements.categories.background_services');
        default: return cat;
    }
};

const formatCurrentValue = (val: string): string => {
    if (!val) return '-';
    if (val === 'Terpasang (Installed)' || val === 'Terpasang') return t('system.system.info.requirements.status_text.installed');
    if (val === 'Tidak Ditemukan (Missing)' || val === 'Tidak Ditemukan') return t('system.system.info.requirements.status_text.missing');
    if (val === 'Tersambung (Linked)' || val === 'Tersambung') return t('system.system.info.requirements.status_text.connected');
    if (val === 'Terputus / Belum Dibuat') return t('system.system.info.requirements.status_text.disconnected');
    if (val === 'Terdeteksi di Crontab') return t('system.system.info.requirements.status_text.detected_cron');
    if (val === 'Belum Dikonfigurasi di Crontab') return t('system.system.info.requirements.status_text.unconfigured_cron');
    if (val === 'Dapat Ditulis (Writable)' || val === 'Writable') return t('system.system.info.requirements.status_text.writable');
    if (val === 'Hanya Baca (Read-only)') return t('system.system.info.requirements.status_text.read_only');
    if (val === 'Aktif (Enabled)') return t('system.system.info.requirements.status_text.enabled');
    if (val === 'Nonaktif (Disabled)') return t('system.system.info.requirements.status_text.disabled');
    if (val.includes('Worker Berjalan') || val.includes('Worker Active') || val.includes('Worker')) {
        const count = val.replace(/[^0-9]/g, '');
        return t('system.system.info.requirements.server_specs.active_workers', { count: count || '1' });
    }
    return val;
};

const formatRequiredValue = (val: string): string => {
    if (!val) return '-';
    if (val === 'Terpasang') return t('system.system.info.requirements.status_text.installed');
    if (val === 'Tersambung') return t('system.system.info.requirements.status_text.connected');
    if (val === 'Dapat Ditulis (Writable)' || val === 'Writable') return t('system.system.info.requirements.status_text.writable');
    if (val.includes('schedule:run')) return t('system.system.info.requirements.server_specs.configured');
    return val;
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

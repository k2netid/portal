<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="$t('system.scheduled_tasks.title')"
      :subtitle="$t('system.scheduled_tasks.description')"
    >
      <template #actions>
        <div class="flex flex-wrap gap-2">
        <Button
          variant="outline"
          size="sm"
          class="h-10 inline-flex items-center gap-2"
          @click="showHelp = !showHelp"
        >
          <BadgeInfo class="w-4 h-4 mr-2" />
          {{ $t('common.labels.help') }}
        </Button>
        <Button
          v-if="isSuperAdmin"
          variant="outline"
          @click="openRunCommandDialog"
        >
          <Terminal class="w-4 h-4 mr-2" />
          {{ $t('system.command_runner.run') }}
        </Button>
        <Button @click="openCreateDialog">
          <Plus class="w-4 h-4 mr-2" />
          {{ $t('system.scheduled_tasks.create') }}
        </Button>
        </div>
      </template>
    </PageHeader>

    <!-- Inline Help Guide -->
    <div>
      <div
        v-if="showHelp"
        class="bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4"
      >
        <div class="flex items-start gap-3">
          <Info class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
          <div class="flex-1 space-y-3">
            <div>
              <h3 class="font-semibold text-blue-900 dark:text-blue-100">
                {{ $t('system.scheduled_tasks.help.title') }}
              </h3>
              <p class="text-sm text-blue-800 dark:text-blue-200 mt-1">
                {{ $t('system.scheduled_tasks.help.intro') }}
              </p>
            </div>
            
            <div class="space-y-2">
              <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100">
                {{ $t('system.scheduled_tasks.help.requirements_title') }}
              </h4>
              <ul class="text-sm text-blue-800 dark:text-blue-200 list-disc list-inside space-y-1">
                <li>{{ $t('system.scheduled_tasks.help.req_1') }}</li>
                <li>{{ $t('system.scheduled_tasks.help.req_2') }}</li>
                <li>{{ $t('system.scheduled_tasks.help.req_3') }}</li>
              </ul>
            </div>

            <div class="space-y-2">
              <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100">
                {{ $t('system.scheduled_tasks.help.cron_title') }}
              </h4>
              <p class="text-sm text-blue-800 dark:text-blue-200">
                {{ $t('system.scheduled_tasks.help.cron_desc') }}
              </p>
              <div class="flex items-center gap-2 bg-slate-900 dark:bg-slate-950 rounded-md p-3 font-mono text-sm">
                <code class="text-green-400 flex-1 overflow-x-auto">* * * * * cd {{ appPath }} && php artisan schedule:run >> /dev/null 2>&1</code>
                <Button 
                  size="icon" 
                  variant="ghost" 
                  class="h-8 w-8 text-slate-400 hover:text-white flex-shrink-0"
                  :title="$t('common.actions.copy')"
                  @click="copyCronScript"
                >
                  <Check
                    v-if="cronCopied"
                    class="w-4 h-4 text-green-400"
                  />
                  <Copy
                    v-else
                    class="w-4 h-4"
                  />
                </Button>
              </div>
              <p class="text-xs text-blue-700 dark:text-blue-300">
                {{ $t('system.scheduled_tasks.help.cron_hint') }}
              </p>
            </div>
          </div>
          <Button
            variant="ghost"
            size="icon"
            class="h-6 w-6 text-blue-600 dark:text-blue-400 flex-shrink-0"
            @click="showHelp = false"
          >
            <X class="w-4 h-4" />
          </Button>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-card border border-border rounded-lg p-4 sticky top-0 z-10 shadow-sm backdrop-blur-xl bg-card/80">
      <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
        <!-- Search -->
        <div class="flex items-center gap-3 flex-1 w-full md:w-auto">
          <div class="relative flex-1 md:max-w-xs">
            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input
              v-model="search"
              :placeholder="$t('common.actions.search')"
              class="pl-9 h-9 w-full md:w-[280px]"
              @input="debouncedSearch"
            />
          </div>
        </div>

        <!-- Bulk Actions & View Toggle -->
        <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end">
          <div>
            <div
              v-if="selectedTasks.length > 0"
              class="flex items-center gap-2"
            >
              <span class="text-sm text-muted-foreground hidden sm:inline-block">
                {{ selectedTasks.length }} selected
              </span>
              <Select
                v-model="bulkActionSelection"
                @update:model-value="handleBulkAction"
              >
                <SelectTrigger class="w-[140px] h-9 bg-primary/5 border-primary/20 text-primary hover:bg-primary/10">
                  <SelectValue :placeholder="$t('common.actions.bulkAction')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    value="delete"
                    class="text-destructive focus:text-destructive"
                  >
                    <div class="flex items-center">
                      <Trash2 class="w-4 h-4 mr-2" />
                      {{ $t('common.actions.delete') }}
                    </div>
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="space-y-2"
    >
      <div
        v-for="i in 3"
        :key="i"
        class="h-16 rounded-lg bg-card border border-border"
      />
    </div>

    <!-- No Tasks State -->
    <div
      v-else-if="tasks.length === 0"
      class="text-center py-12 bg-card border border-border rounded-lg"
    >
      <div class="p-4 rounded-full bg-muted w-16 h-16 mx-auto mb-4 flex items-center justify-center">
        <Calendar class="w-8 h-8 text-muted-foreground" />
      </div>
      <h3 class="text-lg font-medium text-foreground mb-1">
        {{ $t('system.scheduled_tasks.no_tasks') }}
      </h3>
      <p class="text-muted-foreground text-sm max-w-sm mx-auto mb-6">
        {{ search ? $t('common.messages.empty.search', { query: search }) : $t('system.scheduled_tasks.description') }}
      </p>
      <Button
        v-if="search"
        variant="outline"
        @click="search = ''; fetchTasks()"
      >
        {{ $t('common.actions.clear') }}
      </Button>
      <Button
        v-else
        @click="openCreateDialog"
      >
        <Plus class="w-4 h-4 mr-2" />
        {{ $t('system.scheduled_tasks.create') }}
      </Button>
    </div>

    <!-- Tasks List Template (TanStack) -->
    <ConsoleListCard v-else>
      <DataTable
        :table="table"
        :loading="loading"
        :empty-message="$t('system.scheduled_tasks.no_tasks')"
      />
        
      <!-- Pagination -->
      <template #footer>
        <Pagination
          v-if="pagination.total > 0"
          embedded
          :total-items="pagination.total"
          :per-page="pagination.per_page"
          :current-page="pagination.current_page"
          @page-change="changePage"
          @update:per-page="handlePerPageChange"
        />
      </template>
    </ConsoleListCard>

    <!-- Create/Edit Dialog -->
    <Dialog v-model:open="dialogOpen">
      <DialogContent class="console-dialog-lg">
        <DialogHeader>
          <DialogTitle>
            {{ editingTask ? $t('system.scheduled_tasks.edit') : $t('system.scheduled_tasks.create') }}
          </DialogTitle>
        </DialogHeader>
        
        <form
          class="space-y-4 py-4"
          @submit.prevent="saveTask"
        >
          <div class="grid gap-2">
            <Label for="scheduledTaskName">{{ $t('system.scheduled_tasks.form.name') }}</Label>
            <Input
              id="scheduledTaskName"
              v-model="form.name"
              required
              :class="{ 'border-destructive focus-visible:ring-destructive': errors.name }"
            />
            <p
              v-if="errors.name"
              class="text-sm text-destructive"
            >
              {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
            </p>
          </div>

          <div class="grid gap-2">
            <Label for="scheduledTaskCommand">{{ $t('system.scheduled_tasks.form.command') }}</Label>
            <Select
              v-model="form.command"
              required
            >
              <SelectTrigger id="scheduledTaskCommand" :aria-label="$t('system.scheduled_tasks.form.command')" :class="{ 'border-destructive focus:ring-destructive': errors.command }">
                <SelectValue :placeholder="$t('system.scheduled_tasks.form.select_command')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem 
                  v-for="cmd in allowedCommands" 
                  :key="cmd.value" 
                  :value="cmd.value"
                >
                  {{ cmd.label }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p
              v-if="errors.command"
              class="text-sm text-destructive mt-1"
            >
              {{ Array.isArray(errors.command) ? errors.command[0] : errors.command }}
            </p>
          </div>

          <div class="grid gap-2">
            <Label for="scheduledTaskCron">{{ $t('system.scheduled_tasks.form.schedule') }}</Label>
            <div class="flex gap-2">
              <Select
                v-model="cronPreset"
                @update:model-value="applyCronPreset"
              >
                <SelectTrigger class="w-48" aria-label="Cron preset">
                  <SelectValue :placeholder="$t('system.scheduled_tasks.form.preset')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="hourly">
                    Hourly
                  </SelectItem>
                  <SelectItem value="daily">
                    Daily at midnight
                  </SelectItem>
                  <SelectItem value="weekly">
                    Weekly on Sunday
                  </SelectItem>
                  <SelectItem value="monthly">
                    Monthly on 1st
                  </SelectItem>
                </SelectContent>
              </Select>
              <Input
                id="scheduledTaskCron"
                v-model="form.schedule" 
                :placeholder="t('common.placeholders.cronExpression')" 
                class="flex-1 font-mono"
                required
                :class="{ 'border-destructive focus-visible:ring-destructive': errors.schedule }"
              />
            </div>
            <p
              v-if="errors.schedule"
              class="text-sm text-destructive mt-1"
            >
              {{ Array.isArray(errors.schedule) ? errors.schedule[0] : errors.schedule }}
            </p>
            <p class="text-xs text-muted-foreground">
              {{ $t('system.scheduled_tasks.form.cron_help') }}
            </p>
          </div>

          <div class="grid gap-2">
            <Label for="scheduledTaskDescription">{{ $t('system.scheduled_tasks.form.description') }}</Label>
            <Textarea
              id="scheduledTaskDescription"
              v-model="form.description"
              rows="3"
            />
          </div>

          <div class="flex items-center gap-2">
            <input
              id="is_active"
              v-model="form.is_active"
              type="checkbox"
              class="h-4 w-4 rounded border-input"
            >
            <Label
              for="is_active"
              class="cursor-pointer"
            >{{ $t('system.scheduled_tasks.form.active') }}</Label>
          </div>

          <DialogFooter>
            <Button
              type="button"
              variant="outline"
              @click="dialogOpen = false"
            >
              {{ $t('common.actions.cancel') }}
            </Button>
            <Button
              type="submit"
              :disabled="saving || !isValid || (editingTask && !isDirty)"
            >
              {{ saving ? $t('common.actions.save') + '...' : $t('common.actions.save') }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Run Command Dialog (New) -->
    <Dialog v-model:open="runCommandDialogOpen">
      <DialogContent class="console-dialog-lg">
        <DialogHeader>
          <DialogTitle>{{ $t('system.command_runner.title') }}</DialogTitle>
        </DialogHeader>
        
        <div class="grid gap-4 py-4">
          <div class="grid gap-2">
            <Label>{{ $t('system.command_runner.select_command') }}</Label>
            <Select v-model="adhocCommand.command">
              <SelectTrigger>
                <SelectValue :placeholder="$t('system.command_runner.select_placeholder')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem 
                  v-for="cmd in allowedCommands" 
                  :key="cmd.value" 
                  :value="cmd.value"
                >
                  {{ cmd.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="grid gap-2">
            <Label>{{ $t('system.command_runner.parameters') }}</Label>
            <Input 
              v-model="adhocCommand.parameters" 
              :placeholder="$t('system.command_runner.parameters_placeholder')" 
            />
            <p
              v-if="commandHint"
              class="text-xs text-muted-foreground italic"
            >
              💡 {{ commandHint }}
            </p>
          </div>

          <div
            v-if="adhocOutput"
            class="mt-4"
          >
            <Label class="mb-2 block">Output</Label>
            <div class="bg-black text-green-400 p-4 rounded-md font-mono text-sm overflow-auto max-h-[300px] border border-border">
              <div
                v-if="adhocExecuting"
                class="flex items-center gap-2"
              >
                <Loader2 class="w-4 h-4" />
                <span>{{ $t('system.command_runner.executing') }}</span>
              </div>
              <pre
                v-else
                class="whitespace-pre-wrap break-words"
              >{{ adhocOutput }}</pre>
            </div>
          </div>
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            @click="runCommandDialogOpen = false"
          >
            {{ $t('common.actions.close') }}
          </Button>
          <Button 
            :disabled="!adhocCommand.command || adhocExecuting" 
            @click="runAdhocCommand"
          >
            <Loader2
              v-if="adhocExecuting"
              class="w-4 h-4 mr-2"
            />
            <Play
              v-else
              class="w-4 h-4 mr-2"
            />
            {{ adhocExecuting ? $t('system.command_runner.running') : $t('system.command_runner.run') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Output Dialog -->
    <Dialog v-model:open="outputDialogOpen">
      <DialogContent class="console-dialog-xl-scroll">
        <DialogHeader>
          <DialogTitle>{{ $t('system.scheduled_tasks.output.title') }}</DialogTitle>
        </DialogHeader>
        <div class="bg-black text-green-400 p-4 rounded-md font-mono text-sm overflow-auto max-h-[500px] border border-border">
          <pre>{{ selectedTaskOutput }}</pre>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed, onMounted, watch, h } from 'vue';
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { debounce } from '@/shared/utils/debounce';

import axios from 'axios';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useAuthStore } from '@/modules/Core/System/stores/auth';

// UI Components
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    Button,
    Input,
    Label,
    Textarea,
    Badge,
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
    Pagination,
    Checkbox,
    DataTable
} from '@/shared/components/ui';


import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
    getSortedRowModel,
    type SortingState,
    type RowSelectionState
} from '@tanstack/vue-table';

import {
  BadgeInfo,
  Calendar,
  Check,
  Copy,
  FileText,
  Info,
  Loader2,
  Pencil,
  Play,
  Plus,
  Search,
  Terminal,
  Trash2,
  X,
} from 'lucide-vue-next';

interface ScheduledTask {
  id: string;
  name: string;
  command: string;
  schedule: string;
  description: string | null;
  is_active: boolean;
  last_run_at: string | null;
  last_run_duration: number | null;
  status: 'completed' | 'running' | 'failed' | 'pending' | null;
  output: string | null;
  created_at: string;
  updated_at: string;
}

interface AllowedCommand {
  label: string;
  value: string;
}

interface PaginationInfo {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface TaskForm {
  name: string;
  command: string;
  schedule: string;
  description: string;
  is_active: boolean;
}

interface AdhocCommand {
  command: string;
  parameters: string;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();

const tasks = ref<ScheduledTask[]>([]);
const loading = ref(true);
const search = ref('');
const dialogOpen = ref(false);
const outputDialogOpen = ref(false);
const runCommandDialogOpen = ref(false);
const selectedTaskOutput = ref('');
const editingTask = ref<ScheduledTask | null>(null);
const saving = ref(false);
const running = ref<string | null>(null);
const allowedCommands = ref<AllowedCommand[]>([]);
const cronPreset = ref('');
const errors = ref<Record<string, string | string[]>>({});

// Help guide state
const showHelp = ref(false);
const cronCopied = ref(false);
const appPath = ref('/var/www/Jejakawan'); // Fallback default

// Ad-hoc Command State
const adhocCommand = ref<AdhocCommand>({ command: '', parameters: '' });
const adhocOutput = ref('');
const adhocExecuting = ref(false);

// Command Hints for better UX - covers all allowed commands
const commandHints: Record<string, string> = {
  // Cache Management
  'cache:clear': 'Hapus semua cache aplikasi. Tidak perlu parameter.',
  'cache:warm': 'Panaskan cache untuk performa. Tidak perlu parameter.',
  'Jejakawan:clear-cache': 'Hapus cache Jejakawan spesifik. Tidak perlu parameter.',
  
  // Maintenance
  'logs:cleanup': 'Bersihkan log files. Parameter: --days=7 (opsional)',
  'analytics:cleanup': 'Hapus data analytics lama. Tidak perlu parameter.',
  'media:thumbnails': 'Regenerasi thumbnail media. Tidak perlu parameter.',
  
  // Health & Diagnostics
  'Jejakawan:health-check': 'Cek kesehatan sistem Jejakawan. Tidak perlu parameter.',
  'config:clear': 'Hapus cache konfigurasi. Tidak perlu parameter.',
  'route:clear': 'Hapus cache routing. Tidak perlu parameter.',
  'view:clear': 'Hapus cache view/blade. Tidak perlu parameter.',
  
  // Backup
  'Jejakawan:backup': 'Buat backup database & files. Tidak perlu parameter.',
  
  // Security
  'security:clear-blocked-ips': 'Hapus semua IP yang diblokir. Tidak perlu parameter.',
  'security:clear-rate-limit': 'Reset rate limiter. Tidak perlu parameter.',
  'security:audit-dependencies': 'Audit keamanan package. Tidak perlu parameter.',
  'security:maintenance': 'Kontrol mode pemeliharaan keamanan. Parameter: start|stop|status --modules=all --duration=60',
  
  // Maintenance & Cleanup
  'logs:cleanup-slow-queries': 'Hapus log slow queries. Tidak perlu parameter.',
  'logs:cleanup-csp-reports': 'Hapus laporan CSP. Tidak perlu parameter.',
  
  // Queue Management
  'queue:restart': 'Restart semua queue worker. Tidak perlu parameter.',
  'queue:flush': 'Kosongkan antrean job. Parameter: default (nama queue)',
  'queue:prune-failed': 'Hapus job gagal lama. Parameter: --hours=24 (opsional)',
  'queue:retry': 'Coba ulang job gagal. Parameter: all atau ID job tertentu',
  'queue:monitor': 'Monitor antrean. Parameter: default (nama queue)',
  
  // System Optimization
  'optimize': 'Optimasi config, routes & views. Tidak perlu parameter.',
  'optimize:clear': 'Hapus semua cache optimasi. Tidak perlu parameter.',
  'activitylog:clean': 'Hapus activity logs lama. Tidak perlu parameter.',
  'sanctum:prune-tokens': 'Hapus token API expired. Tidak perlu parameter.'
};

const commandHint = computed(() => {
  return commandHints[adhocCommand.value.command] || '';
});

// Copy cron script to clipboard
const copyCronScript = async () => {
  const cronScript = `* * * * * cd ${appPath.value} && php artisan schedule:run >> /dev/null 2>&1`;
  try {
    await navigator.clipboard.writeText(cronScript);
    cronCopied.value = true;
    toast.success.action(t('common.messages.copied'));
    setTimeout(() => {
      cronCopied.value = false;
    }, 2000);
  } catch (err) {
    logger.error('Failed to copy cron script:', err);
  }
};

// Selection & Bulk Actions
const selectedTasks = ref<string[]>([]);
const bulkActionSelection = ref('');

const columnHelper = createColumnHelper<ScheduledTask>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            checked: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
            'onUpdate:checked': (val) => table.toggleAllPageRowsSelected(!!val),
            'aria-label': t('common.actions.selectAll'),
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: row.getIsSelected(),
            'onUpdate:checked': (val) => row.toggleSelected(!!val),
            'aria-label': t('common.actions.selectRow') + ': ' + row.original.name,
        }),
        size: 50,
    }),
    columnHelper.accessor('name', {
        header: t('system.scheduled_tasks.table.name'),
        cell: ({ row }) => h('span', { class: 'font-semibold' }, row.original.name)
    }),
    columnHelper.accessor('command', {
        header: t('system.scheduled_tasks.table.command'),
        cell: ({ row }) => h('code', { class: 'text-[11px] bg-muted/50 px-2 py-0.5 rounded border border-border dark:bg-muted/20' }, row.original.command)
    }),
    columnHelper.accessor('schedule', {
        header: t('system.scheduled_tasks.table.schedule'),
        cell: ({ row }) => h('code', { class: 'text-[11px] font-mono font-bold text-primary' }, row.original.schedule)
    }),
    columnHelper.accessor('last_run_at', {
        header: t('system.scheduled_tasks.table.last_run'),
        cell: ({ row }) => {
            const task = row.original;
            if (!task.last_run_at) return h('span', { class: 'text-muted-foreground text-sm italic' }, t('common.labels.never'));
            return h('div', { class: 'flex flex-col' }, [
                h('span', { class: 'text-sm font-medium' }, formatDate(task.last_run_at)),
                h('span', { class: 'text-[10px] text-muted-foreground tabular-nums' }, task.last_run_duration ? (task.last_run_duration / 1000).toFixed(2) + 's' : '')
            ]);
        }
    }),
    columnHelper.accessor('status', {
        header: t('system.scheduled_tasks.table.status'),
        cell: ({ row }) => h(Badge, {
            variant: getStatusVariant(row.original.status),
            class: 'capitalize text-[10px] px-2 py-0'
        }, t('system.scheduled_tasks.status.' + (row.original.status || 'pending')))
    }),
    columnHelper.accessor('is_active', {
        header: t('system.scheduled_tasks.table.active'),
        cell: ({ row }) => {
            const task = row.original;
            return h(Button, {
                size: 'sm',
                variant: task.is_active ? 'default' : 'secondary',
                onClick: () => toggleActive(task),
                class: 'h-6 text-[10px] font-bold px-2',
                title: task.is_active ? t('system.scheduled_tasks.tooltips.click_to_deactivate') : t('system.scheduled_tasks.tooltips.click_to_activate')
            }, task.is_active ? t('common.labels.active') : t('common.labels.inactive'));
        }
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('system.scheduled_tasks.table.actions')),
        cell: ({ row }) => {
            const task = row.original;
            return h('div', { class: 'flex justify-end gap-1 opacity-100 group-hover:opacity-100 ' }, [
                h(Button, {
                    size: 'icon', variant: 'ghost', onClick: () => runTask(task),
                    disabled: running.value === task.id,
                    class: 'h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/20',
                    title: t('common.actions.run')
                }, [running.value === task.id ? h(Loader2, { class: 'w-4 h-4 ' }) : h(Play, { class: 'w-4 h-4' })]),
                task.output && h(Button, {
                    size: 'icon', variant: 'ghost', onClick: () => viewOutput(task),
                    class: 'h-8 w-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20',
                    title: t('system.scheduled_tasks.output.title')
                }, [h(FileText, { class: 'w-4 h-4' })]),
                h(Button, {
                    size: 'icon', variant: 'ghost', onClick: () => editTask(task),
                    class: 'h-8 w-8 text-orange-600 hover:text-orange-700 hover:bg-orange-50 dark:hover:bg-orange-900/20',
                    title: t('common.actions.edit')
                }, [h(Pencil, { class: 'w-4 h-4' })]),
                h(Button, {
                    size: 'icon', variant: 'ghost', onClick: () => confirmDelete(task),
                    class: 'h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10',
                    title: t('common.actions.delete')
                }, [h(Trash2, { class: 'w-4 h-4' })])
            ]);
        }
    })
];

const sorting = ref<SortingState>([]);
const rowSelection = ref<RowSelectionState>({});

const table = useVueTable({
    get data() { return tasks.value },
    columns,
    state: {
        get sorting() { return sorting.value },
        get rowSelection() { return rowSelection.value },
    },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    onRowSelectionChange: updaterOrValue => {
        rowSelection.value = typeof updaterOrValue === 'function' ? updaterOrValue(rowSelection.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
    enableRowSelection: true,
});

// Sync selectedTasks with rowSelection
watch(rowSelection, (newSelection: RowSelectionState) => {
    selectedTasks.value = Object.keys(newSelection)
        .filter(key => newSelection[key]);
}, { deep: true });

// Clear selection when tasks change
watch(tasks, () => {
    rowSelection.value = {};
});

const pagination = ref<PaginationInfo>({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
});

const form = ref<TaskForm>({
  name: '',
  command: '',
  schedule: '',
  description: '',
  is_active: true
});

const initialForm = ref<string | null>(null);

const cronPresets: Record<string, string> = {
  hourly: '0 * * * *',
  daily: '0 0 * * *',
  weekly: '0 0 * * 0',
  monthly: '0 0 1 * *'
};

const isValid = computed(() => {
    return !!form.value.name?.trim() && !!form.value.command && !!form.value.schedule?.trim();
});

const isDirty = computed(() => {
    if (!initialForm.value) return true;
    return JSON.stringify(form.value) !== initialForm.value;
});


const isSuperAdmin = computed(() => {
  return authStore.getRoleRank() >= 100;
});

onMounted(async () => {
  await Promise.all([
    fetchTasks(),
    fetchAllowedCommands()
  ]);

  // Auto-open Run Command modal if requested via URL Quick Action
  if (route.query.action === 'run_command' && isSuperAdmin.value) {
      openRunCommandDialog();
      
      // Clean up the URL query parameter so it doesn't pop open again on refresh
      const query = { ...route.query };
      delete query.action;
      router.replace({ query });
  }
});

async function fetchTasks(page = 1) : Promise<void> {
  try {
    loading.value = true;
    const params = {
        page,
        search: search.value,
        per_page: pagination.value.per_page
    }
    const response = await api.get('/manage/system/scheduled-tasks', { params });
    
    // Handle both paginated and non-paginated responses for compatibility
    if (response.data?.meta) {
        tasks.value = response.data;
        pagination.value = {
            current_page: response.data.meta.current_page,
            last_page: response.data.meta.last_page,
            per_page: response.data.meta.per_page,
            total: response.data.meta.total
        };
    } else if (response.data?.data) {
        const payload = response.data;
        // BaseApiController paginated response structure keys
        if (payload.current_page !== undefined) {
             tasks.value = Array.isArray(payload.data) ? payload.data : [];
             pagination.value = {
                current_page: payload.current_page,
                last_page: payload.last_page,
                per_page: payload.per_page,
                total: payload.total
             };
        } else {
             // Plain array fallback
             tasks.value = payload;
             pagination.value.total = tasks.value.length;
        }
    } else {
        tasks.value = [];
    }
    
    selectedTasks.value = [];
  } catch (error: unknown) {
    logger.error('Failed to fetch tasks:', error);
    toast.error.fromResponse(error);
  } finally {
    loading.value = false;
  }
}

async function fetchAllowedCommands() : Promise<void> {
  try {
    const response = await api.get('/manage/system/scheduled-tasks/allowed-commands');
    if (response.data.commands) {
        allowedCommands.value = response.data.commands;
        appPath.value = response.data.base_path;
    } else {
        allowedCommands.value = response.data;
    }
  } catch (error: unknown) {
    logger.error('Failed to fetch allowed commands:', error);
  }
}

const debouncedSearch = debounce(() => {
    fetchTasks(1);
}, 300);

const changePage = (page: number) => {
    fetchTasks(page);
};

const handlePerPageChange = (perPage: number) => {
    pagination.value.per_page = perPage;
    // fetchTasks will be called by the page-change event emitted by Pagination component immediately after
};

// Selection Logic (Removed - now handled by TanStack Table)

// Bulk Actions
const handleBulkAction = async (action: string) => {
    if (!action) return;

    if (action === 'delete') {
        if (!selectedTasks.value.length) return;

        const confirmed = await confirm({
            title: t('common.messages.confirm.title'),
            message: t('common.messages.confirm.bulkDelete'),
            variant: 'danger',
            confirmText: t('common.actions.delete'),
        });

        if (!confirmed) {
            bulkActionSelection.value = '';
            return;
        }

        try {
            const deletePromises = selectedTasks.value.map(id => api.delete(`/manage/system/scheduled-tasks/${id}`));
            await Promise.all(deletePromises);
            
            toast.success.action(t('system.scheduled_tasks.messages.bulkDeleted', { count: selectedTasks.value.length }));
            selectedTasks.value = [];
            bulkActionSelection.value = '';
            fetchTasks(pagination.value.current_page);
        } catch (error: unknown) {
            logger.error('Bulk action failed:', error);
            toast.error.fromResponse(error);
        }
    }
};


function openCreateDialog() {
  editingTask.value = null;
  form.value = {
    name: '',
    command: '',
    schedule: '',
    description: '',
    is_active: true
  };
  cronPreset.value = '';
  errors.value = {};
  initialForm.value = JSON.stringify(form.value);
  dialogOpen.value = true;
}

function editTask(task: ScheduledTask) {
  editingTask.value = task;
  errors.value = {};
  form.value = {
    name: task.name,
    command: task.command,
    schedule: task.schedule,
    description: task.description || '',
    is_active: task.is_active
  };
  initialForm.value = JSON.stringify(form.value);
  dialogOpen.value = true;
}

function applyCronPreset(preset: string) {
  form.value.schedule = cronPresets[preset] || form.value.schedule;
}

async function saveTask() {
  try {
    saving.value = true;
    errors.value = {};
    
    if (editingTask.value) {
      await api.put(`/manage/system/scheduled-tasks/${editingTask.value.id}`, form.value);
      toast.success.update('Scheduled Task');
    } else {
      await api.post('/manage/system/scheduled-tasks', form.value);
      toast.success.create('Scheduled Task');
    }
    
    dialogOpen.value = false;
    await fetchTasks(pagination.value.current_page);
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      if (error.response?.status === 422) {
        errors.value = (error.response.data as { errors?: Record<string, string | string[]> })?.errors || {};
      } else {
        toast.error.fromResponse(error);
      }
    } else {
      toast.error.fromResponse(error);
    }
  } finally {
    saving.value = false;
  }
}

async function runTask(task: ScheduledTask) {
  const confirmed = await confirm({
    title: t('system.scheduled_tasks.actions.run'),
    message: t('system.scheduled_tasks.confirm_run', { name: task.name }),
    variant: 'info',
    confirmText: t('common.actions.run'),
  });

  if (!confirmed) return;

  try {
    running.value = task.id;
    const response = await api.post(`/manage/system/scheduled-tasks/${task.id}/run`);
    
    toast.success.action(t('system.scheduled_tasks.messages.executed'));

    selectedTaskOutput.value = response.data.output;
    outputDialogOpen.value = true;
    
    await fetchTasks(pagination.value.current_page);
  } catch (error: unknown) {
    logger.error('Failed to run task:', error);
    toast.error.fromResponse(error);
  } finally {
    running.value = null;
  }
}

async function toggleActive(task: ScheduledTask) : Promise<void> {
  try {
    const newState = !task.is_active;
    await api.put(`/manage/system/scheduled-tasks/${task.id}`, {
      is_active: newState
    });
    
    // Show success feedback
    if (newState) {
      toast.success.action(t('system.scheduled_tasks.messages.activated'));
    } else {
      toast.success.action(t('system.scheduled_tasks.messages.deactivated'));
    }
    
    await fetchTasks(pagination.value.current_page);
  } catch (error: unknown) {
    logger.error('Failed to toggle task:', error);
    toast.error.fromResponse(error);
  }
}

function viewOutput(task: ScheduledTask) {
  selectedTaskOutput.value = task.output || t('system.scheduled_tasks.output.noOutput');
  outputDialogOpen.value = true;
}

async function confirmDelete(task: ScheduledTask) {
  const confirmed = await confirm({
    title: t('system.scheduled_tasks.actions.delete'),
    message: t('system.scheduled_tasks.confirm_delete', { name: task.name }),
    variant: 'danger',
    confirmText: t('common.actions.delete'),
  });

  if (!confirmed) return;

  try {
    await api.delete(`/manage/system/scheduled-tasks/${task.id}`);
    toast.success.delete();
    await fetchTasks(pagination.value.current_page);
  } catch (error: unknown) {
    logger.error('Failed to delete task:', error);
    toast.error.fromResponse(error);
  }
}

function getStatusVariant(status: string | null) {
  const variants: Record<string, string> = {
    completed: 'success',
    running: 'default',
    failed: 'destructive',
    pending: 'secondary'
  };
  return (variants[status || ''] || 'secondary') as "default" | "destructive" | "secondary" | "success" | "outline" | null | undefined;
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleString();
}

function openRunCommandDialog() {
  adhocCommand.value = { command: '', parameters: '' };
  adhocOutput.value = '';
  runCommandDialogOpen.value = true;
}

async function runAdhocCommand() {
  if (!adhocCommand.value.command) return;

  try {
    adhocExecuting.value = true;
    adhocOutput.value = '';

    // Run the command via dedicated ad-hoc endpoint
    const response = await api.post('/manage/system/scheduled-tasks/run-adhoc', {
      command: adhocCommand.value.command,
      parameters: adhocCommand.value.parameters
    });

    adhocOutput.value = response.data.output || t('system.scheduled_tasks.output.noOutput');
    
    if(response.data.exit_code !== 0) {
         // handle error visual if needed, but text is likely enough
    }

  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
        adhocOutput.value = (error.response?.data as { message?: string })?.message || error.message;
    } else {
        adhocOutput.value = (error as Error).message;
    }
    logger.error('Failed to execute command:', error);
  } finally {
    adhocExecuting.value = false;
  }
}
</script>

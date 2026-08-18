<template>
  <div class="space-y-6">
    <PageHeader
      v-if="!isEmbedded"
      borderless
      :title="t('publishing.content_templates.title')"
      :subtitle="t('publishing.content_templates.description')"
    >
      <template #actions>
        <router-link
          v-if="authStore.hasPermission('manage content templates')"
          :to="{ name: 'content-templates.create' }"
          class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-primary px-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90"
        >
          <Plus data-icon="inline-start" class="size-4 shrink-0" />
          {{ t('publishing.content_templates.create') }}
        </router-link>
      </template>
    </PageHeader>
    <ConsoleListCard>
      <template #toolbar>
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between w-full">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <!-- Left: Search / Filters -->
          <div class="flex items-center gap-3 w-full md:w-auto flex-wrap">
            <div class="relative w-full md:w-72">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
              <Input
                v-model="search"
                :placeholder="t('publishing.content_templates.search')"
                :aria-label="t('publishing.content_templates.search')"
                class="pl-9"
                @input="handleSearch"
              />
            </div>
            <Select
              v-model="typeFilter"
              @update:model-value="fetchTemplates"
            >
              <SelectTrigger class="w-[140px]" :aria-label="t('publishing.content_templates.types.all')">
                <SelectValue :placeholder="t('publishing.content_templates.types.all')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ t('publishing.content_templates.types.all') }}
                </SelectItem>
                <SelectItem value="post">
                  {{ t('publishing.content_templates.types.post') }}
                </SelectItem>
                <SelectItem value="page">
                  {{ t('publishing.content_templates.types.page') }}
                </SelectItem>
                <SelectItem value="custom">
                  {{ t('publishing.content_templates.types.custom') }}
                </SelectItem>
              </SelectContent>
            </Select>
            <Select
              v-model="trashedFilter"
              @update:model-value="fetchTemplates"
            >
              <SelectTrigger class="w-[140px]" :aria-label="t('common.labels.status')">
                <SelectValue :placeholder="t('common.labels.status')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="without">
                  {{ t('common.labels.activeOnly') }}
                </SelectItem>
                <SelectItem value="with">
                  {{ t('common.labels.includesTrashed') }}
                </SelectItem>
                <SelectItem value="only">
                  {{ t('common.labels.trashedOnly') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Right: Actions -->
          <div class="flex items-center gap-2">
            <div
              v-if="selectedTemplates.length > 0"
              class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10 animate-in fade-in slide-in-from-top-1 mr-2"
            >
              <span class="text-xs font-semibold text-primary uppercase tracking-wider">
                {{ t('publishing.content_templates.table.selected', { count: selectedTemplates.length }) }}
              </span>
              <div class="h-4 w-px bg-primary/20" />
              <Select
                v-model="bulkAction"
                @update:model-value="handleBulkAction"
              >
                <SelectTrigger class="w-[130px] h-7 border-primary/20 text-xs shadow-none" :aria-label="t('common.labels.bulkAction')">
                  <SelectValue :placeholder="t('common.labels.bulkAction')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    value="delete"
                    class="text-destructive focus:text-destructive"
                  >
                    {{ t('common.actions.delete') }}
                  </SelectItem>
                  <SelectItem
                    value="restore"
                    class="text-emerald-600 focus:text-emerald-600"
                  >
                    {{ t('common.actions.restore') }}
                  </SelectItem>
                  <SelectItem
                    value="force_delete"
                    class="text-destructive focus:text-destructive"
                  >
                    {{ t('common.actions.forceDelete') }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Create Button -->
            <router-link
              v-if="isEmbedded && authStore.hasPermission('manage content templates')"
              :to="{ name: 'content-templates.create' }"
              class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-primary px-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90"
            >
              <Plus data-icon="inline-start" class="size-4 shrink-0" />
              {{ t('publishing.content_templates.create') }}
            </router-link>
          </div>
        </div>
      </div>
      </template>
        <div
          v-if="loading && templates.length === 0"
          class="p-12 text-center"
        >
          <Loader2 class="w-8 h-8 animate-spin mx-auto text-muted-foreground mb-4" />
          <p class="text-muted-foreground font-medium">
            {{ t('publishing.content_templates.loading') }}
          </p>
        </div>

        <div
          v-else-if="templates.length === 0"
          class="p-12 text-center"
        >
          <FileText class="w-12 h-12 mx-auto text-muted-foreground/20 mb-4" />
          <p class="text-muted-foreground font-medium">
            {{ t('publishing.content_templates.empty') }}
          </p>
        </div>

        <div
          v-else
          class="relative overflow-x-auto"
        >
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-12 px-6">
                  <Checkbox
                    :checked="allSelected"
                    :aria-label="t('actions.selectAll')"
                    @update:checked="toggleSelectAll"
                  />
                </TableHead>
                <TableHead class="text-xs font-medium text-muted-foreground">
                  {{ t('publishing.content_templates.table.name') }}
                </TableHead>
                <TableHead class="text-xs font-medium text-muted-foreground">
                  {{ t('publishing.content_templates.table.type') }}
                </TableHead>
                <TableHead class="text-xs font-medium text-muted-foreground">
                  {{ t('publishing.content_templates.table.description') }}
                </TableHead>
                <TableHead class="text-xs font-medium text-muted-foreground">
                  {{ t('publishing.content_templates.table.updated') }}
                </TableHead>
                <TableHead class="text-center text-xs font-medium text-muted-foreground">
                  {{ t('publishing.content_templates.table.actions') }}
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="template in templates"
                :key="template.id"
                class="group"
              >
                <TableCell class="px-6">
                  <Checkbox
                    :checked="selectedTemplates.includes(template.id)"
                    :aria-label="t('actions.select') + ': ' + template.name"
                    @update:checked="(checked) => toggleSelection(template.id, checked)"
                  />
                </TableCell>
                <TableCell>
                  <div class="flex items-center gap-2 text-sm font-medium text-foreground">
                    {{ template.name }}
                    <Badge
                      v-if="template.deleted_at"
                      variant="destructive"
                      class="h-4.5 text-[10px] px-1.5 uppercase font-bold tracking-wider"
                    >
                      {{ t('common.labels.deleted') }}
                    </Badge>
                  </div>
                </TableCell>
                <TableCell>
                  <Badge
                    variant="secondary"
                    class="capitalize"
                  >
                    {{ template.type ? t(`publishing.content_templates.types.${template.type}`) : t('publishing.content_templates.types.post') }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <div
                    class="text-sm truncate max-w-xs"
                    :title="template.description"
                  >
                    {{ template.description || '-' }}
                  </div>
                </TableCell>
                <TableCell class="text-sm">
                  {{ formatDate(template.updated_at) }}
                </TableCell>
                <TableCell class="text-center">
                  <div class="flex justify-center gap-1">
                    <Button
                      variant="ghost"
                      size="icon"
              :aria-label="t('publishing.content_templates.actions.createContent')"
                      :title="t('publishing.content_templates.actions.createContent')"
                      class="h-8 w-8 text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 hover:bg-emerald-500/10"
                      @click="createFromTemplate(template)"
                    >
                      <CopyPlus class="w-4 h-4" />
                    </Button>
                    <router-link
                      :to="{ name: 'content-templates.edit', params: { id: template.id } }"
                      :aria-label="t('common.actions.edit')"
                      class="inline-flex h-8 w-8 items-center justify-center rounded-md text-indigo-600 hover:bg-indigo-500/10 dark:text-indigo-400"
                    >
                      <Pencil class="w-4 h-4" />
                    </router-link>
                    <Button
                      v-if="template.deleted_at"
                      variant="ghost"
                      size="icon"
              :aria-label="t('common.actions.restore')"
                      :title="t('common.actions.restore')"
                      class="h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-500/10"
                      @click="handleRestore(template)"
                    >
                      <RotateCcw class="w-4 h-4" />
                    </Button>
                    <Button
                      variant="ghost"
                      size="icon"
              :aria-label="template.deleted_at ? t('common.actions.forceDelete') : t('publishing.content_templates.actions.delete')"
                      :title="template.deleted_at ? t('common.actions.forceDelete') : t('publishing.content_templates.actions.delete')"
                      class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                      @click="handleDelete(template)"
                    >
                      <Trash2 class="w-4 h-4" />
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

      <template #footer>
        <Pagination
          embedded
          v-if="pagination && pagination.total > 0"
          :current-page="pagination.current_page"
          :total-items="pagination.total"
          :per-page="Number(perPage)"
          @page-change="(p: number) => fetchTemplates(p)"
          @update:per-page="(val) => { perPage = String(val); fetchTemplates(1); }"
        />
      </template>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useToast } from '@/shared/composables/useToast';
import { parseResponse, ensureArray, parseSingleResponse, type PaginationData } from '@/shared/utils/responseParser';
import { debounce } from '@/shared/utils/debounce';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { Badge, Button, Checkbox, Input, Pagination, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/shared/components/ui';

import {
  CopyPlus,
  FileText,
  Loader2,
  Pencil,
  Plus,
  RotateCcw,
  Search,
  Trash2,
} from 'lucide-vue-next';

interface Template {
    id: string;
    name: string;
    type: string;
    description?: string;
    updated_at: string;
    deleted_at?: string | null;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
const router = useRouter();
const authStore = useAuthStore();

defineProps<{
    isEmbedded: boolean;
}>();
const templates = ref<Template[]>([]);
const loading = ref(false);
const search = ref('');
const typeFilter = ref('all');
const trashedFilter = ref('without');
const pagination = ref<PaginationData | null>(null); 
const perPage = ref('10');
const selectedTemplates = ref<string[]>([]);
const bulkAction = ref('');

const allSelected = computed(() => {
    return templates.value.length > 0 && selectedTemplates.value.length === templates.value.length;
});

const handleSearch = debounce(() => {
    fetchTemplates(1);
}, 300);

const fetchTemplates = async (page: number | string = 1) => {
    loading.value = true;
    try {
        const params = {
            page,
            per_page: perPage.value,
            // If 'all', send specific classic types to filter on server
            type: typeFilter.value !== 'all' ? typeFilter.value : 'post,page,custom',
            search: search.value,
            trashed: trashedFilter.value !== 'without' ? trashedFilter.value : undefined
        };

        const response = await api.get('/manage/publishing/content-templates', { params });
        const { data, pagination: pag } = parseResponse(response);
        
        templates.value = ensureArray(data);
        pagination.value = pag;
        selectedTemplates.value = []; // Reset selection on page change
    } catch (error: unknown) {
        logger.error('Failed to fetch templates:', error);
        templates.value = [];
    } finally {
        loading.value = false;
    }
};

const createFromTemplate = async (template: Template) => {
    try {
        const response = await api.post(`/manage/publishing/content-templates/${template.id}/create-content`);
        const content = parseSingleResponse<{ id: string }>(response);
        if (content && content.id) {
            toast.success.createFromTemplate();
            router.push({ name: 'contents.edit', params: { id: content.id } });
        }
    } catch (error: unknown) {
        logger.error('Failed to create content from template:', error);
        toast.error.templateCreateContent(error as Record<string, unknown>);
    }
};

const handleDelete = async (template: Template) => {
    const isTrashed = !!template.deleted_at;
    const confirmed = await confirm({
        title: isTrashed ? t('common.actions.forceDelete') : t('publishing.content_templates.actions.delete'),
        message: isTrashed 
            ? t('publishing.content_templates.messages.forceDeleteConfirm', { name: template.name })
            : t('publishing.content_templates.messages.deleteConfirm', { name: template.name }),
        variant: 'danger',
        confirmText: isTrashed ? t('common.actions.forceDelete') : t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        if (isTrashed) {
            await api.delete(`/manage/publishing/content-templates/${template.id}/force-delete`);
            toast.success.action(t('common.messages.success.deleted', { item: t('publishing.content_templates.title_singular') }));
        } else {
            await api.delete(`/manage/publishing/content-templates/${template.id}`);
            toast.success.delete(t('publishing.content_templates.title_singular'));
        }
        await fetchTemplates(pagination.value?.current_page || 1);
    } catch (error: unknown) {
        logger.error('Failed to delete template:', error);
        toast.error.delete(error, t('publishing.content_templates.title_singular'));
    }
};

const handleRestore = async (template: Template) => {
    const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: t('publishing.content_templates.messages.restoreConfirm', { name: template.name }),
        variant: 'info',
        confirmText: t('common.actions.restore'),
    });

    if (!confirmed) return;

    try {
        await api.post(`/manage/publishing/content-templates/${template.id}/restore`);
        toast.success.restore(t('publishing.content_templates.title_singular'));
        await fetchTemplates(pagination.value?.current_page || 1);
    } catch (error: unknown) {
        logger.error('Failed to restore template:', error);
        toast.error.fromResponse(error);
    }
};

const toggleSelectAll = (checked: boolean) => {
    if (checked) {
        selectedTemplates.value = templates.value.map(t => t.id);
    } else {
        selectedTemplates.value = [];
    }
};

const toggleSelection = (id: string, checked: boolean) => {
    if (checked) {
        selectedTemplates.value.push(id);
    } else {
        selectedTemplates.value = selectedTemplates.value.filter(tId => String(tId) !== String(id));
    }
};

const handleBulkAction = async () => {
    if (!bulkAction.value || selectedTemplates.value.length === 0) return;

    const action = bulkAction.value;
    const count = selectedTemplates.value.length;

    if (action === 'delete' || action === 'force_delete') {
        const isForce = action === 'force_delete';
        const confirmed = await confirm({
            title: isForce ? t('common.actions.forceDelete') : t('publishing.content_templates.actions.bulkDelete'),
            message: isForce 
                ? t('common.messages.confirm.bulkAction', { action: t('common.actions.forceDelete'), count: count })
                : t('common.messages.confirm.bulkAction', { action: t('common.actions.delete'), count: count }),
            variant: 'danger',
            confirmText: isForce ? t('common.actions.forceDelete') : t('common.actions.delete'),
        });

        if (!confirmed) {
            bulkAction.value = '';
            return;
        }

        try {
            await api.post('/manage/publishing/content-templates/bulk-action', {
                action: action,
                ids: selectedTemplates.value
            });
            await fetchTemplates(pagination.value?.current_page || 1);
            bulkAction.value = '';
            toast.success.delete(t('publishing.content_templates.title', { count: count }));
        } catch (error: unknown) {
            logger.error('Bulk action failed:', error);
            toast.error.action(error as Record<string, unknown>);
        }
    } else if (action === 'restore') {
        try {
            await api.post('/manage/publishing/content-templates/bulk-action', {
                action: 'restore',
                ids: selectedTemplates.value
            });
            await fetchTemplates(pagination.value?.current_page || 1);
            bulkAction.value = '';
            toast.success.restore(t('publishing.content_templates.title', { count: count }));
        } catch (error: unknown) {
            logger.error('Bulk action failed:', error);
            toast.error.action(error as Record<string, unknown>);
        }
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

onMounted(() => {
    fetchTemplates();
});
</script>


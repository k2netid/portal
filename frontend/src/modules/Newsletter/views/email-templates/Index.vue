<template>
  <div class="space-y-6">
    <PageHeader
borderless
      :title="t('newsletter.email_templates.list.title')"
    :subtitle="t('newsletter.email_templates.list.subtitle')"
    >
      <template #actions>
        <div class="flex items-center gap-2">
<router-link
                :to="{ name: 'email-templates.create' }"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/80 transition-colors"
              >
                <Plus class="w-5 h-5 mr-2" />
                {{ $t('newsletter.email_templates.list.create') }}
              </router-link>
</div>
      </template>
    </PageHeader>

    <ConsoleListCard>
      <template #toolbar>
        <div class="relative w-full sm:max-w-xs shrink-0">
          <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            v-model="search"
            type="text"
            class="h-10 w-full pl-9 bg-background"
            :placeholder="$t('newsletter.email_templates.list.search')"
            :aria-label="$t('newsletter.email_templates.list.search')"
          />
        </div>
      </template>
      <DataTable
        :table="table"
        :loading="loading"
        :empty-message="t('newsletter.email_templates.list.empty')"
        variant="embedded"
      />
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { PageHeader } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed, h } from 'vue';
import { RouterLink } from 'vue-router';
import api from '@/engine/api/client';
import toast from '@/shared/services/toastService';
import { useI18n } from 'vue-i18n';
import { useConfirm } from '@/shared/composables/useConfirm';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import { useVueTable, getCoreRowModel, getSortedRowModel, createColumnHelper, type SortingState } from '@tanstack/vue-table';
import { Input, Button, Badge, DataTable } from '@/shared/components/ui';

import {
  Eye,
  Pencil,
  Plus,
  Search,
  Send,
  Trash2,
} from 'lucide-vue-next';

const { t } = useI18n();
const { confirm } = useConfirm();

interface Template {
    id: string | string;
    name: string;
    subject?: string | null;
    type?: string | null;
    updated_at: string;
}

const templates = ref<Template[]>([]);
const loading = ref(false);
const search = ref('');
const sorting = ref<SortingState>([]);

const filteredTemplates = computed(() => {
    if (!search.value) return templates.value;
    
    const searchLower = search.value.toLowerCase();
    return templates.value.filter((template: Template) => 
        template.name.toLowerCase().includes(searchLower) ||
        (template.subject && template.subject.toLowerCase().includes(searchLower))
    );
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

const columnHelper = createColumnHelper<Template>();

const columns = [
    columnHelper.accessor('name', {
        header: t('newsletter.email_templates.table.name'),
        cell: ({ row }) => h('div', { class: 'text-sm font-medium text-foreground' }, row.original.name)
    }),
    columnHelper.accessor('subject', {
        header: t('newsletter.email_templates.table.subject'),
        cell: ({ row }) => h('div', { class: 'text-sm text-foreground' }, row.original.subject || '-')
    }),
    columnHelper.accessor('type', {
        header: t('newsletter.email_templates.table.type'),
        cell: ({ row }) => h(Badge, { 
            variant: 'secondary',
            class: 'text-xs'
        }, () => row.original.type || 'custom')
    }),
    columnHelper.accessor('updated_at', {
        header: t('newsletter.email_templates.table.updated'),
        cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, formatDate(row.original.updated_at))
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('newsletter.email_templates.table.actions')),
        cell: ({ row }) => h('div', { class: 'flex justify-end gap-1' }, [
            h(Button, {
                variant: 'ghost',
                size: 'icon',
                onClick: () => previewTemplate(row.original),
                title: t('common.actions.preview'),
                'aria-label': t('common.actions.preview'),
                class: 'h-8 w-8 text-blue-600 hover:text-blue-700 hover:bg-blue-100'
            }, () => h(Eye, { class: 'w-4 h-4' })),
            h(Button, {
                variant: 'ghost',
                size: 'icon',
                onClick: () => sendTestEmail(row.original),
                title: t('newsletter.email_templates.actions.test'),
                class: 'h-8 w-8 text-green-600 hover:text-green-700 hover:bg-green-100'
            }, () => h(Send, { class: 'w-4 h-4' })),
            h(RouterLink, {
                to: { name: 'email-templates.edit', params: { id: row.original.id } },
                class: 'inline-flex items-center justify-center h-8 w-8 rounded-md text-indigo-600 hover:text-indigo-700 hover:bg-indigo-100 transition-colors',
                'aria-label': t('common.actions.edit'),
            }, () => h(Pencil, { class: 'w-4 h-4' })),
            h(Button, {
                variant: 'ghost',
                size: 'icon',
                onClick: () => handleDelete(row.original),
                title: t('common.actions.delete'),
                class: 'h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10'
            }, () => h(Trash2, { class: 'w-4 h-4' }))
        ])
    })
];

const table = useVueTable({
    get data() { return filteredTemplates.value },
    columns,
    state: {
        get sorting() { return sorting.value }
    },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
});

const fetchTemplates = async () => {
    loading.value = true;
    try {
        const response = await api.get('/manage/system/email-templates');
        const { data } = parseResponse(response);
        templates.value = ensureArray(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch templates:', error);
        templates.value = [];
    } finally {
        loading.value = false;
    }
};

const previewTemplate = async (template: Template) => {
    try {
        const response = await api.post(`/manage/system/email-templates/${template.id}/preview`);
        const previewWindow = window.open('', '_blank');
        if (previewWindow) {
            previewWindow.document.write(
                (response.data as { body?: string }).body ?? String(response.data ?? ''),
            );
        }
    } catch (error: unknown) {
        logger.error('Failed to preview template:', error);
        toast.error(t('newsletter.email_templates.messages.preview_failed'));
    }
};

const sendTestEmail = async (template: Template) => {
    try {
        await api.post(`/manage/system/email-templates/${template.id}/send-test`);
        toast.success(t('newsletter.email_templates.messages.send_test_success'));
    } catch (error: unknown) {
        logger.error('Failed to send test email:', error);
        const errorMessage = (error as { response?: { data?: { message?: string } } }).response?.data?.message || t('newsletter.email_templates.messages.send_test_failed');
        toast.error(t('common.messages.toast.error'), errorMessage);
    }
};

const handleDelete = async (template: Template) => {
    const confirmed = await confirm({
        title: t('common.actions.delete'),
        message: t('newsletter.email_templates.confirm_delete', { name: template.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/manage/system/email-templates/${template.id}`);
        toast.success(t('newsletter.email_templates.messages.delete_success'));
        fetchTemplates();
    } catch (error: unknown) {
        logger.error('Failed to delete template:', error);
        toast.error(t('newsletter.email_templates.messages.delete_failed'));
    }
};

onMounted(() => {
    fetchTemplates();
});
</script>

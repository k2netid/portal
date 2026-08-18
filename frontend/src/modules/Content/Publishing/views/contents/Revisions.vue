<template>
  <div class="console-page pb-10 min-w-0 max-w-full">
    <PageHeader
      borderless
      :title="$t('publishing.content.list.revisions')"
      :subtitle="revisionsSubtitle"
    >
      <template #actions>
        <Button variant="ghost" size="sm" as-child class="w-fit inline-flex">
          <router-link :to="{ name: 'contents.edit', params: { id: contentId } }">
            <ArrowLeft data-icon="inline-start" class="size-4 shrink-0" />
            {{ $t('publishing.content.form.back') }}
          </router-link>
        </Button>
      </template>
    </PageHeader>

    <div
      v-if="loading"
      class="flex flex-col items-center justify-center py-20 bg-card/30 border border-border/40 rounded-xl space-y-4"
    >
      <Loader2 class="w-10 h-10 animate-spin opacity-20" />
      <p class="text-sm font-medium animate-pulse text-muted-foreground">
        {{ $t('publishing.content.revisions.loading') }}
      </p>
    </div>

    <div
      v-else-if="revisions.length === 0"
      class="flex flex-col items-center justify-center py-20 bg-card/30 border border-border/40 rounded-xl space-y-4 text-center"
    >
      <div class="w-16 h-16 rounded-full bg-muted/30 flex items-center justify-center">
        <History class="w-8 h-8 text-muted-foreground/50" />
      </div>
      <div class="space-y-1">
        <p class="text-lg font-semibold text-foreground">
          {{ $t('publishing.content.revisions.empty.title') }}
        </p>
        <p class="text-sm text-muted-foreground">
          {{ $t('publishing.content.revisions.empty.description') }}
        </p>
      </div>
    </div>

    <Card
      v-else
      class="border-none shadow-sm overflow-hidden bg-card/50"
    >
      <Table>
        <TableHeader>
          <TableRow class="bg-muted/30 hover:bg-muted/30 border-b border-border/40">
            <TableHead class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">
              {{ $t('publishing.content.revisions.table.version') }}
            </TableHead>
            <TableHead class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">
              {{ $t('publishing.content.revisions.table.author') }}
            </TableHead>
            <TableHead class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">
              {{ $t('publishing.content.revisions.table.date') }}
            </TableHead>
            <TableHead class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">
              {{ $t('publishing.content.revisions.table.changes') }}
            </TableHead>
            <TableHead class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-muted-foreground">
              {{ $t('publishing.content.revisions.table.actions') }}
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow
            v-for="revision in revisions"
            :key="revision.id"
            class="group hover:bg-muted/30 transition-colors border-b border-border/40"
          >
            <TableCell class="px-6 py-4">
              <div class="flex items-center gap-2">
                <Badge
                  v-if="revision.is_current"
                  variant="outline"
                  class="bg-success/10 text-success border-none px-2 py-0.5"
                >
                  {{ $t('publishing.content.revisions.badge.current') }}
                </Badge>
                <span class="text-sm font-mono font-bold text-foreground">v{{ revision.version }}</span>
              </div>
            </TableCell>
            <TableCell class="px-6 py-4">
              <div class="flex items-center gap-2 text-sm">
                <span class="text-foreground/80 font-medium">{{ revision.author?.name || 'System' }}</span>
              </div>
            </TableCell>
            <TableCell class="px-6 py-4">
              <div class="flex flex-col gap-0.5">
                <span class="text-sm text-foreground/80">{{ formatDate(revision.created_at) }}</span>
                <span class="text-[11px] text-muted-foreground font-mono uppercase">{{ formatTime(revision.created_at) }}</span>
              </div>
            </TableCell>
            <TableCell class="px-6 py-4">
              <p class="text-sm text-muted-foreground line-clamp-1 italic">
                {{ revision.changes_summary || $t('publishing.content.revisions.messages.noChanges') }}
              </p>
            </TableCell>
            <TableCell class="px-6 py-4 text-right">
              <div class="flex justify-end items-center gap-2">
                <Button
                  variant="ghost"
                  size="sm"
                  class="inline-flex items-center gap-1.5 text-primary hover:bg-primary/10"
                  @click="viewRevision(revision)"
                >
                  <Eye data-icon="inline-start" class="size-4 shrink-0" />
                  {{ $t('publishing.content.revisions.actions.view') }}
                </Button>
                <Button
                  v-if="!revision.is_current"
                  variant="outline"
                  size="sm"
                  class="inline-flex items-center gap-1.5 text-emerald-800 border-emerald-600/30 hover:bg-emerald-500/10"
                  @click="restoreRevision(revision)"
                >
                  <RotateCcw data-icon="inline-start" class="size-4 shrink-0" />
                  {{ $t('publishing.content.revisions.actions.restore') }}
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </Card>

    <Dialog
      :open="!!viewingRevision"
      @update:open="(open) => { if (!open) viewingRevision = null }"
    >
      <DialogContent class="console-dialog-xl-scroll p-0 gap-0">
        <DialogHeader class="px-6 py-4 border-b border-border/40 space-y-1 text-left">
          <DialogTitle class="text-xl font-bold flex items-center gap-2">
            <History class="size-5 text-primary shrink-0" />
            {{ $t('publishing.content.revisions.modal.title', { version: viewingRevision?.version }) }}
          </DialogTitle>
          <DialogDescription class="text-xs">
            <template v-if="viewingRevision">
              {{ formatDate(viewingRevision.created_at) }} at {{ formatTime(viewingRevision.created_at) }}
            </template>
          </DialogDescription>
        </DialogHeader>
        <div
          v-if="viewingRevision"
          class="p-8 space-y-8"
        >
          <div class="space-y-2">
            <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground">{{ $t('publishing.content.revisions.modal.fields.title') }}</Label>
            <div class="text-2xl font-bold text-foreground">
              {{ viewingRevision.data?.title || '-' }}
            </div>
          </div>
          <div class="space-y-2">
            <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground">{{ $t('publishing.content.revisions.modal.fields.body') }}</Label>
            <SafeHtml
              class="p-6 rounded-xl bg-muted/30 border border-border/40 text-sm prose dark:prose-invert max-w-none"
              :html="viewingRevision.data?.content || '-'"
              mode="cms"
            />
          </div>
          <div class="grid grid-cols-2 gap-8">
            <div class="space-y-2">
              <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground">{{ $t('publishing.content.revisions.modal.fields.status') }}</Label>
              <Badge variant="outline" class="capitalize">
                {{ viewingRevision.data?.status || '-' }}
              </Badge>
            </div>
            <div class="space-y-2">
              <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground">{{ $t('publishing.content.revisions.modal.fields.type') }}</Label>
              <div class="text-sm font-medium capitalize">
                {{ viewingRevision.data?.type || '-' }}
              </div>
            </div>
          </div>
        </div>
        <DialogFooter class="px-4 py-3 border-t border-border/40 gap-2 sm:gap-2">
          <Button
            variant="outline"
            size="sm"
            @click="viewingRevision = null"
          >
            {{ $t('publishing.content.revisions.modal.close') }}
          </Button>
          <Button
            v-if="viewingRevision && !viewingRevision.is_current"
            size="sm"
            class="bg-emerald-700 text-white hover:bg-emerald-800"
            @click="restoreRevision(viewingRevision)"
          >
            <RotateCcw data-icon="inline-start" class="size-4 shrink-0" />
            {{ $t('publishing.content.revisions.modal.restore') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed, onMounted } from 'vue';
import SafeHtml from '@/modules/Core/System/components/ui/SafeHtml.vue';
import { useRoute, useRouter } from 'vue-router';
import { PageHeader } from '@/shared/components/shell';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import {
    Card,
    Button,
    Badge,
    Label,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/shared/components/ui';
import {
  ArrowLeft,
  Eye,
  History,
  Loader2,
  RotateCcw,
} from 'lucide-vue-next';
import { useConfirm } from '@/shared/composables/useConfirm';
import toast from '@/shared/services/toastService';

interface Revision {
    id: string;
    version: number;
    author_id?: string;
    author?: {
        id: string;
        name: string;
    } | null;
    data: {
        title?: string;
        content?: string;
        status?: string;
        type?: string;
        [key: string]: unknown;
    };
    changes_summary?: string;
    is_current: boolean;
    created_at: string;
    updated_at: string;
}

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const contentId = route.params.id as string;
const revisions = ref<Revision[]>([]);
const loading = ref(false);
const contentTitle = ref('');

const revisionsSubtitle = computed(() => {
  const base = t('publishing.content.list.revisionsSubtitle');
  return contentTitle.value ? `${base} — ${contentTitle.value}` : base;
});
const viewingRevision = ref<Revision | null>(null);
const { confirm } = useConfirm();

const fetchRevisions = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/manage/publishing/contents/${contentId}/revisions`);
        revisions.value = response.data;
        
        // Get content title from first revision or fetch content
        if (revisions.value.length > 0) {
            const firstRevision = revisions.value[0];
            if (firstRevision && firstRevision.data?.title) {
                contentTitle.value = firstRevision.data.title;
            } else {
                try {
                    const contentResponse = await api.get(`/manage/publishing/contents/${contentId}`);
                    contentTitle.value = contentResponse.data.data?.title || contentResponse.data.title || t('publishing.content.title_singular');
                } catch {
                    contentTitle.value = t('publishing.content.title_singular');
                }
            }
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch revisions:', error);
    } finally {
        loading.value = false;
    }
};

const viewRevision = async (revision: Revision) => {
    try {
        const response = await api.get(`/manage/publishing/contents/${contentId}/revisions/${revision.id}`);
        viewingRevision.value = response.data;
    } catch (error: unknown) {
        logger.error('Failed to fetch revision detail:', error);
        viewingRevision.value = revision;
    }
};

const restoreRevision = async (revision: Revision) => {
    const confirmed = await confirm({
        title: t('publishing.content.revisions.messages.restoreTitle'),
        message: t('publishing.content.revisions.messages.restoreConfirm', { version: revision.version }),
        confirmText: t('publishing.content.revisions.actions.restore'),
        variant: 'warning',
    });

    if (!confirmed) {
        return;
    }

    try {
        await api.post(`/manage/publishing/contents/${contentId}/revisions/${revision.id}/restore`);
        toast.success(t('common.messages.success.restored', { item: `v${revision.version}` }));
        router.push({ name: 'contents.edit', params: { id: contentId } });
    } catch (error: unknown) {
        logger.error('Failed to restore revision:', error);
        toast.error(t('common.messages.toast.error'), (error as { response?: { data?: { message?: string } } })?.response?.data?.message || t('publishing.content.messages.restoreFailed'));
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

const formatTime = (date: string) => {
    return new Date(date).toLocaleTimeString();
};

onMounted(() => {
    fetchRevisions();
});
</script>


<template>
  <div class="space-y-6">
    <PageHeader
      :title="t('publishing.comments.list.title')"
      :subtitle="t('publishing.comments.list.subtitle')"
      borderless
    />

    <div
      v-if="statistics"
      class="grid grid-cols-2 gap-4 md:grid-cols-5"
    >
      <ConsoleStatCard
        :label="t('publishing.comments.stats.total')"
        :value="statistics.total"
        :icon="MessageSquare"
        tone="primary"
        clickable
        :active="statusFilter === 'all'"
        @click="statusFilter = 'all'"
      />
      <ConsoleStatCard
        :label="t('publishing.comments.stats.pending')"
        :value="statistics.pending"
        :icon="Clock"
        tone="warning"
        clickable
        :active="statusFilter === 'pending'"
        @click="statusFilter = 'pending'"
      />
      <ConsoleStatCard
        :label="t('publishing.comments.stats.approved')"
        :value="statistics.approved"
        :icon="CheckCircle2"
        tone="success"
        clickable
        :active="statusFilter === 'approved'"
        @click="statusFilter = 'approved'"
      />
      <ConsoleStatCard
        :label="t('publishing.comments.stats.rejected')"
        :value="statistics.rejected"
        :icon="X"
        tone="destructive"
        clickable
        :active="statusFilter === 'rejected'"
        @click="statusFilter = 'rejected'"
      />
      <ConsoleStatCard
        :label="t('publishing.comments.stats.spam')"
        :value="statistics.spam"
        :icon="AlertTriangle"
        tone="muted"
        clickable
        :active="statusFilter === 'spam'"
        @click="statusFilter = 'spam'"
      />
    </div>

    <ConsoleListCard>
      <template #toolbar>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-1 sm:min-w-0">
          <div class="relative w-full sm:max-w-xs shrink-0">
            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input
              v-model="search"
              :placeholder="t('publishing.comments.filter.searchPlaceholder')"
              :aria-label="t('publishing.comments.filter.searchPlaceholder')"
              class="h-10 w-full pl-9 bg-background"
            />
          </div>
          <Select v-model="statusFilter">
            <SelectTrigger class="h-10 w-full sm:w-[200px] shrink-0 bg-background" :aria-label="t('publishing.comments.filter.allStatus')">
              <SelectValue :placeholder="t('publishing.comments.filter.allStatus')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">
                {{ t('publishing.comments.filter.allStatus') }}
              </SelectItem>
              <SelectItem value="pending">
                {{ t('publishing.comments.status.pending') }}
              </SelectItem>
              <SelectItem value="approved">
                {{ t('publishing.comments.status.approved') }}
              </SelectItem>
              <SelectItem value="rejected">
                {{ t('publishing.comments.status.rejected') }}
              </SelectItem>
              <SelectItem value="spam">
                {{ t('publishing.comments.status.spam') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div
          v-if="selectedIds.length > 0"
          class="flex flex-wrap items-center justify-end gap-2 shrink-0"
        >
          <span class="text-sm font-medium text-foreground whitespace-nowrap">
            {{ t('publishing.comments.list.selected', { count: selectedIds.length }) }}
          </span>
          <div class="h-4 w-px bg-border" />
          <Select
            v-model="bulkActionSelection"
            @update:model-value="handleBulkAction"
          >
            <SelectTrigger class="h-10 w-[180px] bg-background" :aria-label="t('publishing.content.list.bulkActions')">
              <SelectValue :placeholder="t('publishing.content.list.bulkActions')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="approve">
                {{ t('publishing.comments.actions.approveAll') }}
              </SelectItem>
              <SelectItem value="reject">
                {{ t('publishing.comments.actions.rejectAll') }}
              </SelectItem>
              <SelectItem value="spam">
                {{ t('publishing.comments.actions.markSpam') }}
              </SelectItem>
              <SelectItem
                value="delete"
                class="text-destructive focus:text-destructive"
              >
                {{ t('common.actions.delete') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </template>

      <div class="flex items-center justify-between border-b border-border/40 px-4 py-3 sm:px-6">
        <div class="flex items-center gap-2">
          <Checkbox
            id="select-all"
            :checked="isAllSelected"
            @update:checked="toggleSelectAll"
          />
          <label
            for="select-all"
            class="text-sm font-medium leading-none"
          >
            {{ t('common.actions.selectAll') }}
          </label>
        </div>
      </div>

      <div
        v-if="loading"
        class="p-12 text-center"
      >
        <p class="text-muted-foreground">
          {{ t('common.messages.loading.default') }}
        </p>
      </div>

      <EmptyState
        v-else-if="comments.length === 0"
        :title="t('publishing.comments.list.empty')"
        :icon="MessageSquare"
        class="py-12"
      />

      <div
        v-else
        class="divide-y divide-border/40"
      >
        <Card
          v-for="comment in comments"
          :key="comment.id"
          class="rounded-none border-0 border-b border-border/40 bg-transparent shadow-none last:border-b-0"
        >
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-start space-x-3 flex-1">
                <div class="flex-shrink-0 pt-1">
                  <Checkbox
                    :id="`comment-select-${comment.id}`"
                    :checked="selectedIds.includes(comment.id)"
                    @update:checked="toggleSelection(comment.id)"
                  />
                  <label
                    :for="`comment-select-${comment.id}`"
                    class="sr-only"
                  >{{ t('common.actions.selectRow') }}</label>
                </div>
                <div class="flex-shrink-0">
                  <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center border border-primary/20">
                    <span class="text-primary font-semibold text-sm">
                      {{ ((comment.user?.name || comment.name || 'U')?.charAt(0) || 'U').toUpperCase() }}
                    </span>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center space-x-2">
                    <p class="text-sm font-semibold text-foreground">
                      {{ comment.user?.name || comment.name || t('publishing.comments.detail.anonymous') }}
                    </p>
                    <Badge
                      variant="outline"
                      :class="statusBadgeClass(comment.status)"
                    >
                      {{ t('publishing.comments.status.' + comment.status) }}
                    </Badge>
                  </div>
                  <div class="flex items-center gap-x-3 mt-1 text-xs text-muted-foreground">
                    <span>{{ comment.user?.email || comment.email || t('publishing.comments.detail.no_email') }}</span>
                    <span class="flex items-center">
                      <Clock class="w-3 h-3 mr-1" />
                      {{ formatDate(comment.created_at) }}
                    </span>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-1">
                <Button
                  v-if="comment.status === 'pending' || comment.status === 'rejected'"
                  variant="ghost"
                  size="sm"
                  class="h-8 text-green-800 dark:text-green-300 hover:bg-green-500/15 inline-flex items-center gap-1.5"
                  @click="approveComment(comment)"
                >
                  <Check data-icon="inline-start" class="size-4 shrink-0" />
                  {{ t('publishing.comments.actions.approve') }}
                </Button>
                <Button
                  v-if="comment.status === 'pending' || comment.status === 'approved'"
                  variant="ghost"
                  size="sm"
                  class="h-8 text-amber-900 dark:text-amber-200 hover:bg-amber-500/15 inline-flex items-center gap-1.5"
                  @click="rejectComment(comment)"
                >
                  <X data-icon="inline-start" class="size-4 shrink-0" />
                  {{ t('publishing.comments.actions.reject') }}
                </Button>
                <Button
                  v-if="comment.status !== 'spam'"
                  variant="ghost"
                  size="sm"
                  class="h-8 text-muted-foreground inline-flex items-center gap-1.5"
                  @click="markAsSpam(comment)"
                >
                  <AlertTriangle data-icon="inline-start" class="size-4 shrink-0" />
                  {{ t('publishing.comments.actions.markSpam') }}
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-8 text-red-900 dark:text-red-200 hover:bg-red-500/15 inline-flex items-center gap-1.5"
                  @click="deleteComment(comment)"
                >
                  <Trash2 data-icon="inline-start" class="size-4 shrink-0" />
                  {{ t('common.actions.delete') }}
                </Button>
              </div>
            </div>

            <div class="mb-4 pl-[52px]">
              <p class="text-sm text-foreground leading-relaxed">
                {{ comment.body }}
              </p>
            </div>

            <div class="flex items-center justify-between text-[10px] text-muted-foreground pt-4 border-t border-border/40 pl-[52px]">
              <div class="flex items-center space-x-4">
                <span
                  v-if="comment.content"
                  class="flex items-center"
                >
                  <ArrowUpRight class="w-3 h-3 mr-1" />
                  {{ t('publishing.comments.list.on') }}:
                  <router-link
                    :to="{ name: 'contents.edit', params: { id: comment.content.id } }"
                    class="text-primary hover:underline ml-1 font-medium"
                  >
                    {{ comment.content.title }}
                  </router-link>
                </span>
                <span
                  v-if="comment.parent"
                  class="flex items-center"
                >
                  <Reply class="w-3 h-3 mr-1" />
                  {{ t('publishing.comments.list.replyTo') }}: <b>{{ comment.parent.user?.name || comment.parent.name || t('publishing.comments.detail.anonymous') }}</b>
                </span>
              </div>
              <div class="font-medium">
                <span v-if="(comment.replies_count || 0) > 0">
                  {{ comment.replies_count }} {{ comment.replies_count === 1 ? t('publishing.comments.detail.reply') : t('publishing.comments.detail.replies') }}
                </span>
              </div>
            </div>
          </div>

          <div
            v-if="comment.replies && comment.replies.length > 0"
            class="bg-muted/30 border-t border-border/40 p-6 pl-16 space-y-4"
          >
            <div
              v-for="reply in comment.replies"
              :key="reply.id"
              class="relative group"
            >
              <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                  <div class="h-8 w-8 rounded-full bg-background border border-border flex items-center justify-center">
                    <span class="text-muted-foreground font-semibold text-[10px]">
                      {{ ((reply.user?.name || reply.name || 'U')?.charAt(0) || 'U').toUpperCase() }}
                    </span>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center space-x-2 mb-1">
                    <p class="text-xs font-semibold text-foreground">
                      {{ reply.user?.name || reply.name || t('publishing.comments.detail.anonymous') }}
                    </p>
                    <Badge
                      variant="outline"
                      class="text-[10px] h-4 px-1"
                      :class="statusBadgeClass(reply.status)"
                    >
                      {{ t('publishing.comments.status.' + reply.status) }}
                    </Badge>
                  </div>
                  <p class="text-xs text-foreground/80 leading-relaxed">
                    {{ reply.body }}
                  </p>
                  <p class="text-[10px] text-muted-foreground mt-1">
                    {{ formatDate(reply.created_at) }}
                  </p>
                </div>
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 hover:opacity-100 transition-opacity">
                  <Button
                    v-if="reply.status === 'pending'"
                    variant="ghost"
                    size="icon"
                    :aria-label="t('publishing.comments.actions.approve')"
                    class="w-6 h-6 text-green-600 dark:text-green-400 hover:bg-green-500/10"
                    @click="approveComment(reply)"
                  >
                    <Check class="w-3 h-3" />
                  </Button>
                  <Button
                    v-if="reply.status === 'pending'"
                    variant="ghost"
                    size="icon"
                    :aria-label="t('publishing.comments.actions.reject')"
                    class="w-6 h-6 text-yellow-600 dark:text-yellow-400 hover:bg-yellow-500/10"
                    @click="rejectComment(reply)"
                  >
                    <X class="w-3 h-3" />
                  </Button>
                  <Button
                    variant="ghost"
                    size="icon"
                    :aria-label="t('common.actions.delete')"
                    class="w-6 h-6 text-destructive"
                    @click="deleteComment(reply)"
                  >
                    <Trash2 class="w-3 h-3" />
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </Card>
      </div>

      <template
        v-if="pagination && pagination.total > 0"
        #footer
      >
        <Pagination
          :current-page="pagination.current_page"
          :total-items="pagination.total"
          :per-page="Number(pagination.per_page || 10)"
          :show-page-numbers="true"
          embedded
          @page-change="changePage"
          @update:per-page="(val) => { if (pagination) { pagination.per_page = Number(val); pagination.current_page = 1; fetchComments(); } }"
        />
      </template>
    </ConsoleListCard>
  </div>
</template>


<script setup lang="ts">
import { EmptyState } from '@/shared/components/feedback';

import { PageHeader, ConsoleStatCard, ConsoleListCard } from '@/shared/components/shell';
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useToast } from '@/shared/composables/useToast';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import { Badge, Button, Card, Checkbox, Input, Pagination, Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui';
import type { PaginationData } from '@/shared/utils/responseParser';

import {
  AlertTriangle,
  ArrowUpRight,
  Check,
  CheckCircle2,
  Clock,
  MessageSquare,
  Reply,
  Search,
  Trash2,
  X,
} from 'lucide-vue-next';

import type { Comment, CommentStatus, CommentStatistics } from '@/modules/Content/Publishing/types/comments';

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const loading = ref(false);
const comments = ref<Comment[]>([]);
const search = ref('');
const statusFilter = ref<CommentStatus | 'all'>('pending');
const pagination = ref<PaginationData | null>(null);
const statistics = ref<CommentStatistics | null>(null);
const selectedIds = ref<string[]>([]);

const fetchStatistics = async () => {
    try {
        const response = await api.get('/manage/publishing/comments/statistics');
        statistics.value = (response.data) as CommentStatistics;
    } catch (error: unknown) {
        logger.error('Failed to fetch statistics:', error);
    }
};

const bulkActionSelection = ref('');

const handleBulkAction = async (value: string) => {
    if (!value) return;
    await bulkAction(value);
    bulkActionSelection.value = '';
};

const bulkAction = async (action: string) => {
    if (selectedIds.value.length === 0) return;
    
    const count = selectedIds.value.length;
    let confirmMsg = '';
    
    switch (action) {
        case 'delete':
            confirmMsg = t('common.messages.confirm.bulkDelete', { count });
            break;
        case 'approve':
            confirmMsg = t('publishing.comments.messages.bulkApproveConfirm', { count });
            break;
        case 'reject':
            confirmMsg = t('publishing.comments.messages.bulkRejectConfirm', { count });
            break;
        case 'spam':
            confirmMsg = t('publishing.comments.messages.bulkSpamConfirm', { count });
            break;
    }
    
    const confirmed = await confirm({
        title: t('publishing.comments.actions.bulkAction'),
        message: confirmMsg,
        variant: action === 'delete' ? 'danger' : 'warning',
        confirmText: t('common.actions.confirm'),
    });

    if (!confirmed) {
        bulkActionSelection.value = '';
        return;
    }
    
    try {
        await api.post('/manage/publishing/comments/bulk', {
            ids: selectedIds.value,
            action: action
        });
        selectedIds.value = [];
        await fetchComments();
        await fetchStatistics();
        toast.success.action(t('common.messages.success.action'));
    } catch (error: unknown) {
        logger.error('Bulk action failed:', error);
        toast.error.action(error as Record<string, unknown>);
    }
};

const fetchComments = async () => {
    loading.value = true;
    try {
        const params: Record<string, string | number> = {
            page: pagination.value?.current_page || 1,
            per_page: Number(pagination.value?.per_page || 10),
        };

        if (statusFilter.value && statusFilter.value !== 'all') {
            params.status = statusFilter.value;
        }

        const response = await api.get('/manage/publishing/comments', { params });
        const { data, pagination: paginationData } = parseResponse<Comment>(response);
        comments.value = ensureArray<Comment>(data);
        if (paginationData) {
            pagination.value = paginationData;
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch comments:', error);
    } finally {
        loading.value = false;
    }
};

const changePage = (page: number) => {
    if (pagination.value) {
        pagination.value.current_page = page;
        fetchComments();
    }
};

const approveComment = async (comment: Comment) => {
    try {
        await api.put(`/manage/publishing/comments/${comment.id}/approve`);
        await fetchComments();
        toast.success.approve(t('publishing.comments.title_singular'));
    } catch (error: unknown) {
        logger.error('Failed to approve comment:', error);
        toast.error.update(error as Record<string, unknown>, t('publishing.comments.title_singular'));
    }
};

const rejectComment = async (comment: Comment) => {
    try {
        await api.put(`/manage/publishing/comments/${comment.id}/reject`);
        await fetchComments();
        await fetchStatistics();
        toast.success.reject(t('publishing.comments.title_singular'));
    } catch (error: unknown) {
        logger.error('Failed to reject comment:', error);
        toast.error.update(error as Record<string, unknown>, t('publishing.comments.title_singular'));
    }
};

const markAsSpam = async (comment: Comment) => {
    try {
        await api.put(`/manage/publishing/comments/${comment.id}/spam`);
        await fetchComments();
        await fetchStatistics();
        toast.success.markSpam(t('publishing.comments.title_singular'));
    } catch (error: unknown) {
        logger.error('Failed to mark as spam:', error);
        toast.error.update(error as Record<string, unknown>, t('publishing.comments.title_singular'));
    }
};

const toggleSelection = (commentId: string) => {
    const index = selectedIds.value.indexOf(commentId);
    if (index > -1) {
        selectedIds.value.splice(index, 1);
    } else {
        selectedIds.value.push(commentId);
    }
};

const deleteComment = async (comment: Comment) => {
    const confirmed = await confirm({
        title: t('publishing.comments.actions.delete'),
        message: t('publishing.comments.messages.deleteConfirm'),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/manage/publishing/comments/${comment.id}`);
        await fetchComments();
        toast.success.delete(t('publishing.comments.title_singular'));
    } catch (error: unknown) {
        logger.error('Failed to delete comment:', error);
        toast.error.delete(error as Record<string, unknown>, t('publishing.comments.title_singular'));
    }
};

const statusBadgeClass = (status: string) => {
    switch (status) {
        case 'pending':
            return 'bg-amber-600/15 text-amber-950 dark:text-amber-100 border-amber-600/30';
        case 'approved':
            return 'bg-emerald-600/15 text-emerald-950 dark:text-emerald-100 border-emerald-600/30';
        case 'rejected':
            return 'bg-red-600/15 text-red-950 dark:text-red-100 border-red-600/30';
        case 'spam':
            return 'bg-slate-600/15 text-slate-900 dark:text-slate-100 border-slate-500/30';
        default:
            return '';
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

watch([statusFilter, search], () => {
    if (pagination.value) {
        pagination.value.current_page = 1;
    }
    fetchComments();
});

const isAllSelected = computed(() => {
    return comments.value.length > 0 && selectedIds.value.length === comments.value.length;
});

const toggleSelectAll = (checked: boolean) => {
    if (checked) {
        selectedIds.value = comments.value.map(c => c.id);
    } else {
        selectedIds.value = [];
    }
};

onMounted(() => {
    fetchComments();
    fetchStatistics();
});
</script>


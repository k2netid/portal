<template>
  <div class="space-y-6">
    <PageHeader :title="t('system.kycReviews.title')" :subtitle="t('system.kycReviews.subtitle')">
      <template #actions>
        <Select v-model="statusFilter" @update:model-value="fetchList">
          <SelectTrigger class="w-[180px]"><SelectValue /></SelectTrigger>
          <SelectContent>
            <SelectItem value="pending_review">{{ t('system.kycReviews.filters.pending') }}</SelectItem>
            <SelectItem value="approved">{{ t('system.kycReviews.filters.approved') }}</SelectItem>
            <SelectItem value="rejected">{{ t('system.kycReviews.filters.rejected') }}</SelectItem>
            <SelectItem value="all">{{ t('system.kycReviews.filters.all') }}</SelectItem>
          </SelectContent>
        </Select>
        <Button variant="outline" :disabled="loading" @click="fetchList">
          <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
          {{ t('common.actions.refresh') }}
        </Button>
      </template>
    </PageHeader>

    <PageSkeleton v-if="loading && items.length === 0" />

    <EmptyState
      v-else-if="items.length === 0"
      :title="t('system.kycReviews.empty.title')"
      :description="t('system.kycReviews.empty.description')"
    />

    <div v-else class="space-y-4">
      <Card v-for="item in items" :key="item.id" class="p-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <h3 class="font-semibold">{{ item.user?.name }}</h3>
            <p class="text-sm text-muted-foreground">{{ item.user?.email }}</p>
            <Badge class="mt-2" :variant="item.status === 'pending_review' ? 'secondary' : 'outline'">{{ item.status }}</Badge>
          </div>
          <div v-if="item.status === 'pending_review'" class="flex gap-2">
            <Button size="sm" @click="openReview(item)">{{ t('system.kycReviews.actions.review') }}</Button>
          </div>
        </div>
      </Card>
    </div>

    <Dialog v-model:open="reviewOpen">
      <DialogContent class="console-dialog-lg sm:max-w-2xl bg-card border border-border/80 rounded-xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>{{ t('system.kycReviews.review.title') }}</DialogTitle>
          <DialogDescription>{{ selected?.user?.email }}</DialogDescription>
        </DialogHeader>

        <div v-if="detailLoading" class="py-8 flex justify-center"><Loader2 class="animate-spin" /></div>
        <div v-else-if="detail" class="space-y-4">
          <div class="flex flex-wrap gap-2">
            <Button
              v-for="doc in detail.documents"
              :key="doc.id"
              variant="outline"
              size="sm"
              @click="previewDoc(doc.id)"
            >
              {{ doc.type }}: {{ doc.original_name }}
            </Button>
          </div>

          <div v-if="rejectMode" class="grid gap-2">
            <Label>{{ t('system.kycReviews.review.rejectReason') }}</Label>
            <Textarea v-model="rejectReason" rows="3" />
          </div>
        </div>

        <DialogFooter class="gap-2">
          <Button variant="outline" @click="reviewOpen = false">{{ t('common.actions.cancel') }}</Button>
          <template v-if="selected?.status === 'pending_review'">
            <Button v-if="!rejectMode" variant="destructive" @click="rejectMode = true">{{ t('system.kycReviews.actions.reject') }}</Button>
            <Button v-if="rejectMode" variant="destructive" :disabled="acting || rejectReason.length < 5" @click="doReject">
              {{ t('system.kycReviews.actions.confirmReject') }}
            </Button>
            <Button v-if="!rejectMode" :disabled="acting" @click="doApprove">
              <Loader2 v-if="acting" class="w-4 h-4 mr-2 animate-spin" />
              {{ t('system.kycReviews.actions.approve') }}
            </Button>
          </template>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import PageHeader from '@/shared/components/shell/PageHeader.vue';
import EmptyState from '@/shared/components/feedback/EmptyState.vue';
import PageSkeleton from '@/shared/components/feedback/PageSkeleton.vue';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import {
  Button, Card, Badge, Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Label, Textarea, Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
} from '@/shared/components/ui';
import { Loader2 } from 'lucide-vue-next';

const { t } = useI18n();
const toast = useToast();

interface SubmissionItem {
  id: string;
  status: string;
  user?: { id: string; name: string; email: string; kyc_level?: string };
  documents?: { id: string; type: string; original_name: string }[];
}

const items = ref<SubmissionItem[]>([]);
const loading = ref(false);
const statusFilter = ref('pending_review');
const reviewOpen = ref(false);
const selected = ref<SubmissionItem | null>(null);
const detail = ref<{ documents: { id: string; type: string; original_name: string }[] } | null>(null);
const detailLoading = ref(false);
const acting = ref(false);
const rejectMode = ref(false);
const rejectReason = ref('');

async function fetchList() {
  loading.value = true;
  try {
    const res = await api.get('/manage/system/kyc/submissions', { params: { status: statusFilter.value } });
    items.value = Array.isArray(res.data) ? res.data : [];
  } catch (e) {
    toast.error.load(e);
  } finally {
    loading.value = false;
  }
}

async function openReview(item: SubmissionItem) {
  selected.value = item;
  rejectMode.value = false;
  rejectReason.value = '';
  reviewOpen.value = true;
  detailLoading.value = true;
  try {
    const res = await api.get(`/manage/system/kyc/submissions/${item.id}`);
    const sub = res.data?.submission ?? res.data;
    detail.value = { documents: sub?.documents ?? item.documents ?? [] };
  } catch (e) {
    toast.error.load(e);
  } finally {
    detailLoading.value = false;
  }
}

async function previewDoc(documentId: string) {
  if (!selected.value) return;
  try {
    const res = await api.get(
      `/manage/system/kyc/submissions/${selected.value.id}/documents/${documentId}/download`,
      { responseType: 'blob' }
    );
    const url = URL.createObjectURL(res.data as Blob);
    window.open(url, '_blank');
  } catch (e) {
    toast.error.load(e);
  }
}

async function doApprove() {
  if (!selected.value) return;
  acting.value = true;
  try {
    await api.post(`/manage/system/kyc/submissions/${selected.value.id}/approve`, {});
    toast.success.default(t('system.kycReviews.messages.approved'));
    reviewOpen.value = false;
    await fetchList();
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    acting.value = false;
  }
}

async function doReject() {
  if (!selected.value) return;
  acting.value = true;
  try {
    await api.post(`/manage/system/kyc/submissions/${selected.value.id}/reject`, { reason: rejectReason.value });
    toast.success.default(t('system.kycReviews.messages.rejected'));
    reviewOpen.value = false;
    await fetchList();
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    acting.value = false;
  }
}

onMounted(fetchList);
</script>

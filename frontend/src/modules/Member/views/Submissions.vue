<template>
  <MemberPage
    :title="t('member.nav.submissions', 'My submissions')"
    :subtitle="t('member.portal.submissions.subtitle', 'Forms you submitted while signed in.')"
  >
    <ConsoleListCard>
      <div
        v-if="loading"
        class="p-6 text-sm text-muted-foreground"
      >
        {{ t('member.account.loading', 'Loading…') }}
      </div>
      <ul
        v-else-if="submissions.length"
        class="divide-y divide-border/50"
      >
        <li
          v-for="item in submissions"
          :key="item.id"
          class="space-y-2 px-6 py-4"
        >
          <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="font-medium">
              {{ item.form?.name || t('member.portal.submissions.untitledForm', 'Untitled form') }}
            </p>
            <span class="text-xs text-muted-foreground capitalize">
              {{ item.status }}
            </span>
          </div>
          <p class="text-xs text-muted-foreground">
            {{ formatDate(item.created_at) }}
          </p>
          <dl
            v-if="item.data && Object.keys(item.data).length"
            class="grid gap-2 text-sm"
          >
            <div
              v-for="(value, key) in item.data"
              :key="String(key)"
              class="grid gap-0.5"
            >
              <dt class="text-muted-foreground capitalize">
                {{ String(key).replace(/_/g, ' ') }}
              </dt>
              <dd class="font-medium break-words">
                {{ formatValue(value) }}
              </dd>
            </div>
          </dl>
        </li>
      </ul>
      <EmptyState
        v-else
        :title="t('member.portal.submissions.empty', 'No submissions yet.')"
        compact
      />
    </ConsoleListCard>
  </MemberPage>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import MemberPage from '@/modules/Member/components/MemberPage.vue';
import { extractPaginatedRows } from '@/modules/Member/utils/memberApi';
import { EmptyState } from '@/shared/components/feedback';
import { ConsoleListCard } from '@/shared/components/shell';

interface SubmissionRow {
    id: string;
    status: string;
    created_at?: string;
    data?: Record<string, unknown>;
    form?: { name?: string; slug?: string };
}

const { t } = useI18n();
const submissions = ref<SubmissionRow[]>([]);
const loading = ref(true);

const formatDate = (value?: string): string => {
    if (!value) {
        return '—';
    }
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? value : date.toLocaleString();
};

const formatValue = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return '—';
    }
    if (typeof value === 'object') {
        return JSON.stringify(value);
    }
    return String(value);
};

const load = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await api.get('/member/submissions', { params: { per_page: 50 } });
        submissions.value = extractPaginatedRows<SubmissionRow>(response);
    } catch {
        submissions.value = [];
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    void load();
});
</script>

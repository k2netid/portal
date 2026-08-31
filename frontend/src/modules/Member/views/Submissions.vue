<template>
  <div class="space-y-6">
    <div class="space-y-1">
      <h2 class="text-xl font-bold">
        {{ t('member.nav.submissions', 'My submissions') }}
      </h2>
      <p class="text-sm text-muted-foreground">
        {{ t('member.portal.submissions.subtitle', 'Forms you submitted while signed in.') }}
      </p>
    </div>

    <section class="rounded-2xl border border-border/60 bg-background/60 p-5 sm:p-6 space-y-4">
      <p
        v-if="loading"
        class="text-sm text-muted-foreground"
      >
        {{ t('member.account.loading', 'Loading…') }}
      </p>
      <ul
        v-else-if="submissions.length"
        class="space-y-4"
      >
        <li
          v-for="item in submissions"
          :key="item.id"
          class="space-y-2 border-b border-border/40 pb-4 last:border-0 last:pb-0"
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
      <p
        v-else
        class="text-sm text-muted-foreground"
      >
        {{ t('member.portal.submissions.empty', 'No submissions yet.') }}
      </p>
    </section>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { extractPaginatedRows } from '@/modules/Member/utils/memberApi';

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

const extractRows = (response: { data?: unknown }): SubmissionRow[] => (
    extractPaginatedRows<SubmissionRow>(response)
);

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
        submissions.value = extractRows(response);
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

<template>
  <ConsoleListCard>
    <template
      v-if="showHeader"
      #toolbar
    >
      <div class="flex w-full items-center justify-between gap-3">
        <h3 class="text-base font-semibold tracking-tight text-foreground">
          {{ t('member.nav.submissions', 'My submissions') }}
        </h3>
        <router-link
          v-if="showViewAll"
          :to="{ name: 'member.submissions' }"
          class="text-sm font-semibold text-primary hover:underline underline-offset-4"
        >
          {{ t('member.portal.widgets.viewAll', 'View all') }}
        </router-link>
      </div>
    </template>

    <div class="p-5 sm:p-6 space-y-4">
      <p
        v-if="loading"
        class="text-sm text-muted-foreground"
      >
        {{ t('member.account.loading', 'Loading…') }}
      </p>
      <ul
        v-else-if="submissions.length"
        class="space-y-3"
      >
        <li
          v-for="item in submissions"
          :key="item.id"
          class="flex items-center justify-between gap-3"
        >
          <div class="min-w-0">
            <p class="font-medium truncate">
              {{ item.form?.name || t('member.portal.submissions.untitledForm', 'Untitled form') }}
            </p>
            <p class="text-xs text-muted-foreground capitalize">
              {{ item.status }}
            </p>
          </div>
        </li>
      </ul>
      <p
        v-else
        class="text-sm text-muted-foreground"
      >
        {{ t('member.portal.submissions.empty', 'No submissions yet.') }}
      </p>
    </div>
  </ConsoleListCard>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { extractPaginatedRows } from '@/modules/Member/utils/memberApi';
import { ConsoleListCard } from '@/shared/components/shell';

interface SubmissionRow {
    id: string;
    status: string;
    form?: { name?: string };
}

const props = withDefaults(defineProps<{
    limit?: number;
    showHeader?: boolean;
    showViewAll?: boolean;
}>(), {
    limit: 3,
    showHeader: true,
    showViewAll: true,
});

const { t } = useI18n();
const submissions = ref<SubmissionRow[]>([]);
const loading = ref(true);

onMounted(() => {
    void (async () => {
        loading.value = true;
        try {
            const response = await api.get('/member/submissions', { params: { per_page: props.limit } });
            submissions.value = extractPaginatedRows<SubmissionRow>(response);
        } catch {
            submissions.value = [];
        } finally {
            loading.value = false;
        }
    })();
});
</script>

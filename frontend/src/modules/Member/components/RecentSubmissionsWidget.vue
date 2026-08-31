<template>
  <section class="rounded-lg border border-border/60 bg-card shadow-sm p-5 space-y-4">
    <div class="flex items-center justify-between gap-3">
      <h3 class="text-lg font-bold">
        {{ t('member.nav.submissions', 'My submissions') }}
      </h3>
      <router-link
        v-if="showViewAll"
        :to="{ name: 'member.submissions' }"
        class="text-sm font-semibold text-primary"
      >
        {{ t('member.portal.widgets.viewAll', 'View all') }}
      </router-link>
    </div>
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
  </section>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { extractPaginatedRows } from '@/modules/Member/utils/memberApi';

interface SubmissionRow {
    id: string;
    status: string;
    form?: { name?: string };
}

const props = withDefaults(defineProps<{
    limit?: number;
    showViewAll?: boolean;
}>(), {
    limit: 3,
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

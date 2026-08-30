<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="t('member.title')"
      :subtitle="t('member.subtitle')"
    />

    <ConsoleListCard>
      <template #toolbar>
        <div class="relative flex-1">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
          <Input
            v-model="search"
            :placeholder="t('member.filters.search')"
            class="pl-9"
            @input="debounceSearch"
          />
        </div>
      </template>

      <div
        v-if="loading"
        class="p-6 text-sm text-muted-foreground"
      >
        {{ t('member.messages.loading') }}
      </div>
      <div
        v-else-if="members.length === 0"
        class="p-6 text-sm text-muted-foreground"
      >
        {{ t('member.messages.empty') }}
      </div>
      <div
        v-else
        class="overflow-x-auto"
      >
        <table class="w-full text-sm">
          <thead class="border-b text-left text-muted-foreground">
            <tr>
              <th class="px-6 py-3 font-medium">
                {{ t('member.table.member') }}
              </th>
              <th class="px-6 py-3 font-medium">
                {{ t('member.table.status') }}
              </th>
              <th class="px-6 py-3 font-medium">
                {{ t('member.table.verified') }}
              </th>
              <th class="px-6 py-3 font-medium">
                {{ t('member.table.joinedAt') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in members"
              :key="row.id"
              class="border-b last:border-0"
            >
              <td class="px-6 py-3">
                <div class="font-medium">
                  {{ row.name || row.email }}
                </div>
                <div class="text-xs text-muted-foreground">
                  {{ row.email }}
                </div>
              </td>
              <td class="px-6 py-3">
                {{ row.status === 'active' ? t('member.status.active') : t('member.status.inactive') }}
              </td>
              <td class="px-6 py-3">
                {{ row.email_verified_at ? t('member.verified.yes') : t('member.verified.no') }}
              </td>
              <td class="px-6 py-3 text-muted-foreground">
                {{ formatDate(row.created_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </ConsoleListCard>
  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Search } from 'lucide-vue-next';
import api from '@/engine/api/client';
import { memberPaths } from '@/engine/api/paths';
import { parseResponse } from '@/shared/utils/responseParser';
import { PageHeader, ConsoleListCard } from '@/shared/components/shell';
import { Input } from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';

interface MemberRow {
    id: string;
    name: string | null;
    email: string;
    status: string;
    email_verified_at: string | null;
    created_at: string;
}

const { t } = useI18n();
const toast = useToast();
const loading = ref(true);
const search = ref('');
const members = ref<MemberRow[]>([]);
let searchTimer: ReturnType<typeof setTimeout> | undefined;

const formatDate = (value: string): string => {
    if (!value) {
        return '—';
    }
    return new Date(value).toLocaleDateString();
};

const fetchMembers = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await api.get(memberPaths.index, {
            params: { search: search.value, per_page: 25 },
        });
        const { data } = parseResponse<MemberRow>(response);
        members.value = data;
    } catch (error: unknown) {
        toast.error.fromResponse(error);
        members.value = [];
    } finally {
        loading.value = false;
    }
};

const debounceSearch = (): void => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }
    searchTimer = setTimeout(() => {
        void fetchMembers();
    }, 300);
};

onMounted(() => {
    void fetchMembers();
});

onUnmounted(() => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }
});
</script>

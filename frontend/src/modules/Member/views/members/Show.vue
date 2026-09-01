<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="member?.name || member?.email || t('member.detail.title')"
      :subtitle="member?.email || ''"
    >
      <template #actions>
        <Button
          variant="outline"
          size="sm"
          @click="router.back()"
        >
          {{ t('member.detail.back') }}
        </Button>
        <router-link
          v-if="canManage && member && !member.deleted_at"
          :to="{ name: 'members.edit', params: { id: member.id } }"
        >
          <Button size="sm">
            {{ t('common.actions.edit') }}
          </Button>
        </router-link>
        <Button
          v-if="canManage && member && !member.deleted_at"
          variant="outline"
          size="sm"
          @click="toggleStatus"
        >
          {{
            member.status === 'active'
              ? t('member.actions.deactivate')
              : t('member.actions.activate')
          }}
        </Button>
      </template>
    </PageHeader>

    <div
      v-if="loading"
      class="text-sm text-muted-foreground"
    >
      {{ t('member.messages.loading') }}
    </div>

    <template v-else-if="member">
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <ConsoleStatCard
          :label="t('member.detail.activity.bookmarks')"
          :value="String(member.activity?.bookmarks ?? 0)"
          :icon="Bookmark"
          tone="primary"
        />
        <ConsoleStatCard
          :label="t('member.detail.activity.comments')"
          :value="String(member.activity?.comments ?? 0)"
          :icon="MessageSquare"
          tone="info"
        />
        <ConsoleStatCard
          :label="t('member.detail.activity.submissions')"
          :value="String(member.activity?.submissions ?? 0)"
          :icon="FileText"
          tone="success"
        />
        <ConsoleStatCard
          :label="t('member.detail.activity.newsletter')"
          :value="newsletterLabel"
          :icon="Mail"
          tone="warning"
        />
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <ConsoleFormCard :title="t('member.detail.profile')">
          <dl class="grid gap-4 text-sm">
            <div class="space-y-1">
              <dt class="text-muted-foreground">{{ t('member.table.member') }}</dt>
              <dd class="font-medium">{{ member.name || '—' }}</dd>
            </div>
            <div class="space-y-1">
              <dt class="text-muted-foreground">{{ t('member.table.phone') }}</dt>
              <dd class="font-medium">{{ member.phone || '—' }}</dd>
            </div>
            <div class="space-y-1">
              <dt class="text-muted-foreground">{{ t('member.table.status') }}</dt>
              <dd class="font-medium capitalize">{{ member.status }}</dd>
            </div>
            <div class="space-y-1">
              <dt class="text-muted-foreground">{{ t('member.table.verified') }}</dt>
              <dd class="font-medium">
                {{ member.email_verified_at ? t('member.verified.yes') : t('member.verified.no') }}
              </dd>
            </div>
            <div
              v-if="member.pending_email"
              class="space-y-1"
            >
              <dt class="text-muted-foreground">{{ t('member.detail.pendingEmail') }}</dt>
              <dd class="font-medium">{{ member.pending_email }}</dd>
            </div>
            <div class="space-y-1">
              <dt class="text-muted-foreground">{{ t('member.table.lastLogin') }}</dt>
              <dd class="font-medium">{{ formatDate(member.last_login_at) }}</dd>
            </div>
            <div class="space-y-1">
              <dt class="text-muted-foreground">{{ t('member.table.joinedAt') }}</dt>
              <dd class="font-medium">{{ formatDate(member.created_at) }}</dd>
            </div>
            <div
              v-if="member.bio"
              class="space-y-1 lg:col-span-2"
            >
              <dt class="text-muted-foreground">{{ t('member.portal.profile.bio') }}</dt>
              <dd class="font-medium whitespace-pre-wrap">{{ member.bio }}</dd>
            </div>
          </dl>
        </ConsoleFormCard>

        <ConsoleFormCard :title="t('member.detail.preferences')">
          <dl class="grid gap-4 text-sm">
            <div class="space-y-1">
              <dt class="text-muted-foreground">{{ t('member.portal.profile.locale') }}</dt>
              <dd class="font-medium">{{ member.locale || t('member.portal.profile.localeDefault') }}</dd>
            </div>
            <div class="space-y-1">
              <dt class="text-muted-foreground">{{ t('member.portal.profile.timezone') }}</dt>
              <dd class="font-medium">{{ member.timezone || t('member.portal.profile.timezoneDefault') }}</dd>
            </div>
            <div
              v-if="member.deleted_at"
              class="space-y-1"
            >
              <dt class="text-muted-foreground">{{ t('common.labels.deleted') }}</dt>
              <dd class="font-medium text-destructive">{{ formatDate(member.deleted_at) }}</dd>
            </div>
          </dl>

          <div
            v-if="canManage"
            class="mt-6 flex flex-wrap gap-2 border-t border-border/50 pt-4"
          >
            <Button
              v-if="!member.email_verified_at && !member.deleted_at"
              variant="outline"
              size="sm"
              @click="verifyEmail"
            >
              {{ t('member.actions.verify') }}
            </Button>
            <Button
              v-if="member.deleted_at"
              variant="outline"
              size="sm"
              @click="restore"
            >
              {{ t('common.actions.restore') }}
            </Button>
            <Button
              v-if="!member.deleted_at"
              variant="outline"
              size="sm"
              class="text-destructive border-destructive/40"
              @click="softDelete"
            >
              {{ t('common.actions.delete') }}
            </Button>
            <Button
              v-if="member.deleted_at"
              variant="outline"
              size="sm"
              class="text-destructive border-destructive/40"
              @click="forceDelete"
            >
              {{ t('common.actions.forceDelete') }}
            </Button>
          </div>
        </ConsoleFormCard>
      </div>

      <ConsoleFormCard :title="t('member.detail.securityEvents')">
        <div
          v-if="eventsLoading"
          class="text-sm text-muted-foreground"
        >
          {{ t('member.messages.loading') }}
        </div>
        <p
          v-else-if="securityEvents.length === 0"
          class="text-sm text-muted-foreground"
        >
          {{ t('member.detail.securityEventsEmpty') }}
        </p>
        <ul
          v-else
          class="divide-y divide-border/60"
        >
          <li
            v-for="event in securityEvents"
            :key="event.id"
            class="py-3 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-1 text-sm"
          >
            <div class="min-w-0 space-y-0.5">
              <p class="font-medium">
                {{ formatSecurityEventLabel(t, event.event_type) }}
              </p>
              <p class="text-muted-foreground truncate">
                {{ event.description || '—' }}
              </p>
            </div>
            <div class="shrink-0 text-xs text-muted-foreground text-left sm:text-right space-y-0.5">
              <p>{{ formatDate(event.created_at) }}</p>
              <p v-if="event.ip_address">{{ event.ip_address }}</p>
            </div>
          </li>
        </ul>
      </ConsoleFormCard>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { Bookmark, FileText, Mail, MessageSquare } from 'lucide-vue-next';
import { formatSecurityEventLabel } from '@/modules/Core/Security/utils/securityEventLabel';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import MemberDirectoryService, { type MemberDirectoryDetail } from '@/modules/Member/services/memberDirectoryService';
import { PageHeader, ConsoleStatCard, ConsoleFormCard } from '@/shared/components/shell';
import { Button } from '@/shared/components/ui';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useToast } from '@/shared/composables/useToast';
import { parseSingleResponse, ensureArray } from '@/shared/utils/responseParser';

interface MemberSecurityEvent {
    id: string;
    event_type: string;
    ip_address?: string | null;
    description?: string | null;
    created_at?: string | null;
}

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const toast = useToast();
const { confirm } = useConfirm();
const authStore = useAuthStore();

const loading = ref(true);
const eventsLoading = ref(false);
const member = ref<MemberDirectoryDetail | null>(null);
const securityEvents = ref<MemberSecurityEvent[]>([]);

const canManage = computed(() => authStore.hasPermission('manage members'));

const newsletterLabel = computed(() => {
    const subscribed = member.value?.activity?.newsletter_subscribed;
    if (subscribed === true) {
        return t('member.portal.newsletter.subscribed');
    }
    if (subscribed === false) {
        return t('member.portal.newsletter.notSubscribed');
    }
    return '—';
});

const formatDate = (value?: string | null): string => {
    if (!value) {
        return '—';
    }
    return new Date(value).toLocaleString();
};

const loadEvents = async (id: string): Promise<void> => {
    eventsLoading.value = true;
    try {
        const response = await MemberDirectoryService.securityEvents(id, { limit: 20 });
        securityEvents.value = ensureArray<MemberSecurityEvent>(parseSingleResponse(response));
    } catch {
        securityEvents.value = [];
    } finally {
        eventsLoading.value = false;
    }
};

const load = async (): Promise<void> => {
    loading.value = true;
    try {
        const id = String(route.params.id);
        const response = await MemberDirectoryService.show(id);
        member.value = parseSingleResponse<MemberDirectoryDetail>(response);
        void loadEvents(id);
    } catch (error: unknown) {
        toast.error.fromResponse(error);
        member.value = null;
        securityEvents.value = [];
    } finally {
        loading.value = false;
    }
};

const toggleStatus = async (): Promise<void> => {
    if (!member.value) {
        return;
    }
    const nextStatus = member.value.status === 'active' ? 'inactive' : 'active';
    try {
        await MemberDirectoryService.update(member.value.id, { status: nextStatus });
        toast.success.action(t('member.actions.updated'));
        await load();
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    }
};

const verifyEmail = async (): Promise<void> => {
    if (!member.value) {
        return;
    }
    try {
        await MemberDirectoryService.update(member.value.id, { verify_email: true });
        toast.success.action(t('member.messages.verified'));
        await load();
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    }
};

const softDelete = async (): Promise<void> => {
    if (!member.value) {
        return;
    }
    const confirmed = await confirm({
        title: t('common.actions.delete'),
        message: t('member.confirm.delete', { email: member.value.email }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });
    if (!confirmed) {
        return;
    }
    try {
        await MemberDirectoryService.destroy(member.value.id);
        toast.success.delete(t('member.title_singular', 'Member'));
        await router.push({ name: 'members.index' });
    } catch (error: unknown) {
        toast.error.delete(error, t('member.title_singular', 'Member'));
    }
};

const restore = async (): Promise<void> => {
    if (!member.value) {
        return;
    }
    try {
        await MemberDirectoryService.restore(member.value.id);
        toast.success.restore(t('member.title_singular', 'Member'));
        await load();
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    }
};

const forceDelete = async (): Promise<void> => {
    if (!member.value) {
        return;
    }
    const confirmed = await confirm({
        title: t('common.actions.forceDelete'),
        message: t('member.confirm.forceDelete', { email: member.value.email }),
        variant: 'danger',
        confirmText: t('common.actions.forceDelete'),
    });
    if (!confirmed) {
        return;
    }
    try {
        await MemberDirectoryService.forceDelete(member.value.id);
        toast.success.delete(t('member.title_singular', 'Member'));
        await router.push({ name: 'members.index' });
    } catch (error: unknown) {
        toast.error.delete(error, t('member.title_singular', 'Member'));
    }
};

onMounted(() => {
    void load();
});
</script>

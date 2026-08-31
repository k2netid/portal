<template>
  <div class="space-y-6">
    <div class="space-y-1">
      <h2 class="text-xl font-bold">
        {{ t('member.portal.dashboard.title', 'Dashboard') }}
      </h2>
      <p class="text-sm text-muted-foreground">
        {{ t('member.portal.dashboard.subtitle', 'Welcome back. Manage bookmarks and account settings from here.') }}
      </p>
    </div>

    <p
      v-if="memberStore.member && memberStore.member.email_verified !== true"
      class="text-sm rounded-2xl border border-amber-500/40 bg-amber-500/10 px-4 py-3"
    >
      {{ t('member.account.verifyHint', 'Confirm your email to finish setting up this reader account.') }}
      <button
        type="button"
        class="ml-2 font-semibold text-primary"
        :disabled="resending"
        @click="resend"
      >
        {{ resendLabel }}
      </button>
    </p>

    <div class="grid gap-6 lg:grid-cols-2">
      <component
        :is="widget.component"
        v-for="widget in dashboardWidgets"
        :key="`${widget.extensionSlug ?? 'core'}:${widget.slug}`"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { memberAreaRegistry } from '@/engine/memberArea/MemberAreaRegistry';
import { useMemberStore } from '@/modules/Member/stores/member';
import { useSystemStore } from '@/modules/Core/System/stores/system';

const { t } = useI18n();
const memberStore = useMemberStore();
const systemStore = useSystemStore();

const resending = ref(false);
const resent = ref(false);

const portalContext = computed(() => ({
    activeExtensions: memberStore.portal?.active_extensions
        ?? systemStore.activeExtensions
        ?? [],
    emailVerified: memberStore.member?.email_verified === true,
    capabilities: memberStore.portalCapabilities,
}));

const dashboardWidgets = computed(() => (
    memberAreaRegistry.getDashboardWidgets(
        portalContext.value,
        memberStore.portal?.widgets,
    ).map((widget) => ({
        ...widget,
        component: defineAsyncComponent(widget.component as () => Promise<{ default: unknown }>),
    }))
));

const resendLabel = computed(() => (
    resent.value
        ? t('member.account.resent', 'Sent')
        : t('member.account.resend', 'Resend email')
));

const resend = async (): Promise<void> => {
    resending.value = true;
    try {
        await memberStore.resendVerification();
        resent.value = true;
    } finally {
        resending.value = false;
    }
};
</script>

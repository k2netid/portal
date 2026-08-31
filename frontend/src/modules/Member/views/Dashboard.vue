<template>
  <div class="space-y-6">
    <header class="space-y-1 border-b border-border/50 pb-5">
      <h1 class="text-2xl font-bold tracking-tight text-foreground">
        {{ t('member.portal.dashboard.title', 'Overview') }}
      </h1>
      <p class="text-sm text-muted-foreground max-w-3xl">
        {{ greeting }}
      </p>
    </header>

    <p
      v-if="memberStore.member && memberStore.member.email_verified !== true"
      class="text-sm rounded-lg border border-amber-500/35 bg-amber-500/10 px-4 py-3"
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

    <section
      v-if="dashboardWidgets.length"
      class="space-y-4"
    >
      <h2 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">
        {{ t('member.portal.dashboard.activity', 'Your activity') }}
      </h2>
      <div class="grid gap-5 sm:grid-cols-1 lg:grid-cols-2 xl:grid-cols-2 2xl:grid-cols-3">
        <component
          :is="widget.component"
          v-for="widget in dashboardWidgets"
          :key="`${widget.extensionSlug ?? 'core'}:${widget.slug}`"
        />
      </div>
    </section>

    <section
      v-else
      class="rounded-lg border border-dashed border-border/70 bg-card px-5 py-10 text-center"
    >
      <p class="text-sm font-medium">
        {{ t('member.portal.dashboard.emptyTitle', 'Nothing saved yet') }}
      </p>
      <p class="mt-1.5 text-sm text-muted-foreground max-w-md mx-auto">
        {{ t('member.portal.dashboard.emptyBody', 'Bookmark articles, leave comments, or submit forms while browsing the site — they will show up here.') }}
      </p>
      <router-link
        to="/blog"
        class="inline-flex mt-4 text-sm font-semibold text-primary hover:underline underline-offset-4"
      >
        {{ t('member.portal.dashboard.browseSite', 'Browse articles') }}
      </router-link>
    </section>
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

const greeting = computed(() => {
    const name = memberStore.member?.name?.trim();
    if (name) {
        return `${t('member.portal.dashboard.welcomePrefix', 'Welcome back')}, ${name}. ${t('member.portal.dashboard.subtitle', 'Manage your reader account and activity below.')}`;
    }
    return t('member.portal.dashboard.subtitle', 'Manage your reader account and activity below.');
});

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

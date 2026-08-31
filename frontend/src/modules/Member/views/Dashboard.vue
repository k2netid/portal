<template>
  <div class="space-y-6">
    <div class="space-y-1">
      <h2 class="text-xl font-bold tracking-tight">
        {{ t('member.portal.dashboard.title', 'Dashboard') }}
      </h2>
      <p class="text-sm text-muted-foreground">
        {{ greeting }}
      </p>
    </div>

    <p
      v-if="memberStore.member && memberStore.member.email_verified !== true"
      class="text-sm rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-3"
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

    <!-- Quick links -->
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
      <router-link
        v-for="card in quickLinks"
        :key="card.to"
        :to="{ name: card.to }"
        class="group rounded-xl border border-border/70 bg-background/80 p-4 transition-colors hover:border-primary/40 hover:bg-muted/40"
      >
        <p class="text-sm font-semibold text-foreground group-hover:text-primary">
          {{ card.title }}
        </p>
        <p class="mt-1 text-xs text-muted-foreground leading-relaxed">
          {{ card.body }}
        </p>
      </router-link>
    </section>

    <!-- Adaptive widgets -->
    <section
      v-if="dashboardWidgets.length"
      class="space-y-3"
    >
      <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
        {{ t('member.portal.dashboard.activity', 'Your activity') }}
      </h3>
      <div class="grid gap-4 lg:grid-cols-2">
        <component
          :is="widget.component"
          v-for="widget in dashboardWidgets"
          :key="`${widget.extensionSlug ?? 'core'}:${widget.slug}`"
        />
      </div>
    </section>

    <section
      v-else
      class="rounded-xl border border-dashed border-border/80 bg-muted/20 px-5 py-8 text-center"
    >
      <p class="text-sm font-medium text-foreground">
        {{ t('member.portal.dashboard.emptyTitle', 'No pack widgets yet') }}
      </p>
      <p class="mt-1 text-sm text-muted-foreground max-w-md mx-auto">
        {{ t('member.portal.dashboard.emptyBody', 'When Publishing, Newsletter, or Forms are active, bookmarks, comments, and submissions will show up here.') }}
      </p>
      <div class="mt-4 flex flex-wrap justify-center gap-2">
        <router-link
          :to="{ name: 'member.profile' }"
          class="text-sm font-semibold text-primary"
        >
          {{ t('member.portal.nav.profile', 'Profile') }}
        </router-link>
        <span class="text-muted-foreground">·</span>
        <router-link
          :to="{ name: 'member.security' }"
          class="text-sm font-semibold text-primary"
        >
          {{ t('member.portal.nav.security', 'Security') }}
        </router-link>
      </div>
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
        return t('member.portal.dashboard.hello', 'Welcome back, {name}.', { name });
    }
    return t('member.portal.dashboard.subtitle', 'Welcome back. Manage bookmarks and account settings from here.');
});

const quickLinks = computed(() => {
    const links = [
        {
            to: 'member.profile',
            title: t('member.portal.nav.profile', 'Profile'),
            body: t('member.portal.dashboard.cardProfile', 'Update your display name and review verification status.'),
        },
        {
            to: 'member.security',
            title: t('member.portal.nav.security', 'Security'),
            body: t('member.portal.dashboard.cardSecurity', 'Change password, email, or delete your reader account.'),
        },
    ];

    const caps = memberStore.portalCapabilities;
    if (caps.includes('member.bookmarks')) {
        links.push({
            to: 'member.bookmarks',
            title: t('member.nav.bookmarks', 'Bookmarks'),
            body: t('member.portal.dashboard.cardBookmarks', 'Articles you saved for later.'),
        });
    }
    if (caps.includes('member.comments')) {
        links.push({
            to: 'member.comments',
            title: t('member.nav.comments', 'Comments'),
            body: t('member.portal.dashboard.cardComments', 'Comments you posted on this site.'),
        });
    }
    if (caps.includes('member.newsletter')) {
        links.push({
            to: 'member.newsletter',
            title: t('member.nav.newsletter', 'Newsletter'),
            body: t('member.portal.dashboard.cardNewsletter', 'Manage email updates for this account.'),
        });
    }
    if (caps.includes('member.submissions')) {
        links.push({
            to: 'member.submissions',
            title: t('member.nav.submissions', 'My submissions'),
            body: t('member.portal.dashboard.cardSubmissions', 'Forms you submitted while signed in.'),
        });
    }

    return links;
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

<template>
  <div class="sarangenge-theme flex-1 flex flex-col py-10 md:py-14">
    <div class="max-w-5xl mx-auto w-full px-4 space-y-8">
      <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-1">
          <h1 class="text-2xl md:text-3xl font-extrabold font-heading">
            {{ t('member.portal.title', 'My account') }}
          </h1>
          <p
            v-if="memberStore.member?.email"
            class="text-sm text-muted-foreground"
          >
            {{ memberStore.member.email }}
          </p>
        </div>
        <Button
          variant="outline"
          size="sm"
          @click="logout"
        >
          {{ t('member.portal.logout', 'Sign out') }}
        </Button>
      </header>

      <div class="flex flex-col md:flex-row gap-8">
        <nav
          class="md:w-52 shrink-0"
          aria-label="Member portal"
        >
          <ul class="flex md:flex-col gap-1 overflow-x-auto pb-1 md:pb-0">
            <li
              v-for="item in navItems"
              :key="item.slug"
            >
              <router-link
                :to="{ name: item.routeName }"
                class="block whitespace-nowrap rounded-xl px-3 py-2 text-sm font-medium transition-colors"
                :class="route.name === item.routeName
                  ? 'bg-primary text-primary-foreground'
                  : 'text-muted-foreground hover:bg-muted hover:text-foreground'"
              >
                {{ t(item.labelKey) }}
              </router-link>
            </li>
          </ul>
        </nav>

        <main class="flex-1 min-w-0">
          <router-view />
        </main>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { memberAreaRegistry } from '@/engine/memberArea/MemberAreaRegistry';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useMemberStore } from '@/modules/Member/stores/member';
import { useSystemStore } from '@/modules/Core/System/stores/system';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const memberStore = useMemberStore();
const systemStore = useSystemStore();

const portalContext = computed(() => ({
    activeExtensions: memberStore.portal?.active_extensions
        ?? systemStore.activeExtensions
        ?? [],
    emailVerified: memberStore.member?.email_verified === true,
    capabilities: memberStore.portalCapabilities,
}));

const navItems = computed(() => {
    const portalNav = memberStore.portal?.navigation;
    if (portalNav?.length) {
        return portalNav.map((item) => ({
            slug: item.slug,
            labelKey: item.label_key,
            routeName: item.route,
        }));
    }

    return memberAreaRegistry.getNavigation(portalContext.value).map((item) => ({
        slug: item.slug,
        labelKey: item.labelKey,
        routeName: item.routeName,
    }));
});

const logout = async (): Promise<void> => {
    await memberStore.logout();
    await router.replace('/member/login');
};

onMounted(() => {
    void memberStore.fetchPortal();
});
</script>

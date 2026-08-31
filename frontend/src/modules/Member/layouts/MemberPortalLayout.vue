<template>
  <div class="member-portal flex-1 flex flex-col py-8 md:py-12">
    <div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8">
      <div class="rounded-2xl border border-border/70 bg-card/80 shadow-sm overflow-hidden">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-border/60 px-5 py-4 sm:px-6">
          <div class="flex items-center gap-3 min-w-0">
            <div
              class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary text-primary-foreground text-sm font-bold tracking-wide"
              aria-hidden="true"
            >
              {{ initials }}
            </div>
            <div class="min-w-0 space-y-0.5">
              <h1 class="text-lg sm:text-xl font-bold truncate">
                {{ t('member.portal.title', 'My account') }}
              </h1>
              <p
                v-if="memberStore.member?.email"
                class="text-xs sm:text-sm text-muted-foreground truncate"
              >
                {{ memberStore.member.name || memberStore.member.email }}
                <span class="text-muted-foreground/70"> · {{ memberStore.member.email }}</span>
              </p>
            </div>
          </div>
          <Button
            variant="outline"
            size="sm"
            class="shrink-0"
            @click="logout"
          >
            {{ t('member.portal.logout', 'Sign out') }}
          </Button>
        </header>

        <div class="flex flex-col md:flex-row min-h-[28rem]">
          <nav
            class="md:w-56 lg:w-64 shrink-0 border-b md:border-b-0 md:border-r border-border/60 bg-muted/20"
            aria-label="Member portal"
          >
            <ul class="flex md:flex-col gap-1 p-3 overflow-x-auto">
              <li
                v-for="item in navItems"
                :key="item.slug"
                class="shrink-0"
              >
                <router-link
                  :to="{ name: item.routeName }"
                  class="block whitespace-nowrap rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
                  :class="isActive(item.routeName)
                    ? 'bg-primary text-primary-foreground shadow-sm'
                    : 'text-muted-foreground hover:bg-background hover:text-foreground'"
                >
                  {{ t(item.labelKey) }}
                </router-link>
              </li>
            </ul>
          </nav>

          <main class="flex-1 min-w-0 p-5 sm:p-6 lg:p-8">
            <router-view />
          </main>
        </div>
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

const initials = computed(() => {
    const name = memberStore.member?.name?.trim() || memberStore.member?.email || '?';
    const parts = name.split(/[\s@._-]+/).filter(Boolean);
    if (parts.length >= 2) {
        return `${parts[0]![0] ?? ''}${parts[1]![0] ?? ''}`.toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
});

const isActive = (routeName: string): boolean => route.name === routeName;

const logout = async (): Promise<void> => {
    await memberStore.logout();
    await router.replace('/member/login');
};

onMounted(() => {
    void memberStore.fetchPortal();
});
</script>

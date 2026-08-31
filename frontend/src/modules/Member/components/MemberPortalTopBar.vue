<template>
  <header
    class="sticky top-0 z-[100] w-full border-b border-border/60 bg-background/95 backdrop-blur-md supports-[backdrop-filter]:bg-background/80"
  >
    <div class="flex items-center gap-3 sm:gap-4 h-14 px-4 sm:px-6 lg:px-8">
      <!-- Branding: logo + name (+ tagline on lg) -->
      <router-link
        to="/"
        class="flex items-center gap-2.5 shrink-0 min-w-0 max-w-[min(100%,14rem)] sm:max-w-xs group"
      >
        <div
          v-if="siteLogo"
          class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border/50 bg-card overflow-hidden"
        >
          <img
            :src="siteLogo"
            :alt="siteName"
            class="h-full w-full object-contain p-0.5"
            width="36"
            height="36"
          >
        </div>
        <div
          v-else
          class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary text-primary-foreground text-sm font-bold"
          aria-hidden="true"
        >
          {{ siteInitial }}
        </div>
        <div class="min-w-0 leading-tight">
          <span class="block text-sm font-bold tracking-tight text-foreground truncate group-hover:text-primary transition-colors">
            {{ siteName }}
          </span>
          <span
            v-if="siteTagline"
            class="hidden lg:block text-[11px] text-muted-foreground truncate max-w-[12rem]"
          >
            {{ siteTagline }}
          </span>
        </div>
      </router-link>

      <div
        class="hidden sm:block h-6 w-px bg-border/70 shrink-0"
        aria-hidden="true"
      />

      <!-- Breadcrumbs -->
      <MemberPortalBreadcrumbs class="hidden sm:flex flex-1 min-w-0" />

      <!-- Right actions -->
      <div class="flex items-center gap-1.5 sm:gap-2 shrink-0 ml-auto">
        <router-link
          to="/"
          class="hidden sm:inline-flex items-center gap-1.5 h-9 px-3 text-xs font-semibold text-muted-foreground border border-border/60 rounded-lg hover:text-foreground hover:bg-muted/50 transition-colors"
        >
          <ArrowLeft class="w-3.5 h-3.5" />
          {{ t('member.portal.backToSite', 'Back to site') }}
        </router-link>

        <ThemeToggle />

        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <button
              type="button"
              class="inline-flex items-center gap-2 h-9 max-w-[11rem] px-2.5 sm:px-3 text-xs font-semibold text-foreground bg-muted/60 hover:bg-muted border border-border/50 rounded-lg transition-colors focus:outline-none"
            >
              <img
                v-if="memberStore.member?.avatar"
                :src="memberStore.member.avatar"
                :alt="memberDisplayName"
                class="h-6 w-6 shrink-0 rounded-full object-cover"
              >
              <span
                v-else
                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/15 text-primary text-[10px] font-bold"
                aria-hidden="true"
              >
                {{ memberInitials }}
              </span>
              <span class="truncate hidden sm:inline">{{ memberDisplayName }}</span>
              <ChevronDown class="w-3.5 h-3.5 text-muted-foreground shrink-0" />
            </button>
          </DropdownMenuTrigger>
          <DropdownMenuContent
            align="end"
            class="w-52"
          >
            <div class="px-3 py-2 border-b border-border/50 mb-1">
              <p class="text-sm font-semibold truncate">
                {{ memberDisplayName }}
              </p>
              <p
                v-if="memberStore.member?.email"
                class="text-xs text-muted-foreground truncate"
              >
                {{ memberStore.member.email }}
              </p>
            </div>
            <DropdownMenuItem as-child>
              <router-link
                to="/member/profile"
                class="flex items-center gap-2 cursor-pointer w-full"
              >
                <UserIcon class="w-4 h-4 text-primary" />
                <span>{{ t('member.portal.nav.profile', 'Profile') }}</span>
              </router-link>
            </DropdownMenuItem>
            <DropdownMenuItem
              class="text-destructive focus:text-destructive cursor-pointer"
              @click="handleLogout"
            >
              <LogOut class="w-4 h-4 mr-2" />
              <span>{{ t('member.portal.logout', 'Sign out') }}</span>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import {
  ArrowLeft,
  ChevronDown,
  LogOut,
  User as UserIcon,
} from 'lucide-vue-next';
import { ThemeToggle } from '@/shared/components';
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/modules/Layout/views/themes/janari/ui';
import { useMemberStore } from '@/modules/Member/stores/member';
import { useMemberPortalBranding } from '@/modules/Member/composables/useMemberPortalBranding';
import MemberPortalBreadcrumbs from '@/modules/Member/components/MemberPortalBreadcrumbs.vue';

const { t } = useI18n({ useScope: 'global' });
const router = useRouter();
const memberStore = useMemberStore();
const { siteName, siteTagline, siteLogo } = useMemberPortalBranding();

const siteInitial = computed(() => siteName.value.substring(0, 1).toUpperCase() || 'J');

const memberDisplayName = computed(() =>
  memberStore.member?.name?.trim()
    || t('member.portal.title', 'My account'),
);

const memberInitials = computed(() => {
  const raw = memberStore.member?.name?.trim() || memberStore.member?.email || '?';
  const parts = raw.split(/[\s@._-]+/).filter(Boolean);
  if (parts.length >= 2) {
    return `${parts[0]![0] ?? ''}${parts[1]![0] ?? ''}`.toUpperCase();
  }
  return raw.slice(0, 2).toUpperCase();
});

const handleLogout = async () => {
  await memberStore.logout();
  await router.replace('/');
};
</script>

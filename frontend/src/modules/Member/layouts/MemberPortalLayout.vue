<template>
  <div class="member-portal flex-1 w-full flex flex-col min-h-0 bg-muted/30">
    <!-- Mobile sidebar toggle -->
    <div class="lg:hidden flex items-center justify-between gap-3 border-b border-border/60 bg-background px-4 py-2.5 shrink-0">
      <button
        type="button"
        class="inline-flex items-center gap-2 text-sm font-semibold text-foreground"
        :aria-expanded="sidebarOpen"
        @click="sidebarOpen = !sidebarOpen"
      >
        <Menu
          v-if="!sidebarOpen"
          class="w-5 h-5"
        />
        <X
          v-else
          class="w-5 h-5"
        />
        {{ sidebarOpen
          ? t('member.portal.closeMenu', 'Close menu')
          : t('member.portal.openMenu', 'Account menu') }}
      </button>
      <span class="text-sm font-medium text-muted-foreground truncate">
        {{ currentPageTitle }}
      </span>
    </div>

    <div class="flex flex-1 w-full min-h-0">
      <!-- Mobile backdrop -->
      <button
        v-if="sidebarOpen"
        type="button"
        class="lg:hidden fixed inset-0 z-30 bg-background/70 backdrop-blur-sm"
        :aria-label="t('member.portal.closeMenu', 'Close menu')"
        @click="sidebarOpen = false"
      />

      <MemberPortalSidebar
        :sidebar-open="sidebarOpen"
        @navigate="sidebarOpen = false"
      />

      <main class="flex-1 min-w-0 flex flex-col overflow-hidden">
        <div class="flex-1 overflow-y-auto">
          <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
            <router-view />
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { Menu, X } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import MemberPortalSidebar from '@/modules/Member/components/MemberPortalSidebar.vue';
import { useMemberPortalBreadcrumbs } from '@/modules/Member/composables/useMemberPortalBreadcrumbs';
import { useMemberStore } from '@/modules/Member/stores/member';

const { t } = useI18n();
const route = useRoute();
const memberStore = useMemberStore();
const { currentPageTitle } = useMemberPortalBreadcrumbs();
const sidebarOpen = ref(false);

watch(() => route.name, () => {
    sidebarOpen.value = false;
});

onMounted(() => {
    void memberStore.fetchPortal();
});
</script>

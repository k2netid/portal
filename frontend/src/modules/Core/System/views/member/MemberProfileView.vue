<template>
  <div class="space-y-6">
    <!-- Header Hero Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-2xl bg-card/80 border border-border/50 shadow-sm backdrop-blur-md">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-primary/10 text-primary flex items-center justify-center text-xl sm:text-2xl font-black border border-primary/20 shadow-inner shrink-0">
          {{ userInitial }}
        </div>
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-foreground">
              {{ authStore.user?.name || 'Member' }}
            </h1>
            <Badge
              variant="outline"
              class="text-[10px] uppercase font-bold tracking-wider bg-primary/10 text-primary border-primary/20"
            >
              {{ userRoleLabel }}
            </Badge>
          </div>
          <p class="text-xs sm:text-sm text-muted-foreground mt-0.5 font-medium">
            {{ authStore.user?.email }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2 self-start sm:self-auto">
        <Badge
          variant="secondary"
          class="text-xs px-3 py-1 bg-muted/60 text-muted-foreground border-border/40 font-medium"
        >
          {{ t('system.member.memberSince', 'Member sejak') }} {{ memberSinceDate }}
        </Badge>
      </div>
    </div>

    <!-- Embedded Profile Tabs Component -->
    <div class="bg-card/60 rounded-2xl border border-border/50 p-2 sm:p-4 backdrop-blur-sm">
      <Profile />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { Badge } from '@/shared/components/ui';
import Profile from '../Profile.vue';

const { t } = useI18n();
const authStore = useAuthStore();

const userInitial = computed(() => {
  const name = authStore.user?.name || '';
  return name.charAt(0).toUpperCase() || 'M';
});

const userRoleLabel = computed(() => {
  const roles = authStore.user?.roles || [];
  if (roles.length > 0) {
    const roleName = typeof roles[0] === 'string' ? roles[0] : (roles[0] as any)?.name;
    return roleName || 'Reader';
  }
  return 'Reader';
});

const memberSinceDate = computed(() => {
  const createdAt = (authStore.user as any)?.created_at;
  if (!createdAt) return '2026';
  try {
    return new Date(createdAt).toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'short',
    });
  } catch {
    return '2026';
  }
});
</script>

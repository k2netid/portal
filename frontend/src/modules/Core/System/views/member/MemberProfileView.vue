<template>
  <div class="space-y-6">
    <!-- Header Card -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-2xl bg-gradient-to-br from-card/80 to-card/40 border border-border/50 shadow-sm backdrop-blur-md">
      <div class="flex items-center gap-4">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 text-primary flex items-center justify-center text-2xl font-black border border-primary/20 shadow-inner">
          {{ userInitial }}
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-foreground">
              {{ authStore.user?.name || 'Member' }}
            </h1>
            <Badge
              variant="outline"
              class="text-[10px] uppercase font-bold tracking-wider bg-primary/10 text-primary border-primary/20"
            >
              {{ userRoleLabel }}
            </Badge>
          </div>
          <p class="text-xs sm:text-sm text-muted-foreground mt-0.5">
            {{ authStore.user?.email }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <Badge
          variant="secondary"
          class="text-xs px-3 py-1 bg-muted/60 text-muted-foreground border-border/40"
        >
          {{ t('system.member.memberSince', 'Member sejak') }} {{ memberSinceDate }}
        </Badge>
      </div>
    </div>

    <!-- Embedded Full Profile Component -->
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

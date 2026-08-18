<template>
  <div>
    <component :is="activeDashboard" v-if="activeDashboard" />
    <div v-else class="flex items-center justify-center h-[60vh] text-muted-foreground italic">
      No dashboard available for your role.
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useDashboardStore } from '@/shared/stores/dashboard';

const authStore = useAuthStore();
const dashboardStore = useDashboardStore();

const activeDashboard = computed(() => {
    if (!authStore.user) return null;
    return dashboardStore.getActiveDashboard(authStore.user, authStore);
});
</script>
